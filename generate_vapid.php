<?php
require_once __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\Vapid;

$vapidKeys = Vapid::createVapidKeys();

echo "VAPID_PUBLIC_KEY=\"" . $vapidKeys['publicKey'] . "\"\n";
echo "VAPID_PRIVATE_KEY=\"" . $vapidKeys['privateKey'] . "\"\n";
?>
