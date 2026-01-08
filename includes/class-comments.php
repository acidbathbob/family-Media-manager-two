<?php
/**
 * Comments management class
 * Handles comments on photos
 */
class Family_Media_Manager_Comments {

    /**
     * Add comment to media
     */
    public static function add_comment($media_id, $comment_text, $user_id = null) {
        global $wpdb;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Verify user can access this media
        if (!Family_Media_Manager_Sharing::user_can_access($media_id, $user_id)) {
            return false;
        }
        
        $table = $wpdb->prefix . 'family_comments';
        
        $result = $wpdb->insert($table, array(
            'media_id'     => $media_id,
            'user_id'      => $user_id,
            'comment_text' => sanitize_textarea_field($comment_text),
            'created_date' => current_time('mysql')
        ));
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Get comments for media
     */
    public static function get_comments($media_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_comments';
        
        $comments = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, u.display_name as user_name
             FROM $table c
             LEFT JOIN {$wpdb->prefix}users u ON c.user_id = u.ID
             WHERE c.media_id = %d
             ORDER BY c.created_date ASC",
            $media_id
        ));
        
        return $comments;
    }

    /**
     * Delete comment
     */
    public static function delete_comment($comment_id, $user_id = null) {
        global $wpdb;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $table = $wpdb->prefix . 'family_comments';
        
        // Get comment to verify ownership
        $comment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $comment_id
        ));
        
        if (!$comment) {
            return false;
        }
        
        // Only comment owner or media owner can delete
        $media = Family_Media_Manager_Media_Library::get_media_by_id($comment->media_id);
        
        if ($comment->user_id != $user_id && $media->owner_id != $user_id) {
            return false;
        }
        
        return $wpdb->delete($table, array('id' => $comment_id));
    }

    /**
     * Get comment count for media
     */
    public static function get_comment_count($media_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_comments';
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE media_id = %d",
            $media_id
        ));
    }

    /**
     * Create comments table (for activator)
     */
    public static function create_table() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_comments';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            media_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            comment_text TEXT NOT NULL,
            created_date DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY media_id (media_id),
            KEY user_id (user_id),
            KEY created_date (created_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
