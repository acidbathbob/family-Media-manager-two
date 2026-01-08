<?php
/**
 * Admin settings page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['family_media_manager_settings_submit'])) {
    check_admin_referer('family_media_manager_settings');
    
    update_option('family_media_manager_google_client_id', sanitize_text_field($_POST['google_client_id']));
    update_option('family_media_manager_google_client_secret', sanitize_text_field($_POST['google_client_secret']));
    update_option('family_media_manager_thumbnail_size', intval($_POST['thumbnail_size']));
    update_option('family_media_manager_photos_per_page', intval($_POST['photos_per_page']));
    
    echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
}

// Get current settings
$google_client_id = get_option('family_media_manager_google_client_id', '');
$google_client_secret = get_option('family_media_manager_google_client_secret', '');
$thumbnail_size = get_option('family_media_manager_thumbnail_size', 300);
$photos_per_page = get_option('family_media_manager_photos_per_page', 20);

// Check if user has connected cloud storage
$user_id = get_current_user_id();
$cloud = new Family_Media_Manager_Cloud_Storage($user_id);
$is_connected = $cloud->is_connected();

// Display any settings errors
settings_errors('family_media_manager_messages');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('family_media_manager_settings'); ?>
        
        <h2>Google Drive API Settings</h2>
        <p>To use this plugin, you need to create a Google Cloud project and enable the Drive API. 
        <a href="https://console.cloud.google.com" target="_blank">Get started here</a></p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="google_client_id">Google Client ID</label>
                </th>
                <td>
                    <input type="text" 
                           id="google_client_id" 
                           name="google_client_id" 
                           value="<?php echo esc_attr($google_client_id); ?>" 
                           class="regular-text" />
                    <p class="description">Your Google OAuth 2.0 Client ID</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="google_client_secret">Google Client Secret</label>
                </th>
                <td>
                    <input type="text" 
                           id="google_client_secret" 
                           name="google_client_secret" 
                           value="<?php echo esc_attr($google_client_secret); ?>" 
                           class="regular-text" />
                    <p class="description">Your Google OAuth 2.0 Client Secret</p>
                </td>
            </tr>
        </table>

        <h2>Connection Status</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Google Drive</th>
                <td>
                    <?php if ($is_connected): ?>
                        <span style="color: green;">✓ Connected</span>
                    <?php else: ?>
                        <span style="color: red;">✗ Not Connected</span>
                        <?php if (!empty($google_client_id) && !empty($google_client_secret)): ?>
                            <br><br>
                            <a href="<?php echo esc_url($cloud->get_auth_url()); ?>" class="button button-primary">
                                Connect Google Drive
                            </a>
                        <?php else: ?>
                            <p class="description">Please enter your Google API credentials above and save settings first.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <h2>Display Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="thumbnail_size">Thumbnail Size (px)</label>
                </th>
                <td>
                    <input type="number" 
                           id="thumbnail_size" 
                           name="thumbnail_size" 
                           value="<?php echo esc_attr($thumbnail_size); ?>" 
                           min="100" 
                           max="500" />
                    <p class="description">Size of thumbnail images in pixels (default: 300)</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="photos_per_page">Photos Per Page</label>
                </th>
                <td>
                    <input type="number" 
                           id="photos_per_page" 
                           name="photos_per_page" 
                           value="<?php echo esc_attr($photos_per_page); ?>" 
                           min="10" 
                           max="100" />
                    <p class="description">Number of photos to display per page (default: 20)</p>
                </td>
            </tr>
        </table>

        <?php submit_button('Save Settings', 'primary', 'family_media_manager_settings_submit'); ?>
    </form>
</div>
