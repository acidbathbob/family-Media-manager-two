<?php
/**
 * Public-facing functionality
 */
class Family_Media_Manager_Public {

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
     * Register public stylesheets
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            FAMILY_MEDIA_MANAGER_URL . 'public/css/public.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register public JavaScript
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            FAMILY_MEDIA_MANAGER_URL . 'public/js/public.js',
            array('jquery'),
            $this->version,
            false
        );
    }
}
