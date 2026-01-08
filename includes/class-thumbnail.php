<?php
/**
 * Thumbnail generation class
 */
class Family_Media_Manager_Thumbnail {

    /**
     * Generate thumbnail for image or video
     */
    public static function generate($file_path, $mime_type) {
        $thumbnail_size = get_option('family_media_manager_thumbnail_size', 300);
        
        // Create thumbnails directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $thumbnail_dir = $upload_dir['basedir'] . '/family-gallery';
        
        if (!file_exists($thumbnail_dir)) {
            wp_mkdir_p($thumbnail_dir);
        }

        $filename = uniqid('thumb_') . '.jpg';
        $thumbnail_path = $thumbnail_dir . '/' . $filename;

        if (strpos($mime_type, 'image') !== false) {
            return self::generate_image_thumbnail($file_path, $thumbnail_path, $thumbnail_size);
        } elseif (strpos($mime_type, 'video') !== false) {
            return self::generate_video_thumbnail($file_path, $thumbnail_path, $thumbnail_size);
        }

        return null;
    }

    /**
     * Generate thumbnail for image
     */
    private static function generate_image_thumbnail($source_path, $dest_path, $size) {
        $image = wp_get_image_editor($source_path);
        
        if (is_wp_error($image)) {
            return null;
        }

        // Resize to square thumbnail
        $image->resize($size, $size, true);
        
        // Save as JPEG
        $result = $image->save($dest_path, 'image/jpeg');
        
        if (is_wp_error($result)) {
            return null;
        }

        return $dest_path;
    }

    /**
     * Generate thumbnail for video
     */
    private static function generate_video_thumbnail($source_path, $dest_path, $size) {
        // Check if FFmpeg is available
        if (!function_exists('exec')) {
            // If FFmpeg is not available, return a default video icon
            return self::create_default_video_thumbnail($dest_path, $size);
        }

        // Try to use FFmpeg to extract frame
        $ffmpeg_path = exec('which ffmpeg');
        
        if (empty($ffmpeg_path)) {
            return self::create_default_video_thumbnail($dest_path, $size);
        }

        // Extract frame at 1 second
        $command = sprintf(
            '%s -i %s -ss 00:00:01 -vframes 1 -vf scale=%d:%d %s 2>&1',
            escapeshellarg($ffmpeg_path),
            escapeshellarg($source_path),
            $size,
            $size,
            escapeshellarg($dest_path)
        );

        exec($command, $output, $return_var);

        if ($return_var === 0 && file_exists($dest_path)) {
            return $dest_path;
        }

        // Fallback to default video thumbnail
        return self::create_default_video_thumbnail($dest_path, $size);
    }

    /**
     * Create default video thumbnail (play icon)
     */
    private static function create_default_video_thumbnail($dest_path, $size) {
        // Create a simple image with play icon
        $image = imagecreatetruecolor($size, $size);
        
        // Dark gray background
        $bg_color = imagecolorallocate($image, 50, 50, 50);
        imagefill($image, 0, 0, $bg_color);

        // White play triangle
        $white = imagecolorallocate($image, 255, 255, 255);
        $center_x = $size / 2;
        $center_y = $size / 2;
        $triangle_size = $size / 3;

        $points = array(
            $center_x - $triangle_size / 2, $center_y - $triangle_size / 2,
            $center_x - $triangle_size / 2, $center_y + $triangle_size / 2,
            $center_x + $triangle_size / 2, $center_y
        );

        imagefilledpolygon($image, $points, 3, $white);

        // Save as JPEG
        imagejpeg($image, $dest_path, 85);
        imagedestroy($image);

        return $dest_path;
    }

    /**
     * Delete thumbnail file
     */
    public static function delete($thumbnail_path) {
        if ($thumbnail_path && file_exists($thumbnail_path)) {
            return @unlink($thumbnail_path);
        }
        return false;
    }
}
