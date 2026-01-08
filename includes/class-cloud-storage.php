<?php
/**
 * Cloud storage integration class
 * Handles Google Drive API integration
 */
class Family_Media_Manager_Cloud_Storage {

    /**
     * User ID for this cloud storage instance
     */
    private $user_id;

    /**
     * Cloud provider (google_drive, onedrive, etc.)
     */
    private $provider;

    /**
     * Initialize the cloud storage handler
     */
    public function __construct($user_id, $provider = 'google_drive') {
        $this->user_id = $user_id;
        $this->provider = $provider;
    }

    /**
     * Get OAuth authorization URL for user to connect their cloud storage
     */
    public function get_auth_url() {
        if ($this->provider === 'google_drive') {
            return $this->get_google_auth_url();
        }
        
        return false;
    }

    /**
     * Get Google Drive OAuth URL
     */
    private function get_google_auth_url() {
        $client_id = get_option('family_media_manager_google_client_id');
        $redirect_uri = admin_url('admin.php?page=family-media-manager-settings&action=oauth_callback');
        
        $params = array(
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/drive.file',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => wp_create_nonce('family_media_oauth_' . $this->user_id)
        );
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Handle OAuth callback and exchange code for tokens
     */
    public function handle_callback($code) {
        if ($this->provider === 'google_drive') {
            return $this->handle_google_callback($code);
        }
        
        return false;
    }

    /**
     * Handle Google Drive OAuth callback
     */
    private function handle_google_callback($code) {
        $client_id = get_option('family_media_manager_google_client_id');
        $client_secret = get_option('family_media_manager_google_client_secret');
        $redirect_uri = admin_url('admin.php?page=family-media-manager-settings&action=oauth_callback');
        
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code'          => $code,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => $redirect_uri,
                'grant_type'    => 'authorization_code'
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['access_token'])) {
            $this->save_tokens($body['access_token'], $body['refresh_token'] ?? null, $body['expires_in'] ?? 3600);
            return true;
        }
        
        return false;
    }

    /**
     * Save access and refresh tokens to database
     */
    private function save_tokens($access_token, $refresh_token, $expires_in) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_cloud_tokens';
        $expires_at = date('Y-m-d H:i:s', time() + $expires_in);
        
        $wpdb->replace($table, array(
            'user_id'       => $this->user_id,
            'provider'      => $this->provider,
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'expires_at'    => $expires_at
        ));
    }

    /**
     * Get valid access token (refresh if expired)
     */
    private function get_access_token() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'family_cloud_tokens';
        $token_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND provider = %s",
            $this->user_id,
            $this->provider
        ));
        
        if (!$token_data) {
            return false;
        }
        
        // Check if token is expired
        if (strtotime($token_data->expires_at) < time()) {
            // Refresh token
            if ($this->provider === 'google_drive') {
                $this->refresh_google_token($token_data->refresh_token);
                return $this->get_access_token(); // Recursively get new token
            }
        }
        
        return $token_data->access_token;
    }

    /**
     * Refresh Google Drive access token
     */
    private function refresh_google_token($refresh_token) {
        $client_id = get_option('family_media_manager_google_client_id');
        $client_secret = get_option('family_media_manager_google_client_secret');
        
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
                'grant_type'    => 'refresh_token'
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['access_token'])) {
            $this->save_tokens($body['access_token'], $refresh_token, $body['expires_in'] ?? 3600);
            return true;
        }
        
        return false;
    }

    /**
     * Upload file to cloud storage
     */
    public function upload_file($file_path, $filename) {
        $access_token = $this->get_access_token();
        
        if (!$access_token) {
            return array('success' => false, 'error' => 'Not authenticated');
        }
        
        if ($this->provider === 'google_drive') {
            return $this->upload_to_google_drive($file_path, $filename, $access_token);
        }
        
        return array('success' => false, 'error' => 'Unsupported provider');
    }

    /**
     * Upload file to Google Drive
     */
    private function upload_to_google_drive($file_path, $filename, $access_token) {
        // First, create/get the FamilyGallery folder
        $folder_id = $this->get_or_create_folder('FamilyGallery', $access_token);
        
        if (!$folder_id) {
            return array('success' => false, 'error' => 'Could not create folder');
        }
        
        // Upload file
        $boundary = wp_generate_password(32, false);
        $file_content = file_get_contents($file_path);
        $mime_type = mime_content_type($file_path);
        
        $metadata = json_encode(array(
            'name' => $filename,
            'parents' => array($folder_id)
        ));
        
        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= $metadata . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mime_type}\r\n\r\n";
        $body .= $file_content . "\r\n";
        $body .= "--{$boundary}--";
        
        $response = wp_remote_post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'multipart/related; boundary=' . $boundary
            ),
            'body' => $body,
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }
        
        $result = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($result['id'])) {
            return array(
                'success' => true,
                'file_id' => $result['id'],
                'file_name' => $result['name']
            );
        }
        
        return array('success' => false, 'error' => 'Upload failed');
    }

    /**
     * Get or create folder in Google Drive
     */
    private function get_or_create_folder($folder_name, $access_token) {
        // Search for existing folder
        $query = "name='{$folder_name}' and mimeType='application/vnd.google-apps.folder' and trashed=false";
        $response = wp_remote_get('https://www.googleapis.com/drive/v3/files?' . http_build_query(array('q' => $query)), array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            )
        ));
        
        if (!is_wp_error($response)) {
            $result = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($result['files'])) {
                return $result['files'][0]['id'];
            }
        }
        
        // Create folder if not exists
        $metadata = json_encode(array(
            'name' => $folder_name,
            'mimeType' => 'application/vnd.google-apps.folder'
        ));
        
        $response = wp_remote_post('https://www.googleapis.com/drive/v3/files', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => $metadata
        ));
        
        if (!is_wp_error($response)) {
            $result = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($result['id'])) {
                return $result['id'];
            }
        }
        
        return false;
    }

    /**
     * Get download URL for a file
     */
    public function get_download_url($file_id) {
        $access_token = $this->get_access_token();
        
        if (!$access_token) {
            return false;
        }
        
        if ($this->provider === 'google_drive') {
            // Google Drive direct download URL
            return "https://www.googleapis.com/drive/v3/files/{$file_id}?alt=media&access_token={$access_token}";
        }
        
        return false;
    }

    /**
     * Check if user has connected cloud storage
     */
    public function is_connected() {
        return $this->get_access_token() !== false;
    }
}
