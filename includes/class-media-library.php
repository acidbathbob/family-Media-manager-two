<?php
/**
 * Media library management class
 * Handles database operations for media items
 */
class Family_Media_Manager_Media_Library {

    /**
     * Get media items with pagination
     */
    public static function get_media($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'page'     => 1,
            'per_page' => 20,
            'user_id'  => null,
            'album_id' => null,
            'order_by' => 'upload_date',
            'order'    => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table = $wpdb->prefix . 'family_media';
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        $where = array('1=1');
        
        if ($args['user_id']) {
            $where[] = $wpdb->prepare('owner_id = %d', $args['user_id']);
        }
        
        if ($args['album_id']) {
            $where[] = $wpdb->prepare('album_id = %d', $args['album_id']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM $table 
                WHERE $where_clause 
                ORDER BY {$args['order_by']} {$args['order']} 
                LIMIT %d OFFSET %d";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $args['per_page'], $offset));
        
        // Get total count
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where_clause");
        
        return array(
            'media' => $results,
            'total' => (int) $total,
            'pages' => ceil($total / $args['per_page'])
        );
    }

    /**
     * Get single media item by ID
     */
    public static function get_media_by_id($media_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_media';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $media_id
        ));
    }

    /**
     * Add new media item to library
     */
    public static function add_media($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_media';
        
        $defaults = array(
            'owner_id'        => get_current_user_id(),
            'cloud_provider'  => 'google_drive',
            'cloud_file_id'   => '',
            'filename'        => '',
            'file_type'       => 'photo',
            'file_size'       => 0,
            'thumbnail_path'  => null,
            'upload_date'     => current_time('mysql'),
            'taken_date'      => null,
            'album_id'        => null,
            'caption'         => ''
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert($table, $data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Update media item
     */
    public static function update_media($media_id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_media';
        
        return $wpdb->update(
            $table,
            $data,
            array('id' => $media_id)
        );
    }

    /**
     * Delete media item
     */
    public static function delete_media($media_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_media';
        
        // Also delete sharing permissions
        $sharing_table = $wpdb->prefix . 'family_sharing';
        $wpdb->delete($sharing_table, array('media_id' => $media_id));
        
        return $wpdb->delete($table, array('id' => $media_id));
    }

    /**
     * Get media shared with current user
     */
    public static function get_shared_media($user_id = null) {
        global $wpdb;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $media_table = $wpdb->prefix . 'family_media';
        $sharing_table = $wpdb->prefix . 'family_sharing';
        
        $sql = "SELECT m.* 
                FROM $media_table m
                INNER JOIN $sharing_table s ON m.id = s.media_id
                WHERE s.shared_with_user_id = %d
                ORDER BY m.upload_date DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $user_id));
    }
}
