<?php
/**
 * Database operations for Wacapi Integration
 */

// Exit if accessed directly
if (!defined('WPINC')) {
    die;
}

class Wacapi_DB {

    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Registrations table
        $table_registrations = $wpdb->prefix . 'wacapi_registrations';
        $sql_registrations = "CREATE TABLE IF NOT EXISTS $table_registrations (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20) NOT NULL,
            whatsapp_opt_in tinyint(1) DEFAULT 1,
            plant_name varchar(255) NOT NULL,
            purchase_platform varchar(50) NOT NULL,
            order_id varchar(50) DEFAULT NULL,
            registered_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY phone (phone),
            KEY email (email)
        ) $charset_collate;";
        
        // Campaigns table
        $table_campaigns = $wpdb->prefix . 'wacapi_campaigns';
        $sql_campaigns = "CREATE TABLE IF NOT EXISTS $table_campaigns (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status)
        ) $charset_collate;";
        
        // Message queue table
        $table_message_queue = $wpdb->prefix . 'wacapi_message_queue';
        $sql_message_queue = "CREATE TABLE IF NOT EXISTS $table_message_queue (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            registration_id bigint(20) unsigned NOT NULL,
            campaign_id bigint(20) unsigned DEFAULT NULL,
            template_id varchar(255) DEFAULT NULL,
            scheduled_at datetime NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            message_data text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY registration_id (registration_id),
            KEY campaign_id (campaign_id),
            KEY status (status),
            KEY scheduled_at (scheduled_at)
        ) $charset_collate;";
        
        // Message log table
        $table_message_log = $wpdb->prefix . 'wacapi_message_log';
        $sql_message_log = "CREATE TABLE IF NOT EXISTS $table_message_log (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            registration_id bigint(20) unsigned NOT NULL,
            campaign_id bigint(20) unsigned DEFAULT NULL,
            direction varchar(10) NOT NULL,  -- outgoing/incoming
            message_type varchar(20) NOT NULL,
            message_id varchar(255) DEFAULT NULL,
            message_content text NOT NULL,
            media_url varchar(255) DEFAULT NULL,
            status varchar(20) DEFAULT NULL,
            sent_at datetime DEFAULT NULL,
            delivered_at datetime DEFAULT NULL,
            read_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY registration_id (registration_id),
            KEY campaign_id (campaign_id),
            KEY message_id (message_id),
            KEY direction (direction)
        ) $charset_collate;";
        
        // Inbox messages table
        $table_inbox_messages = $wpdb->prefix . 'wacapi_inbox_messages';
        $sql_inbox_messages = "CREATE TABLE IF NOT EXISTS $table_inbox_messages (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            phone varchar(20) NOT NULL,
            registration_id bigint(20) unsigned DEFAULT NULL,
            direction varchar(10) NOT NULL,  -- outgoing/incoming
            message_id varchar(255) DEFAULT NULL,
            message_content text NOT NULL,
            media_url varchar(255) DEFAULT NULL,
            status varchar(20) DEFAULT NULL,
            is_read tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY registration_id (registration_id),
            KEY phone (phone),
            KEY direction (direction),
            KEY is_read (is_read)
        ) $charset_collate;";
        
        // Templates table
        $table_templates = $wpdb->prefix . 'wacapi_templates';
        $sql_templates = "CREATE TABLE IF NOT EXISTS $table_templates (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            template_name varchar(255) NOT NULL,
            template_id varchar(255) DEFAULT NULL,
            template_content text NOT NULL,
            template_variables text DEFAULT NULL,
            template_status varchar(20) DEFAULT 'draft',
            template_category varchar(50) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY template_name (template_name),
            KEY template_status (template_status)
        ) $charset_collate;";
        
        // Execute database creation with dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_registrations);
        dbDelta($sql_campaigns);
        dbDelta($sql_message_queue);
        dbDelta($sql_message_log);
        dbDelta($sql_inbox_messages);
        dbDelta($sql_templates);
    }
    
    /**
     * Get registrations with optional filtering
     *
     * @param array $args Filter arguments
     * @param int $page Page number
     * @param int $per_page Items per page
     * @return array
     */
    public function get_registrations($args = array(), $page = 1, $per_page = 20) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wacapi_registrations';
        $where = array('1=1');
        $values = array();
        
        // Apply filters
        if (!empty($args['plant_name'])) {
            $where[] = 'plant_name = %s';
            $values[] = $args['plant_name'];
        }
        
        if (!empty($args['purchase_platform'])) {
            $where[] = 'purchase_platform = %s';
            $values[] = $args['purchase_platform'];
        }
        
        if (!empty($args['whatsapp_opt_in'])) {
            $where[] = 'whatsapp_opt_in = %d';
            $values[] = (int) $args['whatsapp_opt_in'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }
        
        // Calculate offset
        $offset = ($page - 1) * $per_page;
        
        // Prepare the WHERE clause
        $where_clause = implode(' AND ', $where);
        
        // Count total
        $count_query = "SELECT COUNT(id) FROM $table WHERE $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($count_query, $values));
        
        // Get results
        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY registered_at DESC LIMIT %d OFFSET %d";
        $values[] = $per_page;
        $values[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($query, $values), ARRAY_A);
        
        return array(
            'items' => $results,
            'total' => (int) $total,
            'pages' => ceil($total / $per_page),
            'page' => $page,
        );
    }
    
    /**
     * Add a new registration
     *
     * @param array $data Registration data
     * @return int|false Registration ID or false on failure
     */
    public function add_registration($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wacapi_registrations';
        
        $result = $wpdb->insert(
            $table,
            array(
                'name' => sanitize_text_field($data['name']),
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone']),
                'whatsapp_opt_in' => isset($data['whatsapp_opt_in']) ? 1 : 0,
                'plant_name' => sanitize_text_field($data['plant_name']),
                'purchase_platform' => sanitize_text_field($data['purchase_platform']),
                'order_id' => !empty($data['order_id']) ? sanitize_text_field($data['order_id']) : null,
                'registered_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get a single registration by ID
     *
     * @param int $id Registration ID
     * @return object|null Registration data or null if not found
     */
    public function get_registration($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wacapi_registrations';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)
        );
    }
    
    /**
     * Get a registration by phone number
     *
     * @param string $phone Phone number
     * @return object|null Registration data or null if not found
     */
    public function get_registration_by_phone($phone) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wacapi_registrations';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE phone = %s", $phone)
        );
    }
    
    /**
     * Delete a registration
     *
     * @param int $id Registration ID
     * @return bool Success status
     */
    public function delete_registration($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wacapi_registrations';
        
        return $wpdb->delete(
            $table,
            array('id' => $id),
            array('%d')
        );
    }
}
