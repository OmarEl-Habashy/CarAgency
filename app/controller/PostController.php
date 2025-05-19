<?php
require_once __DIR__ . '/../DAO/postdao.php';

class PostController {
    private $postDAO;
    
    public function __construct($connection) {
        $this->postDAO = new Postdao($connection);
    }
    
    public function getPostData($post, $userId) {
        $postData = [
            'postId' => $post->getPostId(),
            'liked' => $this->postDAO->hasUserLikedPost($post->getPostId(), $userId),
            'likeCount' => $this->postDAO->getLikeCountByPostId($post->getPostId()),
            'postUsername' => method_exists($post, 'getUsername') ? $post->getUsername() : 
                (isset($post['username']) ? $post['username'] : ''),
            'createdAt' => method_exists($post, 'getCreatedAt') ? $post->getCreatedAt() : 
                (isset($post['created_at']) ? $post['created_at'] : ''),
            'caption' => method_exists($post, 'getCaption') ? $post->getCaption() : 
                (isset($post['caption']) ? $post['caption'] : ''),
            'contentURL' => method_exists($post, 'getContentURL') ? $post->getContentURL() : 
                (isset($post['content_url']) ? $post['content_url'] : ''),
            'postUserId' => method_exists($post, 'getUserId') ? $post->getUserId() : 
                (isset($post['user_id']) ? $post['user_id'] : 0)
        ];
        
        return $postData;
    }
    
    public function getFollowingPosts($userId) {
        return $this->postDAO->getFollowingPosts($userId);
    }
    
    public function hasUserLikedPost($postId, $userId) {
        return $this->postDAO->hasUserLikedPost($postId, $userId);
    }
    
    public function getLikeCountByPostId($postId) {
        return $this->postDAO->getLikeCountByPostId($postId);
    }
}
?>