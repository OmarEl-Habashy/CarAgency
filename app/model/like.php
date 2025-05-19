<?php

class Like {
    private $likeId;
    private $postId;
    private $userId;
    private $createdAt;

    public function __construct($likeId = null, $postId = null, $userId = null, $createdAt = null) {
        $this->likeId = $likeId;
        $this->postId = $postId;
        $this->userId = $userId;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getLikeId() {
        return $this->likeId;
    }
    
    public function getPostId() {
        return $this->postId;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    // Setters
    public function setLikeId($likeId) {
        $this->likeId = $likeId;
        return $this;
    }
    
    public function setPostId($postId) {
        $this->postId = $postId;
        return $this;
    }
    
    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }
    
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function __toString() {
        return "Like{" .
                "likeId=" . $this->likeId .
                ", postId=" . $this->postId .
                ", userId=" . $this->userId .
                ", createdAt=" . $this->createdAt .
                '}';
    }
    
    public function toArray() {
        return [
            'likeId' => $this->likeId,
            'postId' => $this->postId,
            'userId' => $this->userId,
            'createdAt' => $this->createdAt
        ];
    }
}
?>