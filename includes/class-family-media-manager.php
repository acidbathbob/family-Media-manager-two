<?php
/**
 * The core plugin class
 */
class Family_Media_Manager {

    /**
     * The loader that's responsible for maintaining and registering all hooks
     */
    protected $loader;

    /**
     * The unique identifier of this plugin
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     */
    protected $version;

    /**
     * Initialize the plugin
     */
    public function __construct() {
        $this->version = FAMILY_MEDIA_MANAGER_VERSION;
        $this->plugin_name = 'family-media-manager';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
    }

    /**
     * Load the required dependencies for this plugin
     */
    private function load_dependencies() {
        // Core classes
        require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-loader.php';
        require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-cloud-storage.php';
        require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-media-library.php';
        require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-sharing.php';
        require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-uploader.php';
        require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-thumbnail.php';
        
        // Admin classes
        require_once FAMILY_MEDIA_MANAGER_PATH . 'admin/class-admin.php';
        
        // Public classes
        require_once FAMILY_MEDIA_MANAGER_PATH . 'public/class-public.php';
        
        // API classes
        require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-api.php';

        $this->loader = new Family_Media_Manager_Loader();
    }

    /**
     * Register all hooks related to admin functionality
     */
    private function define_admin_hooks() {
        $plugin_admin = new Family_Media_Manager_Admin($this->plugin_name, $this->version);

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
    }

    /**
     * Register all hooks related to public-facing functionality
     */
    private function define_public_hooks() {
        $plugin_public = new Family_Media_Manager_Public($this->plugin_name, $this->version);

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Register all hooks related to REST API
     */
    private function define_api_hooks() {
        $plugin_api = new Family_Media_Manager_API($this->plugin_name, $this->version);

        $this->loader->add_action('rest_api_init', $plugin_api, 'register_routes');
    }

    /**
     * Run the loader to execute all hooks
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number
     */
    public function get_version() {
        return $this->version;
    }
}
