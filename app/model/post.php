<?php

class Post {
    private $postId;
    private $userId;
    private $contentURL;
    private $caption;
    private $createdAt;
    private $username;

    public function __construct($postId = null, $userId = null, $contentURL = null, $caption = null, $createdAt = null) {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->contentURL = $contentURL;
        $this->caption = $caption;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->username = null;
    }

    public function getPostId() {
        return $this->postId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getContentURL() {
        return $this->contentURL;
    }

    public function getCaption() {
        return $this->caption;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setPostId($postId) {
        $this->postId = $postId;
        return $this;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    public function setContentURL($contentURL) {
        $this->contentURL = $contentURL;
        return $this;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
        return $this;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    public function __toString() {
        return "Post{" .
                "postId=" . $this->postId .
                ", userId=" . $this->userId .
                ", contentURL='" . $this->contentURL . '\'' .
                ", caption='" . $this->caption . '\'' .
                ", createdAt=" . $this->createdAt .
                '}';
    }

    public function toArray() {
        return [
            'postId' => $this->postId,
            'userId' => $this->userId,
            'contentURL' => $this->contentURL,
            'caption' => $this->caption,
            'createdAt' => $this->createdAt,
            'username' => $this->username
        ];
    }
}
?>