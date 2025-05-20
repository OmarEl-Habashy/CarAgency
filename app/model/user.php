<?php
class User {
    private $userId;
    private $username;
    private $email;
    private $hashedPassword;
    private $bio;
    private $createdAt;
    private $profilePicture;

    public function __construct($userId, $username, $email, $hashedPassword, $bio, $createdAt, $profilePicture = null) {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
        $this->hashedPassword = $hashedPassword;
        $this->bio = $bio;
        $this->createdAt = $createdAt;
        $this->profilePicture = $profilePicture;
    }

    // Getters
    public function getUserId() {
        return $this->userId;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getHashedPassword() {
        return $this->hashedPassword;
    }

    public function getBio() {
        return $this->bio;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getProfilePicture() {
        return $this->profilePicture;
    }

    // Setters
    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setHashedPassword($hashedPassword) {
        $this->hashedPassword = $hashedPassword;
    }

    public function setBio($bio) {
        $this->bio = $bio;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function setProfilePicture($profilePicture) {
        $this->profilePicture = $profilePicture;
    }
}
?>