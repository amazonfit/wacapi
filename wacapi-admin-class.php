<?php
/**
 * Admin functionality for Wacapi Integration
 */

// Exit if accessed directly
if (!defined('WPINC')) {
    die;
}

class Wacapi_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX handlers for admin
        add_action('wp_ajax_wacapi_get_registrations', array($this, 'ajax_get_registrations'));
        add_action('wp_ajax_wacapi_delete_registration', array($this, 'ajax_delete_registration'));
        add_action('wp_ajax_wacapi_export_registrations', array($this, 'ajax_export_registrations'));
    }

    /**
     * Setup admin menu
     */
    public function setup_menu() {
        // Main menu
        add_menu_page(
            __('WhatsApp Care & Marketing', 'wacapi'),
            __('WhatsApp Care', 'wacapi'),
            'manage_options',
            'wacapi-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-format-chat',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            'wacapi-dashboard',
            __('Dashboard', 'wacapi'),
            __('Dashboard', 'wacapi'),
            'manage_options',
            'wacapi-dashboard',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'wacapi-dashboard',
            __('Registrations', 'wacapi'),
            __('Registrations', 'wacapi'),
            'manage_options',
            'wacapi-registrations',
            array($this, 'render_registrations')
        );
        
        add_submenu_page(
            'wacapi-dashboard',
            __('Campaigns', 'wacapi'),
            __('Campaigns', 'wacapi'),
            'manage_options',
            'wacapi-campaigns',
            array($this, 'render_campaigns')
        );
        
        add_submenu_page(
            'wacapi-dashboard',
            __('Inbox', 'wacapi'),
            __('Inbox', 'wacapi'),
            'manage_options',
            'wacapi-inbox',
            array($this, 'render_inbox')
        );
        
        add_submenu_page(
            'wacapi-dashboard',
            __('Message Logs', 'wacapi'),
            __('Message Logs', 'wacapi'),
            'manage_options',
            'wacapi-message-logs',
            array($this, 'render_message_logs')
        );
        
        add_submenu_page(
            'wacapi-dashboard',
            __('Templates', 'wacapi'),
            __('Templates', 'wacapi'),
            'manage_options',
            'wacapi-templates',
            array($this, 'render_templates')
        );
        
        add_submenu_page(
            'wacapi-dashboard',
            __('Settings', 'wacapi'),
            __('Settings', 'wacapi'),
            'manage_options',
            'wacapi-settings',
            array($this, 'render_settings')
        );
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        // Verify user access
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wacapi'));
        }
        
        // Include dashboard view
        include WACAPI_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Render registrations page
     */
    public function render_registrations() {
        // Verify user access
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wacapi'));
        }
        
        // Include registrations view
        include WACAPI_PLUGIN_DIR . 'admin/views/registrations.php';
    }
    
    /**
     * Render campaigns page
     */
    public function render_campaigns() {
        // Verify user access
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wacapi'));
        }
        
        // Include campaigns view
        include WACAPI_PLUGIN_DIR . 'admin/views/campaigns.php';
    }
    
    /**
     * Render inbox page
     */
    public function render_inbox() {
        // Verify user access
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wacapi'));
        }
        
        // Include inbox view
        include WACAPI_PLUGIN_DIR . 'admin/views/inbox.php';
    }
    
    /**
     * Render message logs page
     */
    public function render_message_logs() {
        // Verify user access
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wacapi'));
        }
        
        // Include message logs view
        include WACAPI_PLUGIN_DIR . 'admin/views/message-logs.php';
    }
    
    /**
     * Render templates page
     */
    public function render_templates() {
        // Verify user access
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wacapi'));
        }
        
        // Include templates view
        include WACAPI_PLUGIN_DIR . 'admin/views/templates.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings() {
        // Verify user access
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wacapi'));
        }
        
        // Process form submission
        if (isset($_POST['wacapi_settings_nonce']) && wp_verify_nonce($_POST['wacapi_settings_nonce'], 'wacapi_settings')) {
            $this->save_settings();
        }
        
        // Include settings view
        include WACAPI_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Save plugin settings
     */
    private function save_settings() {
        // WhatsApp API settings
        $whatsapp_api_token = sanitize_text_field($_POST['whatsapp_api_token']);
        $whatsapp_phone_number_id = sanitize_text_field($_POST['whatsapp_phone_number_id']);
        $whatsapp_business_id = sanitize_text_field($_POST['whatsapp_business_id']);
        
        // Update options
        update_option('wacapi_whatsapp_api_token', $whatsapp_api_token);
        update_option('wacapi_whatsapp_phone_number_id', $whatsapp_phone_number_id);
        update_option('wacapi_whatsapp_business_id', $whatsapp_business_id);
        
        // Set notification
        add_settings_error(
            'wacapi_settings',
            'wacapi_settings_updated',
            __('Settings saved successfully.', 'wacapi'),
            'updated'
        );
    }
    
    /**
     * AJAX handler for getting registrations
     */
    public function ajax_get_registrations() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wacapi-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wacapi')));
        }
        
        // Get args from request
        $args = array();
        
        if (!empty($_POST['search'])) {
            $args['search'] = sanitize_text_field($_POST['search']);
        }
        
        if (!empty($_POST['plant_name'])) {
            $args['plant_name'] = sanitize_text_field($_POST['plant_name']);
        }
        
        if (!empty($_POST['purchase_platform'])) {
            $args['purchase_platform'] = sanitize_text_field($_POST['purchase_platform']);
        }
        
        if (isset($_POST['whatsapp_opt_in'])) {
            $args['whatsapp_opt_in'] = (int) $_POST['whatsapp_opt_in'];
        }
        
        // Get page and per_page
        $page = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? max(10, (int) $_POST['per_page']) : 20;
        
        // Get registrations
        $db = new Wacapi_DB();
        $registrations = $db->get_registrations($args, $page, $per_page);
        
        // Send response
        wp_send_json_success($registrations);
    }
    
    /**
     * AJAX handler for deleting registration
     */
    public function ajax_delete_registration() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wacapi-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wacapi')));
        }
        
        // Check ID
        if (empty($_POST['id'])) {
            wp_send_json_error(array('message' => __('Registration ID is required.', 'wacapi')));
        }
        
        // Delete registration
        $db = new Wacapi_DB();
        $result = $db->delete_registration((int) $_POST['id']);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Registration deleted successfully.', 'wacapi')));
        } else {
            wp_send_json_error(array('message' => __('Error deleting registration.', 'wacapi')));
        }
    }
    
    /**
     * AJAX handler for exporting registrations
     */
    public function ajax_export_registrations() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wacapi-admin-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wacapi')));
        }
        
        // Get args from request
        $args = array();
        
        if (!empty($_POST['search'])) {
            $args['search'] = sanitize_text_field($_POST['search']);
        }
        
        if (!empty($_POST['plant_name'])) {
            $args['plant_name'] = sanitize_text_field($_POST['plant_name']);
        }
        
        if (!empty($_POST['purchase_platform'])) {
            $args['purchase_platform'] = sanitize_text_field($_POST['purchase_platform']);
        }
        
        if (isset($_POST['whatsapp_opt_in'])) {
            $args['whatsapp_opt_in'] = (int) $_POST['whatsapp_opt_in'];
        }
        
        // Get all registrations (no pagination)
        $db = new Wacapi_DB();
        $registrations = $db->get_registrations($args, 1, 10000);
        
        // Prepare CSV data
        $csv_data = array(
            array(
                __('ID', 'wacapi'),
                __('Name', 'wacapi'),
                __('Email', 'wacapi'),
                __('Phone', 'wacapi'),
                __('WhatsApp Opt-in', 'wacapi'),
                __('Plant Name', 'wacapi'),
                __('Purchase Platform', 'wacapi'),
                __('Order ID', 'wacapi'),
                __('Registered At', 'wacapi')
            )
        );
        
        foreach ($registrations['items'] as $registration) {
            $csv_data[] = array(
                $registration['id'],
                $registration['name'],
                $registration['email'],
                $registration['phone'],
                $registration['whatsapp_opt_in'] ? __('Yes', 'wacapi') : __('No', 'wacapi'),
                $registration['plant_name'],
                $registration['purchase_platform'],
                $registration['order_id'],
                $registration['registered_at']
            );
        }
        
        // Generate CSV content
        $csv_content = '';
        foreach ($csv_data as $row) {
            $csv_content .= implode(',', array_map(array($this, 'escape_csv'), $row)) . "\n";
        }
        
        // Send CSV content
        wp_send_json_success(array(
            'csv' => $csv_content,
            'filename' => 'wacapi-registrations-' . date('Y-m-d') . '.csv'
        ));
    }
    
    /**
     * Escape CSV value
     *
     * @param string $value CSV value
     * @return string Escaped value
     */
    private function escape_csv($value) {
        $value = str_replace('"', '""', $value);
        return '"' . $value . '"';
    }
}
