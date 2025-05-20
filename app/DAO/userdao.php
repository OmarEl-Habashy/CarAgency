<?php
require_once __DIR__ . '/../../database/database.php';
require_once __DIR__ . '/../model/user.php';        
class userdao {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a new user
    public function insertUser(User $user) {
        try {
            $sql = "INSERT INTO Users (Username, Email, PassW, Bio, CreatedAt) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            $username = $user->getUsername();
            $email = $user->getEmail();
            $password = $user->getHashedPassword();
            $bio = $user->getBio();
            $createdAt = $user->getCreatedAt();
            
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $password);
            $stmt->bindParam(4, $bio);
            $stmt->bindParam(5, $createdAt);
            
            $stmt->execute();
            
            $user->setUserId($this->db->lastInsertId());
            
            return true;
        } catch (PDOException $e) {
            // Log error
            error_log("Error inserting user: " . $e->getMessage());
            return false;
        }
    }

    // Retrieve a user by ID
    public function selectUser($userId) {
        try {
            $sql = "SELECT * FROM Users WHERE UserID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $this->createUserFromRow($row);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error selecting user: " . $e->getMessage());
            return null;
        }
    }

    // Retrieve all users
    public function selectAllUsers() {
        try {
            $sql = "SELECT * FROM Users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $users = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = $this->createUserFromRow($row);
            }
            
            return $users;
        } catch (PDOException $e) {
            error_log("Error selecting all users: " . $e->getMessage());
            return [];
        }
    }

    // Update a user
    public function updateUser(User $user) {
        try {
            $sql = "UPDATE Users SET Username = ?, Email = ?, PassW = ?, Bio = ? WHERE UserID = ?";
            $stmt = $this->db->prepare($sql);
            
            $username = $user->getUsername();
            $email = $user->getEmail();
            $password = $user->getHashedPassword();
            $bio = $user->getBio();
            $userId = $user->getUserId();
            
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $password);
            $stmt->bindParam(4, $bio);
            $stmt->bindParam(5, $userId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    // Delete a user
    public function deleteUser($userId) {
        try {
            $sql = "DELETE FROM Users WHERE UserID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $userId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    // Get a user by username
    public function getUserByUsername($username) {
        try {
            $sql = "SELECT * FROM Users WHERE Username = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $username);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $this->createUserFromRow($row);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error getting user by username: " . $e->getMessage());
            return null;
        }
    }

    // Get user by email
    public function getUserByEmail($email) {
        try {
            $sql = "SELECT * FROM Users WHERE Email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $email);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $this->createUserFromRow($row);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return null;
        }
    }

    // Login user
    public function loginUser($username, $password) {
        try {
            $sql = "SELECT * FROM Users WHERE Username = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $username);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $row['PassW'])) {
                    return $this->createUserFromRow($row);
                }
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error logging in user: " . $e->getMessage());
            return null;
        }
    }

    // Helper method to create a User object from a database row
    private function createUserFromRow($row) {
        return new User(
            $row['UserID'],
            $row['Username'],
            $row['Email'],
            $row['PassW'],
            $row['Bio'] ?? null,
            $row['CreatedAt']
        );
    }

    // Get follower count
    public function getFollowerCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM Follows WHERE FolloweeID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting follower count: " . $e->getMessage());
            return 0;
        }
    }

    // Get following count
    public function getFollowingCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM Follows WHERE FollowerID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting following count: " . $e->getMessage());
            return 0;
        }
    }

    // Check if a user is following another
    public function isFollowing($followerId, $followeeId) {
        try {
            $sql = "SELECT COUNT(*) FROM Follows WHERE FollowerID = ? AND FolloweeID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $followerId);
            $stmt->bindParam(2, $followeeId);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking following status: " . $e->getMessage());
            return false;
        }
    }

    // Follow a user
    public function followUser($followerId, $followeeId) {
        if ($followerId == $followeeId) {
            return false; // Cannot follow self
        }

        if ($this->isFollowing($followerId, $followeeId)) {
            return true; // Already following
        }

        try {
            $sql = "INSERT INTO Follows (FollowerID, FolloweeID) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $followerId);
            $stmt->bindParam(2, $followeeId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error following user: " . $e->getMessage());
            return false;
        }
    }

    // Unfollow a user
    public function unfollowUser($followerId, $followeeId) {
        try {
            $sql = "DELETE FROM Follows WHERE FollowerID = ? AND FolloweeID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $followerId);
            $stmt->bindParam(2, $followeeId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error unfollowing user: " . $e->getMessage());
            return false;
        }
    }

    // Get followers
    public function getFollowers($userId) {
        try {
            $sql = "SELECT u.* FROM Users u JOIN Follows f ON u.UserID = f.FollowerID WHERE f.FolloweeID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            
            $followers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $followers[] = $this->createUserFromRow($row);
            }
            
            return $followers;
        } catch (PDOException $e) {
            error_log("Error getting followers: " . $e->getMessage());
            return [];
        }
    }

    // Get following
    public function getFollowing($userId) {
        try {
            $sql = "SELECT u.* FROM Users u JOIN Follows f ON u.UserID = f.FolloweeID WHERE f.FollowerID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            
            $following = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $following[] = $this->createUserFromRow($row);
            }
            
            return $following;
        } catch (PDOException $e) {
            error_log("Error getting following: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUserEmail($username) {
        try {
            $sql = "SELECT Email FROM Users WHERE Username = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $username);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['Email'];
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error getting user email: " . $e->getMessage());
            return false;
        }
    }
}
?>