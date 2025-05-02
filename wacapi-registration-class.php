<?php
/**
 * Registration functionality for Wacapi Integration
 */

// Exit if accessed directly
if (!defined('WPINC')) {
    die;
}

class Wacapi_Registration {

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_wacapi_register_plant', array($this, 'handle_registration'));
        add_action('wp_ajax_nopriv_wacapi_register_plant', array($this, 'handle_registration'));
    }

    /**
     * Render the registration form
     *
     * @param array $atts Shortcode attributes
     * @return string Form HTML
     */
    public function render_form($atts = array()) {
        $atts = shortcode_atts(array(
            'title' => __('Register Your Plant', 'wacapi'),
            'redirect' => 'auto', // auto, none, or custom URL
        ), $atts, 'wacapi_registration_form');
        
        // Get available plants for dropdown
        $plants = $this->get_available_plants();
        
        // Start output buffering
        ob_start();
        
        // Form container
        echo '<div class="wacapi-registration-form-container">';
        
        // Form title
        if (!empty($atts['title'])) {
            echo '<h2>' . esc_html($atts['title']) . '</h2>';
        }
        
        // Form
        ?>
        <form id="wacapi-registration-form" class="wacapi-form" method="post">
            <?php wp_nonce_field('wacapi_registration', 'wacapi_nonce'); ?>
            
            <div class="wacapi-form-field">
                <label for="wacapi-name"><?php _e('Your Name', 'wacapi'); ?> <span class="required">*</span></label>
                <input type="text" id="wacapi-name" name="name" required>
            </div>
            
            <div class="wacapi-form-field">
                <label for="wacapi-email"><?php _e('Email Address', 'wacapi'); ?> <span class="required">*</span></label>
                <input type="email" id="wacapi-email" name="email" required>
            </div>
            
            <div class="wacapi-form-field">
                <label for="wacapi-phone"><?php _e('Phone Number (WhatsApp)', 'wacapi'); ?> <span class="required">*</span></label>
                <input type="tel" id="wacapi-phone" name="phone" required>
                <p class="field-description"><?php _e('Please include country code (e.g., +1 for US)', 'wacapi'); ?></p>
            </div>
            
            <div class="wacapi-form-field checkbox-field">
                <input type="checkbox" id="wacapi-whatsapp-opt-in" name="whatsapp_opt_in" value="1" checked>
                <label for="wacapi-whatsapp-opt-in"><?php _e('I agree to receive plant care tips and offers via WhatsApp', 'wacapi'); ?></label>
            </div>
            
            <div class="wacapi-form-field">
                <label for="wacapi-plant-name"><?php _e('Plant Type', 'wacapi'); ?> <span class="required">*</span></label>
                <select id="wacapi-plant-name" name="plant_name" required>
                    <option value=""><?php _e('Select your plant', 'wacapi'); ?></option>
                    <?php foreach ($plants as $plant_key => $plant_name): ?>
                        <option value="<?php echo esc_attr($plant_key); ?>"><?php echo esc_html($plant_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="wacapi-form-field">
                <label for="wacapi-purchase-platform"><?php _e('Where did you purchase?', 'wacapi'); ?> <span class="required">*</span></label>
                <select id="wacapi-purchase-platform" name="purchase_platform" required>
                    <option value=""><?php _e('Select platform', 'wacapi'); ?></option>
                    <option value="amazon"><?php _e('Amazon', 'wacapi'); ?></option>
                    <option value="website"><?php _e('Our Website', 'wacapi'); ?></option>
                    <option value="other"><?php _e('Other', 'wacapi'); ?></option>
                </select>
            </div>
            
            <div class="wacapi-form-field">
                <label for="wacapi-order-id"><?php _e('Order ID (optional)', 'wacapi'); ?></label>
                <input type="text" id="wacapi-order-id" name="order_id">
            </div>
            
            <div class="wacapi-form-field">
                <input type="hidden" name="redirect" value="<?php echo esc_attr($atts['redirect']); ?>">
                <button type="submit" class="wacapi-submit-button"><?php _e('Register My Plant', 'wacapi'); ?></button>
            </div>
            
            <div class="wacapi-form-message"></div>
        </form>
        <?php
        
        echo '</div>'; // Close form container
        
        // Add script
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#wacapi-registration-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    var form = $(this);
                    var message = form.find('.wacapi-form-message');
                    
                    message.html('<?php _e('Processing...', 'wacapi'); ?>').removeClass('error success').addClass('info');
                    
                    $.ajax({
                        url: wacapi.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'wacapi_register_plant',
                            nonce: wacapi.nonce,
                            name: form.find('#wacapi-name').val(),
                            email: form.find('#wacapi-email').val(),
                            phone: form.find('#wacapi-phone').val(),
                            whatsapp_opt_in: form.find('#wacapi-whatsapp-opt-in').is(':checked') ? 1 : 0,
                            plant_name: form.find('#wacapi-plant-name').val(),
                            purchase_platform: form.find('#wacapi-purchase-platform').val(),
                            order_id: form.find('#wacapi-order-id').val(),
                            redirect: form.find('input[name="redirect"]').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                message.html(response.data.message).removeClass('error info').addClass('success');
                                
                                // Reset form
                                form[0].reset();
                                
                                // Handle redirect
                                if (response.data.redirect) {
                                    setTimeout(function() {
                                        window.location.href = response.data.redirect;
                                    }, 1000);
                                }
                            } else {
                                message.html(response.data.message).removeClass('success info').addClass('error');
                            }
                        },
                        error: function() {
                            message.html('<?php _e('Error processing request. Please try again.', 'wacapi'); ?>').removeClass('success info').addClass('error');
                        }
                    });
                });
            });
        </script>
        <?php
        
        // Return buffered content
        return ob_get_clean();
    }
    
    /**
     * Handle registration form submission via AJAX
     */
    public function handle_registration() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wacapi-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wacapi')));
        }
        
        // Validate required fields
        $required_fields = array('name', 'email', 'phone', 'plant_name', 'purchase_platform');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => __('Please fill in all required fields.', 'wacapi')));
            }
        }
        
        // Validate email
        if (!is_email($_POST['email'])) {
            wp_send_json_error(array('message' => __('Please enter a valid email address.', 'wacapi')));
        }
        
        // Validate phone (basic validation - should be enhanced for production)
        if (!preg_match('/^\+?[0-9]{10,15}$/', trim($_POST['phone']))) {
            wp_send_json_error(array('message' => __('Please enter a valid phone number with country code.', 'wacapi')));
        }
        
        // Prepare data
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'whatsapp_opt_in' => isset($_POST['whatsapp_opt_in']) ? 1 : 0,
            'plant_name' => sanitize_text_field($_POST['plant_name']),
            'purchase_platform' => sanitize_text_field($_POST['purchase_platform']),
            'order_id' => !empty($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : null
        );
        
        // Save registration
        $db = new Wacapi_DB();
        $registration_id = $db->add_registration($data);
        
        if (!$registration_id) {
            wp_send_json_error(array('message' => __('Error saving registration. Please try again.', 'wacapi')));
        }
        
        // Determine redirect URL
        $redirect_url = null;
        if (isset($_POST['redirect'])) {
            if ($_POST['redirect'] === 'auto') {
                // Auto redirect to plant care page
                $redirect_url = home_url('/plant-care/' . sanitize_title($data['plant_name']));
            } elseif ($_POST['redirect'] !== 'none' && filter_var($_POST['redirect'], FILTER_VALIDATE_URL)) {
                // Custom URL
                $redirect_url = $_POST['redirect'];
            }
        }
        
        // Trigger actions for campaign scheduling (will be implemented in Part 2)
        do_action('wacapi_registration_completed', $registration_id, $data);
        
        // Return success
        wp_send_json_success(array(
            'message' => __('Registration successful! Redirecting to plant care guide...', 'wacapi'),
            'redirect' => $redirect_url,
            'registration_id' => $registration_id
        ));
    }
    
    /**
     * Get available plants for dropdown
     * 
     * @return array Plants array
     */
    private function get_available_plants() {
        // This could be enhanced to pull from custom post types or taxonomy
        return apply_filters('wacapi_available_plants', array(
            'ficus' => __('Ficus Bonsai', 'wacapi'),
            'jade' => __('Jade Bonsai', 'wacapi'),
            'juniper' => __('Juniper Bonsai', 'wacapi'),
            'pine' => __('Pine Bonsai', 'wacapi'),
            'maple' => __('Japanese Maple Bonsai', 'wacapi'),
            'azalea' => __('Azalea Bonsai', 'wacapi'),
            'serissa' => __('Serissa Bonsai', 'wacapi'),
            'carmona' => __('Fukien Tea / Carmona Bonsai', 'wacapi'),
            'chinese_elm' => __('Chinese Elm Bonsai', 'wacapi'),
            'schefflera' => __('Hawaiian Umbrella (Schefflera) Bonsai', 'wacapi')
        ));
    }
}
