<?php
/**
 * Albums management class
 * Handles album creation, editing, and organization
 */
class Family_Media_Manager_Albums {

    /**
     * Create a new album
     */
    public static function create_album($name, $description = '', $owner_id = null) {
        global $wpdb;
        
        if (!$owner_id) {
            $owner_id = get_current_user_id();
        }
        
        $table = $wpdb->prefix . 'family_albums';
        
        $result = $wpdb->insert($table, array(
            'owner_id'     => $owner_id,
            'name'         => sanitize_text_field($name),
            'description'  => sanitize_textarea_field($description),
            'created_date' => current_time('mysql')
        ));
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Get all albums
     */
    public static function get_albums($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'owner_id' => null,
            'order_by' => 'created_date',
            'order'    => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table = $wpdb->prefix . 'family_albums';
        $where = array('1=1');
        
        if ($args['owner_id']) {
            $where[] = $wpdb->prepare('owner_id = %d', $args['owner_id']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT a.*, 
                       COUNT(m.id) as photo_count,
                       MAX(m.upload_date) as last_photo_date
                FROM $table a
                LEFT JOIN {$wpdb->prefix}family_media m ON a.id = m.album_id
                WHERE $where_clause
                GROUP BY a.id
                ORDER BY a.{$args['order_by']} {$args['order']}";
        
        return $wpdb->get_results($sql);
    }

    /**
     * Get album by ID
     */
    public static function get_album($album_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_albums';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $album_id
        ));
    }

    /**
     * Update album
     */
    public static function update_album($album_id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_albums';
        
        $allowed_fields = array('name', 'description', 'cover_media_id');
        $update_data = array();
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $table,
            $update_data,
            array('id' => $album_id)
        );
    }

    /**
     * Delete album
     */
    public static function delete_album($album_id) {
        global $wpdb;
        
        // Remove album ID from media items (don't delete media)
        $media_table = $wpdb->prefix . 'family_media';
        $wpdb->update(
            $media_table,
            array('album_id' => null),
            array('album_id' => $album_id)
        );
        
        // Delete album
        $table = $wpdb->prefix . 'family_albums';
        return $wpdb->delete($table, array('id' => $album_id));
    }

    /**
     * Add media to album
     */
    public static function add_media_to_album($media_id, $album_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_media';
        
        return $wpdb->update(
            $table,
            array('album_id' => $album_id),
            array('id' => $media_id)
        );
    }

    /**
     * Remove media from album
     */
    public static function remove_media_from_album($media_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_media';
        
        return $wpdb->update(
            $table,
            array('album_id' => null),
            array('id' => $media_id)
        );
    }

    /**
     * Get media in album
     */
    public static function get_album_media($album_id, $args = array()) {
        $defaults = array(
            'page'     => 1,
            'per_page' => 20,
            'album_id' => $album_id
        );
        
        $args = wp_parse_args($args, $defaults);
        
        return Family_Media_Manager_Media_Library::get_media($args);
    }

    /**
     * Set album cover photo
     */
    public static function set_cover_photo($album_id, $media_id) {
        return self::update_album($album_id, array(
            'cover_media_id' => $media_id
        ));
    }

    /**
     * Check if user can access album
     */
    public static function user_can_access($album_id, $user_id) {
        $album = self::get_album($album_id);
        
        if (!$album) {
            return false;
        }
        
        // Owner can always access
        if ($album->owner_id == $user_id) {
            return true;
        }
        
        // Check if any media in album is shared with user
        global $wpdb;
        
        $media_table = $wpdb->prefix . 'family_media';
        $sharing_table = $wpdb->prefix . 'family_sharing';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
             FROM $media_table m
             INNER JOIN $sharing_table s ON m.id = s.media_id
             WHERE m.album_id = %d AND s.shared_with_user_id = %d",
            $album_id,
            $user_id
        ));
        
        return $count > 0;
    }
}
