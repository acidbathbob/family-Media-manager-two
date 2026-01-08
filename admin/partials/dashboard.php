<?php
/**
 * Admin dashboard page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
global $wpdb;
$media_table = $wpdb->prefix . 'family_media';
$total_media = $wpdb->get_var("SELECT COUNT(*) FROM $media_table");
$total_photos = $wpdb->get_var("SELECT COUNT(*) FROM $media_table WHERE file_type = 'photo'");
$total_videos = $wpdb->get_var("SELECT COUNT(*) FROM $media_table WHERE file_type = 'video'");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="family-media-dashboard">
        <div class="dashboard-stats">
            <div class="stat-box">
                <h3><?php echo esc_html($total_media); ?></h3>
                <p>Total Media Items</p>
            </div>
            <div class="stat-box">
                <h3><?php echo esc_html($total_photos); ?></h3>
                <p>Photos</p>
            </div>
            <div class="stat-box">
                <h3><?php echo esc_html($total_videos); ?></h3>
                <p>Videos</p>
            </div>
        </div>

        <div class="dashboard-quick-actions">
            <h2>Quick Actions</h2>
            <p>
                <a href="<?php echo admin_url('admin.php?page=family-media-manager-settings'); ?>" class="button button-primary button-large">
                    Configure Settings
                </a>
                <a href="<?php echo admin_url('admin.php?page=family-media-manager-members'); ?>" class="button button-large">
                    Manage Family Members
                </a>
            </p>
        </div>

        <div class="dashboard-info">
            <h2>Getting Started</h2>
            <ol>
                <li>Configure your Google Drive API credentials in <a href="<?php echo admin_url('admin.php?page=family-media-manager-settings'); ?>">Settings</a></li>
                <li>Connect your Google Drive account</li>
                <li>Add family members who can access the gallery</li>
                <li>Start uploading photos and videos!</li>
            </ol>
        </div>
    </div>
</div>
