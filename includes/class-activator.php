<?php
/**
 * Fired during plugin activation
 */
class Family_Media_Manager_Activator {

    /**
     * Create database tables and set up initial plugin data
     */
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Media items table
        $table_media = $wpdb->prefix . 'family_media';
        $sql_media = "CREATE TABLE $table_media (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            owner_id BIGINT(20) UNSIGNED NOT NULL,
            cloud_provider VARCHAR(50) NOT NULL,
            cloud_file_id VARCHAR(255) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            file_size BIGINT(20) UNSIGNED NOT NULL,
            thumbnail_path VARCHAR(255) DEFAULT NULL,
            upload_date DATETIME NOT NULL,
            taken_date DATETIME DEFAULT NULL,
            album_id BIGINT(20) UNSIGNED DEFAULT NULL,
            caption TEXT DEFAULT NULL,
            PRIMARY KEY (id),
            KEY owner_id (owner_id),
            KEY upload_date (upload_date),
            KEY album_id (album_id)
        ) $charset_collate;";
        
        // Sharing permissions table
        $table_sharing = $wpdb->prefix . 'family_sharing';
        $sql_sharing = "CREATE TABLE $table_sharing (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            media_id BIGINT(20) UNSIGNED NOT NULL,
            shared_with_user_id BIGINT(20) UNSIGNED NOT NULL,
            can_download TINYINT(1) DEFAULT 1,
            shared_date DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY media_id (media_id),
            KEY shared_with_user_id (shared_with_user_id)
        ) $charset_collate;";
        
        // User cloud storage tokens table
        $table_tokens = $wpdb->prefix . 'family_cloud_tokens';
        $sql_tokens = "CREATE TABLE $table_tokens (
            user_id BIGINT(20) UNSIGNED NOT NULL,
            provider VARCHAR(50) NOT NULL,
            access_token TEXT NOT NULL,
            refresh_token TEXT DEFAULT NULL,
            expires_at DATETIME DEFAULT NULL,
            PRIMARY KEY (user_id, provider),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        // Albums table
        $table_albums = $wpdb->prefix . 'family_albums';
        $sql_albums = "CREATE TABLE $table_albums (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            owner_id BIGINT(20) UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            created_date DATETIME NOT NULL,
            cover_media_id BIGINT(20) UNSIGNED DEFAULT NULL,
            PRIMARY KEY (id),
            KEY owner_id (owner_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_media);
        dbDelta($sql_sharing);
        dbDelta($sql_tokens);
        dbDelta($sql_albums);
        
        // Create comments table
        require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-comments.php';
        Family_Media_Manager_Comments::create_table();
        
        // Set default options
        add_option('family_media_manager_version', FAMILY_MEDIA_MANAGER_VERSION);
        add_option('family_media_manager_thumbnail_size', 300);
        add_option('family_media_manager_photos_per_page', 20);
    }
}
