<?php
class MediaHandler {
    private $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
    private $maxFileSize = 10485760; // 10MB
    private $uploadDir = '../../public/uploads/';
    private $baseUrl = '/Project/public/uploads/';
    
    public function __construct() {
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload media file (image or video)
     * 
     * @param array $file The $_FILES['media'] array
     * @return array Result with success status, URL or error message
     */
    public function uploadMedia($file) {
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->handleUploadError($file['error']);
        }
        
        // Check file type
        $fileType = mime_content_type($file['tmp_name']);
        if (!$this->isAllowedFileType($fileType)) {
            return [
                'success' => false,
                'error' => 'invalid_file_type'
            ];
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return [
                'success' => false,
                'error' => 'file_too_large'
            ];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid() . '_' . time() . '.' . $extension;
        $destination = $this->uploadDir . $newFilename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => true,
                'url' => $this->baseUrl . $newFilename,
                'type' => $this->isImageFile($fileType) ? 'image' : 'video'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'upload_failed'
            ];
        }
    }
    
    /**
     * Check if file type is allowed
     */
    private function isAllowedFileType($fileType) {
        return in_array($fileType, $this->allowedImageTypes) || 
               in_array($fileType, $this->allowedVideoTypes);
    }
    
    /**
     * Check if file is an image
     */
    private function isImageFile($fileType) {
        return in_array($fileType, $this->allowedImageTypes);
    }
    
    /**
     * Handle upload errors
     */
    private function handleUploadError($errorCode) {
        $error = 'unknown_error';
        
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'file_too_large';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'file_partially_uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'no_file_uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error = 'missing_temp_folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error = 'failed_to_write_file';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error = 'stopped_by_extension';
                break;
        }
        
        return [
            'success' => false,
            'error' => $error
        ];
    }
    
    /**
     * Future method for AWS S3 integration
     * This will replace the local upload when you have your S3 bucket
     */
    public function uploadToS3($file) {
        // This will be implemented when you have an S3 bucket
        // For now, it returns an error
        return [
            'success' => false,
            'error' => 's3_not_configured'
        ];
    }
}
?>