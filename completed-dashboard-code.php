<?php
// Exit if accessed directly
if (!defined('WPINC')) {
    die;
}

// Get some statistics
global $wpdb;
$registrations_table = $wpdb->prefix . 'wacapi_registrations';
$total_registrations = $wpdb->get_var("SELECT COUNT(id) FROM $registrations_table");
$whatsapp_enabled = $wpdb->get_var("SELECT COUNT(id) FROM $registrations_table WHERE whatsapp_opt_in = 1");
$recent_registrations = $wpdb->get_results(
    "SELECT * FROM $registrations_table ORDER BY registered_at DESC LIMIT 5",
    ARRAY_A
);

// Get popular plants
$popular_plants = $wpdb->get_results(
    "SELECT plant_name, COUNT(id) as count FROM $registrations_table GROUP BY plant_name ORDER BY count DESC LIMIT 5",
    ARRAY_A
);

// Check if WhatsApp API is configured
$whatsapp_configured = wacapi_is_whatsapp_configured();
?>

<div class="wrap wacapi-admin-wrap">
    <h1><?php _e('WhatsApp Care & Marketing Dashboard', 'wacapi'); ?></h1>
    
    <?php if (!$whatsapp_configured): ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('WhatsApp API is not configured. Please configure it in the', 'wacapi'); ?>
                <a href="<?php echo admin_url('admin.php?page=wacapi-settings'); ?>"><?php _e('Settings', 'wacapi'); ?></a>
                <?php _e('page to enable messaging functionality.', 'wacapi'); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="wacapi-dashboard-widgets">
        <div class="wacapi-dashboard-widget">
            <h2><?php _e('Registrations', 'wacapi'); ?></h2>
            <div class="wacapi-stats">
                <div class="wacapi-stat">
                    <span class="wacapi-stat-number"><?php echo esc_html($total_registrations); ?></span>
                    <span class="wacapi-stat-label"><?php _e('Total Registrations', 'wacapi'); ?></span>
                </div>
                <div class="wacapi-stat">
                    <span class="wacapi-stat-number"><?php echo esc_html($whatsapp_enabled); ?></span>
                    <span class="wacapi-stat-label"><?php _e('WhatsApp Opt-ins', 'wacapi'); ?></span>
                </div>
                <div class="wacapi-stat">
                    <span class="wacapi-stat-number"><?php echo $total_registrations > 0 ? round(($whatsapp_enabled / $total_registrations) * 100) . '%' : '0%'; ?></span>
                    <span class="wacapi-stat-label"><?php _e('Opt-in Rate', 'wacapi'); ?></span>
                </div>
            </div>
            <a href="<?php echo admin_url('admin.php?page=wacapi-registrations'); ?>" class="button"><?php _e('View All Registrations', 'wacapi'); ?></a>
        </div>
        
        <div class="wacapi-dashboard-widget">
            <h2><?php _e('Popular Plants', 'wacapi'); ?></h2>
            <?php if (!empty($popular_plants)): ?>
                <ul class="wacapi-plants-list">
                    <?php foreach ($popular_plants as $plant): ?>
                        <li>
                            <span class="plant-name"><?php echo esc_html($plant['plant_name']); ?></span>
                            <span class="plant-count"><?php echo esc_html($plant['count']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php _e('No plant registrations yet.', 'wacapi'); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="wacapi-dashboard-widget">
            <h2><?php _e('Quick Actions', 'wacapi'); ?></h2>
            <div class="wacapi-actions">
                <a href="<?php echo admin_url('admin.php?page=wacapi-campaigns'); ?>" class="button button-primary"><?php _e('Create Campaign', 'wacapi'); ?></a>
                <a href="<?php echo admin_url('admin.php?page=wacapi-inbox'); ?>" class="button"><?php _e('Check Inbox', 'wacapi'); ?></a>
                <a href="<?php echo admin_url('admin.php?page=wacapi-templates'); ?>" class="button"><?php _e('Manage Templates', 'wacapi'); ?></a>
            </div>
        </div>
    </div>
    
    <div class="wacapi-dashboard-row">
        <div class="wacapi-dashboard-column">
            <div class="wacapi-card">
                <h2><?php _e('Recent Registrations', 'wacapi'); ?></h2>
                <?php if (!empty($recent_registrations)): ?>
                    <table class="wacapi-table">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'wacapi'); ?></th>
                                <th><?php _e('Plant', 'wacapi'); ?></th>
                                <th><?php _e('Date', 'wacapi'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_registrations as $registration): ?>
                                <tr>
                                    <td><?php echo esc_html($registration['name']); ?></td>
                                    <td><?php echo esc_html($registration['plant_name']); ?></td>
                                    <td><?php echo wacapi_format_date($registration['registered_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No recent registrations found.', 'wacapi'); ?></p>
                <?php endif; ?>
                <div class="wacapi-card-footer">
                    <a href="<?php echo admin_url('admin.php?page=wacapi-registrations'); ?>"><?php _e('View all', 'wacapi'); ?> &rarr;</a>
                </div>
            </div>
        </div>

        <div class="wacapi-dashboard-column">
            <div class="wacapi-card">
                <h2><?php _e('Recent Messages', 'wacapi'); ?></h2>
                <?php
                $messages_table = $wpdb->prefix . 'wacapi_messages';
                $recent_messages = $wpdb->get_results(
                    "SELECT * FROM $messages_table ORDER BY timestamp DESC LIMIT 5",
                    ARRAY_A
                );
                ?>
                
                <?php if (!empty($recent_messages)): ?>
                    <table class="wacapi-table">
                        <thead>
                            <tr>
                                <th><?php _e('From', 'wacapi'); ?></th>
                                <th><?php _e('Message', 'wacapi'); ?></th>
                                <th><?php _e('Date', 'wacapi'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_messages as $message): ?>
                                <tr>
                                    <td><?php echo esc_html($message['sender_name'] ?? $message['phone_number']); ?></td>
                                    <td><?php echo esc_html(wp_trim_words($message['content'], 10, '...')); ?></td>
                                    <td><?php echo wacapi_format_date($message['timestamp']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No messages found.', 'wacapi'); ?></p>
                <?php endif; ?>
                <div class="wacapi-card-footer">
                    <a href="<?php echo admin_url('admin.php?page=wacapi-inbox'); ?>"><?php _e('View inbox', 'wacapi'); ?> &rarr;</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="wacapi-dashboard-row">
        <div class="wacapi-dashboard-column">
            <div class="wacapi-card">
                <h2><?php _e('Campaign Performance', 'wacapi'); ?></h2>
                <?php
                $campaigns_table = $wpdb->prefix . 'wacapi_campaigns';
                $campaign_stats = $wpdb->get_results(
                    "SELECT c.id, c.name, c.sent_date, COUNT(m.id) as message_count, 
                    SUM(CASE WHEN m.delivery_status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
                    SUM(CASE WHEN m.read_status = 'read' THEN 1 ELSE 0 END) as read_count
                    FROM $campaigns_table c
                    LEFT JOIN " . $wpdb->prefix . "wacapi_campaign_messages m ON c.id = m.campaign_id
                    GROUP BY c.id
                    ORDER BY c.sent_date DESC
                    LIMIT 3",
                    ARRAY_A
                );
                ?>
                
                <?php if (!empty($campaign_stats)): ?>
                    <table class="wacapi-table">
                        <thead>
                            <tr>
                                <th><?php _e('Campaign', 'wacapi'); ?></th>
                                <th><?php _e('Sent', 'wacapi'); ?></th>
                                <th><?php _e('Delivered', 'wacapi'); ?></th>
                                <th><?php _e('Read', 'wacapi'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaign_stats as $campaign): ?>
                                <tr>
                                    <td><?php echo esc_html($campaign['name']); ?></td>
                                    <td><?php echo esc_html($campaign['message_count']); ?></td>
                                    <td>
                                        <?php 
                                        $delivered_percent = $campaign['message_count'] > 0 ? 
                                            round(($campaign['delivered_count'] / $campaign['message_count']) * 100) : 0;
                                        echo esc_html($campaign['delivered_count'] . ' (' . $delivered_percent . '%)');
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $read_percent = $campaign['delivered_count'] > 0 ? 
                                            round(($campaign['read_count'] / $campaign['delivered_count']) * 100) : 0;
                                        echo esc_html($campaign['read_count'] . ' (' . $read_percent . '%)');
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No campaigns found.', 'wacapi'); ?></p>
                <?php endif; ?>
                <div class="wacapi-card-footer">
                    <a href="<?php echo admin_url('admin.php?page=wacapi-campaigns'); ?>"><?php _e('View all campaigns', 'wacapi'); ?> &rarr;</a>
                </div>
            </div>
        </div>
        
        <div class="wacapi-dashboard-column">
            <div class="wacapi-card">
                <h2><?php _e('WhatsApp Status', 'wacapi'); ?></h2>
                <?php
                $api_status = get_transient('wacapi_api_status');
                $last_check = get_option('wacapi_api_last_check', '');
                $messages_sent_today = $wpdb->get_var(
                    "SELECT COUNT(id) FROM " . $wpdb->prefix . "wacapi_messages 
                    WHERE direction = 'outbound' 
                    AND DATE(timestamp) = CURDATE()"
                );
                $daily_limit = get_option('wacapi_daily_message_limit', 1000);
                $limit_used_percent = $daily_limit > 0 ? round(($messages_sent_today / $daily_limit) * 100) : 0;
                ?>
                
                <div class="wacapi-api-status">
                    <div class="wacapi-status-item">
                        <span class="status-label"><?php _e('API Connection', 'wacapi'); ?></span>
                        <span class="status-value <?php echo $api_status ? 'status-good' : 'status-error'; ?>">
                            <?php echo $api_status ? __('Connected', 'wacapi') : __('Disconnected', 'wacapi'); ?>
                        </span>
                    </div>
                    
                    <div class="wacapi-status-item">
                        <span class="status-label"><?php _e('Last Check', 'wacapi'); ?></span>
                        <span class="status-value">
                            <?php echo !empty($last_check) ? wacapi_format_date($last_check) : __('Never', 'wacapi'); ?>
                        </span>
                    </div>
                    
                    <div class="wacapi-status-item">
                        <span class="status-label"><?php _e('Messages Today', 'wacapi'); ?></span>
                        <span class="status-value">
                            <?php echo esc_html($messages_sent_today); ?> / <?php echo esc_html($daily_limit); ?>
                        </span>
                        <div class="wacapi-progress-bar">
                            <div class="wacapi-progress" style="width: <?php echo esc_attr($limit_used_percent); ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="wacapi-card-footer">
                    <button id="wacapi-refresh-status" class="button"><?php _e('Refresh Status', 'wacapi'); ?></button>
                    <a href="<?php echo admin_url('admin.php?page=wacapi-settings'); ?>" class="button"><?php _e('API Settings', 'wacapi'); ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="wacapi-dashboard-footer">
        <p>
            <?php 
            printf(
                __('WhatsApp Care & Marketing API v%s | <a href="%s" target="_blank">Documentation</a> | <a href="%s" target="_blank">Support</a>', 'wacapi'),
                WACAPI_VERSION,
                'https://docs.wacapi.com',
                'https://support.wacapi.com'
            ); 
            ?>
        </p>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#wacapi-refresh-status').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Checking...', 'wacapi'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wacapi_check_api_status',
                nonce: '<?php echo wp_create_nonce('wacapi_check_api'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('Error checking API status', 'wacapi'); ?>');
                    button.prop('disabled', false).text('<?php _e('Refresh Status', 'wacapi'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error checking API status', 'wacapi'); ?>');
                button.prop('disabled', false).text('<?php _e('Refresh Status', 'wacapi'); ?>');
            }
        });
    });
});
</script>
