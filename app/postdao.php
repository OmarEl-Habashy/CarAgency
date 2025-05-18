<?php
require_once 'database.php';
require_once 'Post.php';
require_once 'Comment.php';

class postdao {
    private $conn;
    
    // SQL query strings
    private static $INSERT_POST_SQL = 
        "INSERT INTO Posts (UserID, ContentURL, Caption) VALUES (?, ?, ?)";
    private static $SELECT_POST_BY_ID = 
        "SELECT * FROM Posts WHERE PostID = ?";
    private static $SELECT_POSTS_BY_USER_ID = 
        "SELECT * FROM Posts WHERE UserID = ? ORDER BY CreatedAt DESC";
    private static $SELECT_ALL_POSTS = 
        "SELECT * FROM Posts ORDER BY CreatedAt DESC LIMIT ?, ?";
    private static $SELECT_ALL_POSTS_WITH_USER = 
        "SELECT p.*, u.Username FROM Posts p JOIN Users u ON p.UserID = u.UserID ORDER BY p.CreatedAt DESC";
    private static $SELECT_FEED_POSTS = 
        "SELECT p.*, u.Username FROM Posts p 
         JOIN Users u ON p.UserID = u.UserID 
         WHERE p.UserID = ? 
            OR p.UserID IN (SELECT FolloweeID FROM Follows WHERE FollowerID = ?) 
         ORDER BY p.CreatedAt DESC LIMIT ?, ?";
    private static $UPDATE_POST_SQL = 
        "UPDATE Posts SET ContentURL = ?, Caption = ? WHERE PostID = ?";
    private static $DELETE_POST_SQL = 
        "DELETE FROM Posts WHERE PostID = ?";
    private static $COUNT_POSTS_BY_USER_ID = 
        "SELECT COUNT(*) FROM Posts WHERE UserID = ?";
    private static $SELECT_POSTS_BY_HASHTAG = 
        "SELECT p.* FROM Posts p 
         JOIN PostHashtags ph ON p.PostID = ph.PostID 
         JOIN Hashtags h ON ph.HashtagID = h.HashtagID 
         WHERE h.HashtagName = ? 
         ORDER BY p.CreatedAt DESC LIMIT ?, ?";

    // SQL for fetching comments with username
    private static $SELECT_COMMENTS_BY_POST_ID = 
        "SELECT c.*, u.Username FROM Comments c 
         JOIN Users u ON c.UserID = u.UserID 
         WHERE c.PostID = ? ORDER BY c.CreatedAt ASC";

    // SQL for counting likes on a post
    private static $COUNT_LIKES_BY_POST_ID = 
        "SELECT COUNT(*) FROM Likes WHERE PostID = ?";

    // SQL for checking if a user has liked a post
    private static $CHECK_USER_LIKED_POST = 
        "SELECT COUNT(*) FROM Likes WHERE PostID = ? AND UserID = ?";
    
    // SQL for inserting a like
    private static $INSERT_LIKE_SQL = 
        "INSERT INTO Likes (PostID, UserID) VALUES (?, ?)";
    
    // SQL for removing a like
    private static $REMOVE_LIKE_SQL = 
        "DELETE FROM Likes WHERE PostID = ? AND UserID = ?";
    
    // SQL for inserting a comment
    private static $INSERT_COMMENT_SQL = 
        "INSERT INTO Comments (PostID, UserID, Content) VALUES (?, ?, ?)";

    public function __construct(PDO $dbConnection) {
        $this->conn = $dbConnection;
    }

    public function insertPost(Post $post) {
        try {
            $stmt = $this->conn->prepare(self::$INSERT_POST_SQL);
            $stmt->bindValue(1, $post->getUserId(), PDO::PARAM_INT);
            $stmt->bindValue(2, $post->getContentURL(), PDO::PARAM_STR);
            $stmt->bindValue(3, $post->getCaption(), PDO::PARAM_STR);
            
            $affectedRows = $stmt->execute();
            
            if ($affectedRows) {
                $post->setPostId($this->conn->lastInsertId());
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error inserting post: " . $e->getMessage());
            return false;
        }
    }

    public function getPostById($postId) {
        try {
            $stmt = $this->conn->prepare(self::$SELECT_POST_BY_ID);
            $stmt->bindValue(1, $postId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $this->extractPostFromRow($row);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error getting post by ID: " . $e->getMessage());
            return null;
        }
    }

    public function getPostsByUserId($userId) {
        $posts = [];
        
        try {
            $stmt = $this->conn->prepare(self::$SELECT_POSTS_BY_USER_ID);
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = $this->extractPostFromRow($row);
            }
        } catch (PDOException $e) {
            error_log("Error getting posts by user ID: " . $e->getMessage());
        }
        
        return $posts;
    }

    // NEW: Get all posts with usernames for the feed
    public function getAllPosts() {
        $posts = [];
        try {
            $stmt = $this->conn->prepare(self::$SELECT_ALL_POSTS_WITH_USER);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $post = $this->extractPostFromRow($row);
                if (isset($row['Username'])) {
                    $post->setUsername($row['Username']);
                }
                $posts[] = $post;
            }
        } catch (PDOException $e) {
            error_log("Error getting all posts: " . $e->getMessage());
        }
        return $posts;
    }

    public function getFeedPostsForUser($userId, $offset, $limit) {
        $posts = [];
        
        try {
            $stmt = $this->conn->prepare(self::$SELECT_FEED_POSTS);
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $userId, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            $stmt->bindValue(4, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $post = $this->extractPostFromRow($row);
                if (isset($row['Username'])) {
                    $post->setUsername($row['Username']);
                }
                $posts[] = $post;
            }
        } catch (PDOException $e) {
            error_log("Error getting feed posts: " . $e->getMessage());
        }
        
        return $posts;
    }

    public function updatePost(Post $post) {
        try {
            $stmt = $this->conn->prepare(self::$UPDATE_POST_SQL);
            $stmt->bindValue(1, $post->getContentURL(), PDO::PARAM_STR);
            $stmt->bindValue(2, $post->getCaption(), PDO::PARAM_STR);
            $stmt->bindValue(3, $post->getPostId(), PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating post: " . $e->getMessage());
            return false;
        }
    }

    public function deletePost($postId) {
        try {
            $stmt = $this->conn->prepare(self::$DELETE_POST_SQL);
            $stmt->bindValue(1, $postId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting post: " . $e->getMessage());
            return false;
        }
    }

    public function getPostCountByUserId($userId) {
        try {
            $stmt = $this->conn->prepare(self::$COUNT_POSTS_BY_USER_ID);
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting posts by user ID: " . $e->getMessage());
            return 0;
        }
    }

    public function getPostsByHashtag($hashtag, $offset, $limit) {
        $posts = [];
        
        try {
            $stmt = $this->conn->prepare(self::$SELECT_POSTS_BY_HASHTAG);
            $stmt->bindValue(1, $hashtag, PDO::PARAM_STR);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = $this->extractPostFromRow($row);
            }
        } catch (PDOException $e) {
            error_log("Error getting posts by hashtag: " . $e->getMessage());
        }
        
        return $posts;
    }

    // Helper method to extract a Post object from a result set row
    private function extractPostFromRow($row) {
        $post = new Post(
            $row['PostID'],
            $row['UserID'],
            $row['ContentURL'],
            $row['Caption'],
            $row['CreatedAt']
        );
        
        if (isset($row['Username'])) {
            $post->setUsername($row['Username']);
        }
        
        return $post;
    }

    // Method to insert a like into the Likes table
    public function insertLike($postId, $userId) {
        try {
            $stmt = $this->conn->prepare(self::$INSERT_LIKE_SQL);
            $stmt->bindValue(1, $postId, PDO::PARAM_INT);
            $stmt->bindValue(2, $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Check for duplicate entry error (integrity constraint violation)
            if ($e->getCode() == '23000') {
                error_log("User has already liked this post.");
            } else {
                error_log("Error inserting like: " . $e->getMessage());
            }
            return false;
        }
    }

    // Method to insert a comment into the Comments table
    public function insertComment($postId, $userId, $content) {
        try {
            $stmt = $this->conn->prepare(self::$INSERT_COMMENT_SQL);
            $stmt->bindValue(1, $postId, PDO::PARAM_INT);
            $stmt->bindValue(2, $userId, PDO::PARAM_INT);
            $stmt->bindValue(3, $content, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error inserting comment: " . $e->getMessage());
            return false;
        }
    }

    // Method to get all comments for a post with username information
    public function getCommentsByPostId($postId) {
        $comments = [];
        
        try {
            $stmt = $this->conn->prepare(self::$SELECT_COMMENTS_BY_POST_ID);
            $stmt->bindValue(1, $postId, PDO::PARAM_INT);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comment = new Comment(
                    $row['CommentID'],
                    $row['PostID'],
                    $row['UserID'],
                    $row['Content'],
                    $row['CreatedAt'],
                    $row['Username']
                );
                $comments[] = $comment;
            }
        } catch (PDOException $e) {
            error_log("Error getting comments by post ID: " . $e->getMessage());
        }
        
        return $comments;
    }

    // Method to count likes for a post
    public function getLikeCountByPostId($postId) {
        try {
            $stmt = $this->conn->prepare(self::$COUNT_LIKES_BY_POST_ID);
            $stmt->bindValue(1, $postId, PDO::PARAM_INT);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting likes by post ID: " . $e->getMessage());
            return 0;
        }
    }

    // Method to check if user has liked a post
    public function hasUserLikedPost($postId, $userId) {
        try {
            $stmt = $this->conn->prepare(self::$CHECK_USER_LIKED_POST);
            $stmt->bindValue(1, $postId, PDO::PARAM_INT);
            $stmt->bindValue(2, $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking if user liked post: " . $e->getMessage());
            return false;
        }
    }

    // Method to remove a like
    public function removeLike($postId, $userId) {
        try {
            $stmt = $this->conn->prepare(self::$REMOVE_LIKE_SQL);
            $stmt->bindValue(1, $postId, PDO::PARAM_INT);
            $stmt->bindValue(2, $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error removing like: " . $e->getMessage());
            return false;
        }
    }
}
?>