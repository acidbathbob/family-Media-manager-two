<?php
/**
 * Admin-specific functionality
 */
class Family_Media_Manager_Admin {

    /**
     * Plugin name
     */
    private $plugin_name;

    /**
     * Plugin version
     */
    private $version;

    /**
     * Initialize the class
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register admin stylesheets
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            FAMILY_MEDIA_MANAGER_URL . 'admin/css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register admin JavaScript
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            FAMILY_MEDIA_MANAGER_URL . 'admin/js/admin.js',
            array('jquery'),
            $this->version,
            false
        );
    }

    /**
     * Add plugin admin menu
     */
    public function add_plugin_admin_menu() {
        // Main menu
        add_menu_page(
            'Family Media Manager',
            'Family Gallery',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_dashboard'),
            'dashicons-images-alt2',
            25
        );

        // Dashboard submenu
        add_submenu_page(
            $this->plugin_name,
            'Dashboard',
            'Dashboard',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_dashboard')
        );

        // Settings submenu
        add_submenu_page(
            $this->plugin_name,
            'Settings',
            'Settings',
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_settings')
        );

        // Family Members submenu
        add_submenu_page(
            $this->plugin_name,
            'Family Members',
            'Family Members',
            'manage_options',
            $this->plugin_name . '-members',
            array($this, 'display_members')
        );
    }

    /**
     * Display dashboard page
     */
    public function display_dashboard() {
        require_once FAMILY_MEDIA_MANAGER_PATH . 'admin/partials/dashboard.php';
    }

    /**
     * Display settings page
     */
    public function display_settings() {
        // Handle OAuth callback
        if (isset($_GET['action']) && $_GET['action'] === 'oauth_callback' && isset($_GET['code'])) {
            $this->handle_oauth_callback();
        }

        require_once FAMILY_MEDIA_MANAGER_PATH . 'admin/partials/settings.php';
    }

    /**
     * Display family members page
     */
    public function display_members() {
        require_once FAMILY_MEDIA_MANAGER_PATH . 'admin/partials/family-members.php';
    }

    /**
     * Handle OAuth callback
     */
    private function handle_oauth_callback() {
        $code = sanitize_text_field($_GET['code']);
        $user_id = get_current_user_id();

        $cloud = new Family_Media_Manager_Cloud_Storage($user_id);
        $result = $cloud->handle_callback($code);

        if ($result) {
            add_settings_error(
                'family_media_manager_messages',
                'oauth_success',
                'Successfully connected to Google Drive!',
                'updated'
            );
        } else {
            add_settings_error(
                'family_media_manager_messages',
                'oauth_error',
                'Failed to connect to Google Drive. Please try again.',
                'error'
            );
        }
    }
}
