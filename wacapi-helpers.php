<?php
/**
 * Helper functions for Wacapi Integration
 */

// Exit if accessed directly
if (!defined('WPINC')) {
    die;
}

/**
 * Get plugin settings
 *
 * @param string $key Setting key
 * @param mixed $default Default value
 * @return mixed Setting value
 */
function wacapi_get_setting($key, $default = '') {
    $value = get_option('wacapi_' . $key, $default);
    return $value;
}

/**
 * Format phone number for WhatsApp API
 *
 * @param string $phone Phone number
 * @return string Formatted phone number
 */
function wacapi_format_phone($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Remove leading zeros
    $phone = ltrim($phone, '0');
    
    // If starting with a country code (like +1), make sure it's properly formatted
    if (substr($phone, 0, 1) !== '+') {
        // Add + if not present
        $phone = '+' . $phone;
    }
    
    return $phone;
}

/**
 * Get available plants
 *
 * @return array Plants array
 */
function wacapi_get_plants() {
    $registration = new Wacapi_Registration();
    $plants = $registration->get_available_plants();
    
    return $plants;
}

/**
 * Get plant care URL
 *
 * @param string $plant_name Plant name
 * @return string Plant care URL
 */
function wacapi_get_plant_care_url($plant_name) {
    $url = home_url('/plant-care/' . sanitize_title($plant_name));
    return $url;
}

/**
 * Log debug information
 *
 * @param mixed $data Data to log
 * @param string $label Optional label
 * @return void
 */
function wacapi_log($data, $label = '') {
    // Only log if WP_DEBUG is true
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    // Create log directory if it doesn't exist
    $log_dir = WP_CONTENT_DIR . '/wacapi-logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Create log file
    $log_file = $log_dir . '/debug-' . date('Y-m-d') . '.log';
    
    // Format data for logging
    $formatted_data = print_r($data, true);
    
    // Add label if provided
    if (!empty($label)) {
        $formatted_data = "[{$label}] " . $formatted_data;
    }
    
    // Add timestamp
    $formatted_data = '[' . date('Y-m-d H:i:s') . '] ' . $formatted_data . "\n\n";
    
    // Write to log file
    file_put_contents($log_file, $formatted_data, FILE_APPEND);
}

/**
 * Check if WhatsApp API is configured
 *
 * @return bool True if configured, false otherwise
 */
function wacapi_is_whatsapp_configured() {
    $token = wacapi_get_setting('whatsapp_api_token');
    $phone_number_id = wacapi_get_setting('whatsapp_phone_number_id');
    
    return !empty($token) && !empty($phone_number_id);
}

/**
 * Validate phone number for WhatsApp
 *
 * @param string $phone Phone number
 * @return bool True if valid, false otherwise
 */
function wacapi_validate_phone($phone) {
    // Remove any non-digit characters except +
    $phone = preg_replace('/[^0-9\+]/', '', $phone);
    
    // Must start with + and have 10-15 digits
    if (!preg_match('/^\+[0-9]{10,15}$/', $phone)) {
        return false;
    }
    
    return true;
}

/**
 * Get message templates
 *
 * @return array Templates array
 */
function wacapi_get_templates() {
    global $wpdb;
    
    $table = $wpdb->prefix . 'wacapi_templates';
    
    return $wpdb->get_results("SELECT * FROM $table ORDER BY template_name ASC", ARRAY_A);
}

/**
 * Get a single template by ID
 *
 * @param int $id Template ID
 * @return array|null Template data or null if not found
 */
function wacapi_get_template($id) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'wacapi_templates';
    
    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id),
        ARRAY_A
    );
}

/**
 * Parse template variables from template content
 *
 * @param string $content Template content
 * @return array Variables array
 */
function wacapi_parse_template_variables($content) {
    $variables = array();
    
    // Match {{variable_name}} pattern
    preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $content, $matches);
    
    if (!empty($matches[1])) {
        $variables = array_unique($matches[1]);
    }
    
    return $variables;
}

/**
 * Replace template variables in content
 *
 * @param string $content Template content
 * @param array $data Variable data
 * @return string Processed content
 */
function wacapi_replace_template_variables($content, $data) {
    foreach ($data as $key => $value) {
        $content = str_replace('{{' . $key . '}}', $value, $content);
    }
    
    return $content;
}

/**
 * Create admin notice
 *
 * @param string $message Message
 * @param string $type Notice type (error, warning, success, info)
 * @param bool $dismissible Whether notice is dismissible
 * @return void
 */
function wacapi_admin_notice($message, $type = 'info', $dismissible = true) {
    $class = 'notice notice-' . $type;
    
    if ($dismissible) {
        $class .= ' is-dismissible';
    }
    
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

/**
 * Format date for display
 *
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function wacapi_format_date($date, $format = 'M j, Y g:i a') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = strtotime($date);
    
    return date_i18n($format, $timestamp);
}

/**
 * Generate a random string
 *
 * @param int $length String length
 * @return string Random string
 */
function wacapi_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $random_string;
}

/**
 * Get campaign types
 *
 * @return array Campaign types
 */
function wacapi_get_campaign_types() {
    return array(
        'care' => __('Care Campaign', 'wacapi'),
        'cross_sell' => __('Cross-sell Campaign', 'wacapi'),
        'manual' => __('Manual Campaign', 'wacapi')
    );
}

/**
 * Create or update post-registration redirect page
 *
 * @param string $plant_name Plant name
 * @param string $content Page content
 * @return int Page ID
 */
function wacapi_create_plant_care_page($plant_name, $content = '') {
    $plant_slug = sanitize_title($plant_name);
    $page_title = sprintf(__('Care Guide: %s', 'wacapi'), $plant_name);
    
    // Check if page exists
    $existing_page = get_page_by_path('plant-care/' . $plant_slug);
    
    if ($existing_page) {
        // Update existing page
        $page_id = $existing_page->ID;
        
        // Only update content if provided
        if (!empty($content)) {
            wp_update_post(array(
                'ID' => $page_id,
                'post_content' => $content
            ));
        }
    } else {
        // Create parent page if it doesn't exist
        $parent_page = get_page_by_path('plant-care');
        
        if (!$parent_page) {
            $parent_id = wp_insert_post(array(
                'post_title' => __('Plant Care', 'wacapi'),
                'post_name' => 'plant-care',
                'post_content' => __('Plant care guides for your bonsai trees.', 'wacapi'),
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
        } else {
            $parent_id = $parent_page->ID;
        }
        
        // Create new page
        $page_id = wp_insert_post(array(
            'post_title' => $page_title,
            'post_name' => $plant_slug,
            'post_content' => !empty($content) ? $content : sprintf(__('Care guide for your %s bonsai will be available soon.', 'wacapi'), $plant_name),
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_parent' => $parent_id
        ));
    }
    
    return $page_id;
}
