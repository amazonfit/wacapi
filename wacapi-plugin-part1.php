<?php
/**
 * Plugin Name: Wacapi Integration - WhatsApp Care & Marketing Plugin
 * Plugin URI: https://abanahomes.com/
 * Description: Integration with WhatsApp Cloud API for customer care and marketing campaigns for plant owners
 * Version: 1.0.0
 * Author: Abana Homes
 * Author URI: https://abanahomes.com/
 * Text Domain: wacapi
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WACAPI_VERSION', '1.0.0');
define('WACAPI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WACAPI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WACAPI_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Wacapi_Integration {

    /**
     * Instance of this class
     */
    protected static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the plugin
        $this->init();
        
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Include required files
        $this->includes();
        
        // Initialize database
        add_action('plugins_loaded', array($this, 'init_database'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register assets
        add_action('wp_enqueue_scripts', array($this, 'register_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
        
        // Register shortcodes
        add_shortcode('wacapi_registration_form', array($this, 'registration_form_shortcode'));
    }

    /**
     * Include required files
     */
    private function includes() {
        // Core functionality
        require_once WACAPI_PLUGIN_DIR . 'includes/class-wacapi-db.php';
        require_once WACAPI_PLUGIN_DIR . 'includes/class-wacapi-registration.php';
        require_once WACAPI_PLUGIN_DIR . 'includes/class-wacapi-admin.php';
        
        // Helpers and utilities
        require_once WACAPI_PLUGIN_DIR . 'includes/helpers.php';
    }

    /**
     * Initialize database tables
     */
    public function init_database() {
        $db = new Wacapi_DB();
        $db->create_tables();
    }

    /**
     * Plugin activation hook
     */
    public function activate() {
        // Create database tables
        $this->init_database();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Register frontend assets
     */
    public function register_frontend_assets() {
        // CSS
        wp_enqueue_style(
            'wacapi-frontend',
            WACAPI_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WACAPI_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'wacapi-frontend',
            WACAPI_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            WACAPI_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('wacapi-frontend', 'wacapi', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wacapi-nonce')
        ));
    }

    /**
     * Register admin assets
     */
    public function register_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wacapi') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'wacapi-admin',
            WACAPI_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WACAPI_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'wacapi-admin',
            WACAPI_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WACAPI_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('wacapi-admin', 'wacapi', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wacapi-admin-nonce')
        ));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        $admin = new Wacapi_Admin();
        $admin->setup_menu();
    }

    /**
     * Registration form shortcode callback
     */
    public function registration_form_shortcode($atts) {
        $registration = new Wacapi_Registration();
        return $registration->render_form($atts);
    }

}

// Initialize the plugin
function wacapi_init() {
    return Wacapi_Integration::get_instance();
}

// Start the plugin
$GLOBALS['wacapi'] = wacapi_init();
