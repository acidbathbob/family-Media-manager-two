<?php
/**
 * Fired during plugin deactivation
 */
class Family_Media_Manager_Deactivator {

    /**
     * Clean up on deactivation
     */
    public static function deactivate() {
        // Clear scheduled tasks if any
        wp_clear_scheduled_hook('family_media_manager_cleanup');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
