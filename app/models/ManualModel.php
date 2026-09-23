<?php
require_once dirname(__FILE__) . '/Model.php';

class ManualModel extends Model {

    public function getAllTopicsWithContents() {
        $stmtTopic = $this->pdo->prepare("SELECT * FROM tbl_manual_topic 
                                        WHERE active_status = '1'
                                        ORDER BY list_order ASC, topic_id ASC");
        $stmtTopic->execute();
        $topics = $stmtTopic->fetchAll(PDO::FETCH_ASSOC);

        foreach ($topics as &$topic) {
            $stmtContent = $this->pdo->prepare("SELECT content_id, title_name, description, content_image, topic_id, list_order 
                                                FROM tbl_manual_content 
                                                WHERE topic_id = :topic_id 
                                                ORDER BY list_order ASC, content_id ASC");
            $stmtContent->execute(['topic_id' => $topic['topic_id']]);
            $topic['contents'] = $stmtContent->fetchAll(PDO::FETCH_ASSOC);
        }

        return $topics;
    }

    public function updateTopicOrder(array $topicIds) {
        $stmt = $this->pdo->prepare("UPDATE tbl_manual_topic SET list_order = :list_order WHERE topic_id = :topic_id");
        foreach ($topicIds as $index => $id) {
            $stmt->execute(['list_order' => $index + 1, 'topic_id' => $id]);
        }
        return true;
    }

    public function updateContentOrder(array $contentIds) {
        $stmt = $this->pdo->prepare("UPDATE tbl_manual_content SET list_order = :list_order WHERE content_id = :content_id");
        foreach ($contentIds as $index => $id) {
            $stmt->execute(['list_order' => $index + 1, 'content_id' => $id]);
        }
        return true;
    }

    public function getAllTopics() {
        $stmtTopic = $this->pdo->prepare("SELECT * FROM tbl_manual_topic 
                                        WHERE active_status = '1'
                                        ORDER BY list_order ASC, topic_id ASC");
        $stmtTopic->execute();
        return $stmtTopic->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContentById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_manual_content WHERE content_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertTopic($name, $list_order = 0) {
        $stmt = $this->pdo->prepare("INSERT INTO tbl_manual_topic (topics_name, list_order, create_at, active_status) 
                                    VALUES (:name, :list_order, NOW(), '1')");
        $stmt->execute(['name' => $name, 'list_order' => $list_order]);
        return $this->pdo->lastInsertId();
    }

    public function insertContent($topic_id, $title, $description, $content_image = null) {
        $stmtMax = $this->pdo->prepare("SELECT MAX(list_order) as max_order FROM tbl_manual_content WHERE topic_id = :topic_id");
        $stmtMax->execute(['topic_id' => $topic_id]);
        $maxResult = $stmtMax->fetch(PDO::FETCH_ASSOC);
        $next_order = ($maxResult && $maxResult['max_order'] !== null) ? (int)$maxResult['max_order'] + 1 : 1;

        $stmt = $this->pdo->prepare("INSERT INTO tbl_manual_content (topic_id, title_name, description, content_image, list_order, create_at) 
                                    VALUES (:topic_id, :title, :description, :content_image, :list_order, NOW())");
        return $stmt->execute([
            'topic_id' => $topic_id,
            'title' => $title,
            'description' => $description,
            'content_image' => $content_image,
            'list_order' => $next_order
        ]);
    }

    public function updateContent($content_id, $title, $description, $content_image = null) {
        $sql = "UPDATE tbl_manual_content 
                SET title_name = :title, description = :description, update_at = NOW()";
        $params = [
            'content_id' => $content_id,
            'title' => $title,
            'description' => $description
        ];

        if ($content_image !== null) {
            $sql .= ", content_image = :content_image";
            $params['content_image'] = $content_image;
        }

        $sql .= " WHERE content_id = :content_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteContent($content_id) {
        $stmt = $this->pdo->prepare("DELETE FROM tbl_manual_content WHERE content_id = :content_id");
        return $stmt->execute(['content_id' => $content_id]);
    }
}