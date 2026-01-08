<?php
/**
 * Sharing permissions management class
 */
class Family_Media_Manager_Sharing {

    /**
     * Share media with a user
     */
    public static function share_media($media_id, $user_id, $can_download = true) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_sharing';
        
        // Check if already shared
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE media_id = %d AND shared_with_user_id = %d",
            $media_id,
            $user_id
        ));
        
        if ($existing) {
            return false; // Already shared
        }
        
        $result = $wpdb->insert($table, array(
            'media_id'             => $media_id,
            'shared_with_user_id'  => $user_id,
            'can_download'         => $can_download ? 1 : 0,
            'shared_date'          => current_time('mysql')
        ));
        
        return $result !== false;
    }

    /**
     * Share media with multiple users
     */
    public static function share_with_multiple($media_id, $user_ids, $can_download = true) {
        $results = array();
        
        foreach ($user_ids as $user_id) {
            $results[$user_id] = self::share_media($media_id, $user_id, $can_download);
        }
        
        return $results;
    }

    /**
     * Unshare media with a user
     */
    public static function unshare_media($media_id, $user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_sharing';
        
        return $wpdb->delete($table, array(
            'media_id'            => $media_id,
            'shared_with_user_id' => $user_id
        ));
    }

    /**
     * Get list of users media is shared with
     */
    public static function get_shared_with($media_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_sharing';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE media_id = %d",
            $media_id
        ));
    }

    /**
     * Check if media is shared with user
     */
    public static function is_shared_with($media_id, $user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_sharing';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE media_id = %d AND shared_with_user_id = %d",
            $media_id,
            $user_id
        ));
        
        return $count > 0;
    }

    /**
     * Check if user can access media (owner or shared)
     */
    public static function user_can_access($media_id, $user_id) {
        $media = Family_Media_Manager_Media_Library::get_media_by_id($media_id);
        
        if (!$media) {
            return false;
        }
        
        // Owner can always access
        if ($media->owner_id == $user_id) {
            return true;
        }
        
        // Check if shared
        return self::is_shared_with($media_id, $user_id);
    }

    /**
     * Share entire album with users
     */
    public static function share_album($album_id, $user_ids, $can_download = true) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_media';
        
        // Get all media in album
        $media_items = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM $table WHERE album_id = %d",
            $album_id
        ));
        
        $results = array();
        
        foreach ($media_items as $media_id) {
            foreach ($user_ids as $user_id) {
                self::share_media($media_id, $user_id, $can_download);
            }
        }
        
        return true;
    }
}
