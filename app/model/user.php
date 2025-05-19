<?php

class user {
    private $userId;
    private $username;
    private $email;
    private $hashedPassword;
    private $bio;
    private $createdAt;

    public function __construct($userId = null, $username = null, $email = null, $hashedPassword = null, $bio = null, $createdAt = null) {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
        $this->hashedPassword = $hashedPassword;
        $this->bio = $bio;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        if (empty(trim($username))) {
            throw new InvalidArgumentException("Username cannot be null or empty.");
        }
        $this->username = $username;
        return $this;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        if (empty(trim($email))) {
            throw new InvalidArgumentException("Email cannot be null or empty.");
        }
        $this->email = $email;
        return $this;
    }

    public function getHashedPassword() {
        return $this->hashedPassword;
    }

    public function setHashedPassword($hashedPassword) {
        if (empty(trim($hashedPassword))) {
            throw new InvalidArgumentException("Password cannot be null or empty.");
        }
        $this->hashedPassword = $hashedPassword;
        return $this;
    }

    public function getBio() {
        return $this->bio;
    }

    public function setBio($bio) {
        $this->bio = $bio;
        return $this;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function toArray() {
        return [
            'userId' => $this->userId,
            'username' => $this->username,
            'email' => $this->email,
            'bio' => $this->bio,
            'createdAt' => $this->createdAt
        ];
    }
}

?>