<?php

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function cd_s3_client(): ?S3Client
{
    static $client = null;

    if ($client !== null) {
        return $client;
    }

    $key = $_ENV['AWS_ACCESS_KEY_ID'] ?? '';
    $secret = $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '';
    $region = $_ENV['AWS_DEFAULT_REGION'] ?? '';

    if ($key === '' || $secret === '' || $region === '') {
        return null; // Not configured
    }

    $client = new S3Client([
        'version' => 'latest',
        'region'  => $region,
        'credentials' => [
            'key'    => $key,
            'secret' => $secret,
        ]
    ]);

    return $client;
}

function cd_s3_bucket(): string
{
    return $_ENV['AWS_BUCKET'] ?? '';
}

/**
 * Upload a file to S3
 */
function cd_s3_upload(string $sourceFile, string $s3Key): bool
{
    $client = cd_s3_client();
    $bucket = cd_s3_bucket();

    if (!$client) {
        $key    = $_ENV['AWS_ACCESS_KEY_ID']     ?? '';
        $secret = $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '';
        $region = $_ENV['AWS_DEFAULT_REGION']    ?? '';
        error_log('S3 Upload: Client is null. KEY=' . ($key !== '' ? 'SET' : 'EMPTY')
            . ' SECRET=' . ($secret !== '' ? 'SET' : 'EMPTY')
            . ' REGION=' . ($region !== '' ? $region : 'EMPTY'));
        return false;
    }

    if (!$bucket) {
        error_log('S3 Upload: AWS_BUCKET is empty');
        return false;
    }

    if (!is_file($sourceFile)) {
        error_log('S3 Upload: Source file not found: ' . $sourceFile);
        return false;
    }

    try {
        $client->putObject([
            'Bucket'     => $bucket,
            'Key'        => $s3Key,
            'SourceFile' => $sourceFile,
        ]);
        return true;
    } catch (\Throwable $e) {
        $msg = 'S3 Upload Error [' . $s3Key . ']: '
            . get_class($e) . ' | '
            . 'HTTP ' . (method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 'N/A') . ' | '
            . $e->getMessage();
        error_log($msg);
        $debugLog = __DIR__ . '/s3_debug.log';
        file_put_contents($debugLog, '  AWS ERROR: ' . $msg . "\n", FILE_APPEND);
        return false;
    }
}


/**
 * Delete a file from S3
 */
function cd_s3_delete(string $s3Key): bool
{
    $client = cd_s3_client();
    $bucket = cd_s3_bucket();

    if (!$client || !$bucket) {
        return false;
    }

    try {
        $client->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $s3Key,
        ]);
        return true;
    } catch (AwsException $e) {
        error_log('S3 Delete Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete multiple files with prefix
 */
function cd_s3_delete_prefix(string $prefix): bool
{
    $client = cd_s3_client();
    $bucket = cd_s3_bucket();

    if (!$client || !$bucket) {
        return false;
    }

    try {
        $client->deleteMatchingObjects($bucket, $prefix);
        return true;
    } catch (\Exception $e) {
        error_log('S3 DeletePrefix Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get Pre-signed URL for viewing/downloading
 */
function cd_s3_presigned_url(string $s3Key, int $expiresInMinutes = 30, string $downloadFilename = ''): ?string
{
    $client = cd_s3_client();
    $bucket = cd_s3_bucket();

    if (!$client || !$bucket) {
        return null;
    }

    try {
        $params = [
            'Bucket' => $bucket,
            'Key'    => $s3Key
        ];
        
        if ($downloadFilename !== '') {
            $fallback = preg_replace('/[^\w\-\.]/', '_', $downloadFilename);
            $encoded = rawurlencode($downloadFilename);
            $params['ResponseContentDisposition'] = 'attachment; filename="' . $fallback . '"; filename*=UTF-8\'\'' . $encoded;
        }

        $cmd = $client->getCommand('GetObject', $params);
        $request = $client->createPresignedRequest($cmd, "+{$expiresInMinutes} minutes");
        return (string) $request->getUri();
    } catch (AwsException $e) {
        error_log('S3 Presign Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Check if file exists on S3
 */
function cd_s3_exists(string $s3Key): bool
{
    $client = cd_s3_client();
    $bucket = cd_s3_bucket();

    if (!$client || !$bucket) {
        return false;
    }
    
    return $client->doesObjectExist($bucket, $s3Key);
}
