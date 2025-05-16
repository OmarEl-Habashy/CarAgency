<?php

class Comment {
    private $commentId;
    private $postId;
    private $userId;
    private $content;
    private $createdAt;
    private $username;

    // Constructor with all fields including username
    public function __construct($commentId = null, $postId = null, $userId = null, $content = null, $createdAt = null, $username = null) {
        $this->commentId = $commentId;
        $this->postId = $postId;
        $this->userId = $userId;
        $this->content = $content;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->username = $username;
    }
    
    // Getters
    public function getCommentId() {
        return $this->commentId;
    }
    
    public function getPostId() {
        return $this->postId;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getContent() {
        return $this->content;
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    // Setters
    public function setCommentId($commentId) {
        $this->commentId = $commentId;
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
    
    public function setContent($content) {
        $this->content = $content;
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
        return "Comment{" .
                "commentId=" . $this->commentId .
                ", postId=" . $this->postId .
                ", userId=" . $this->userId .
                ", content='" . $this->content . '\'' .
                ", createdAt=" . $this->createdAt .
                ", username='" . $this->username . '\'' .
                '}';
    }
    
    public function toArray() {
        return [
            'commentId' => $this->commentId,
            'postId' => $this->postId,
            'userId' => $this->userId,
            'content' => $this->content,
            'createdAt' => $this->createdAt,
            'username' => $this->username
        ];
    }
}
?>