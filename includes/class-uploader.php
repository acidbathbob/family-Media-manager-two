<?php
/**
 * File uploader class
 * Handles file uploads and validation
 */
class Family_Media_Manager_Uploader {

    /**
     * Allowed file types
     */
    private static $allowed_types = array(
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/webm'
    );

    /**
     * Maximum file size (in bytes) - 100MB
     */
    private static $max_file_size = 104857600;

    /**
     * Handle file upload
     */
    public static function handle_upload($file, $caption = '', $album_id = null) {
        // Validate file
        $validation = self::validate_file($file);
        if (!$validation['valid']) {
            return array(
                'success' => false,
                'error'   => $validation['error']
            );
        }

        $user_id = get_current_user_id();
        
        // Upload to cloud storage
        $cloud = new Family_Media_Manager_Cloud_Storage($user_id);
        
        if (!$cloud->is_connected()) {
            return array(
                'success' => false,
                'error'   => 'Cloud storage not connected. Please connect your Google Drive first.'
            );
        }

        $upload_result = $cloud->upload_file($file['tmp_name'], $file['name']);
        
        if (!$upload_result['success']) {
            return array(
                'success' => false,
                'error'   => $upload_result['error']
            );
        }

        // Generate thumbnail
        $thumbnail = Family_Media_Manager_Thumbnail::generate($file['tmp_name'], $file['type']);

        // Determine file type
        $file_type = strpos($file['type'], 'image') !== false ? 'photo' : 'video';

        // Extract EXIF date if available
        $taken_date = null;
        if ($file_type === 'photo' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file['tmp_name']);
            if ($exif && isset($exif['DateTimeOriginal'])) {
                $taken_date = date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
            }
        }

        // Add to media library
        $media_id = Family_Media_Manager_Media_Library::add_media(array(
            'owner_id'       => $user_id,
            'cloud_provider' => 'google_drive',
            'cloud_file_id'  => $upload_result['file_id'],
            'filename'       => $file['name'],
            'file_type'      => $file_type,
            'file_size'      => $file['size'],
            'thumbnail_path' => $thumbnail,
            'taken_date'     => $taken_date,
            'album_id'       => $album_id,
            'caption'        => sanitize_text_field($caption)
        ));

        if (!$media_id) {
            return array(
                'success' => false,
                'error'   => 'Failed to save media to library'
            );
        }

        return array(
            'success'       => true,
            'media_id'      => $media_id,
            'thumbnail_url' => $thumbnail ? wp_get_upload_dir()['baseurl'] . '/family-gallery/' . basename($thumbnail) : ''
        );
    }

    /**
     * Validate uploaded file
     */
    private static function validate_file($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array(
                'valid' => false,
                'error' => 'File upload error: ' . $file['error']
            );
        }

        // Check file size
        if ($file['size'] > self::$max_file_size) {
            return array(
                'valid' => false,
                'error' => 'File size exceeds maximum allowed size of 100MB'
            );
        }

        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, self::$allowed_types)) {
            return array(
                'valid' => false,
                'error' => 'File type not allowed. Allowed types: images and videos'
            );
        }

        return array('valid' => true);
    }

    /**
     * Handle base64 encoded file upload (for PWA/API)
     */
    public static function handle_base64_upload($base64_data, $filename, $caption = '', $album_id = null) {
        // Decode base64
        $file_data = base64_decode($base64_data);
        
        if ($file_data === false) {
            return array(
                'success' => false,
                'error'   => 'Invalid base64 data'
            );
        }

        // Create temporary file
        $tmp_file = wp_tempnam($filename);
        file_put_contents($tmp_file, $file_data);

        // Create file array similar to $_FILES
        $file = array(
            'name'     => $filename,
            'type'     => mime_content_type($tmp_file),
            'tmp_name' => $tmp_file,
            'error'    => 0,
            'size'     => filesize($tmp_file)
        );

        // Use standard upload handler
        $result = self::handle_upload($file, $caption, $album_id);

        // Clean up temp file
        @unlink($tmp_file);

        return $result;
    }
}
