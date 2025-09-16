<?php
/**
 * Plugin Name: OX Mailchimp Campaign
 * Plugin URI: https://github.com/ox-mailchimp-campaign
 * Description: A WordPress plugin that generates forms for sending email campaigns using Mailchimp API with tag-based audience segmentation. Features include customizable email templates, rich text editor, and duplicate prevention.
 * Version: 1.1.5
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Author: Andy McLeod
 * Author URI: https://differentwines.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ox-mailchimp-campaign
 * Domain Path: /languages
 * Network: false
 * 
 * @package OXMailchimpCampaign
 * @version 1.1.5
 * @author Andy McLeod
 * @license GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MCF_PLUGIN_VERSION', '1.1.5');
define('MCF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MCF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MCF_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once MCF_PLUGIN_DIR . 'includes/class-email-templates.php';

/**
 * Main plugin class
 */
class MailchimpCampaignForm {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_ox_mailchimp_campaign_create_campaign', array($this, 'ajax_create_campaign'));
        add_action('wp_ajax_nopriv_ox_mailchimp_campaign_create_campaign', array($this, 'ajax_create_campaign'));
        add_action('wp_ajax_ox_mailchimp_campaign_get_tags', array($this, 'ajax_get_tags'));
        add_action('wp_ajax_nopriv_ox_mailchimp_campaign_get_tags', array($this, 'ajax_get_tags'));
        add_action('wp_ajax_ox_mailchimp_campaign_get_templates', array($this, 'ajax_get_templates'));
        add_action('wp_ajax_nopriv_ox_mailchimp_campaign_get_templates', array($this, 'ajax_get_templates'));
        add_action('wp_ajax_ox_mailchimp_campaign_get_template_content', array($this, 'ajax_get_template_content'));
        add_action('wp_ajax_nopriv_ox_mailchimp_campaign_get_template_content', array($this, 'ajax_get_template_content'));
        
        // Register shortcode
        add_shortcode('ox_mailchimp_campaign_form', array($this, 'render_form'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for internationalization
        load_plugin_textdomain('ox-mailchimp-campaign', false, dirname(MCF_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script('mcf-script', MCF_PLUGIN_URL . 'assets/js/mcf-script.js', array('jquery'), MCF_PLUGIN_VERSION, true);
        wp_enqueue_style('mcf-style', MCF_PLUGIN_URL . 'assets/css/mcf-style.css', array(), MCF_PLUGIN_VERSION);
        
        // Localize script for AJAX
        wp_localize_script('mcf-script', 'ox_mailchimp_campaign_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ox_mailchimp_campaign_nonce'),
            'submit_text' => get_option('ox_mailchimp_campaign_label_submit', __('Send Campaign', 'ox-mailchimp-campaign')),
            'strings' => array(
                'sending' => __('Sending campaign...', 'ox-mailchimp-campaign'),
                'success' => __('Campaign sent successfully!', 'ox-mailchimp-campaign'),
                'error' => __('Error sending campaign. Please try again.', 'ox-mailchimp-campaign')
            )
        ));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Mailchimp Campaign Form', 'ox-mailchimp-campaign'),
            __('Mailchimp Campaign Form', 'ox-mailchimp-campaign'),
            'manage_options',
            'ox-mailchimp-campaign',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin settings page
     */
    public function admin_page() {
        if (isset($_POST['submit'])) {
            update_option('ox_mailchimp_campaign_api_key', sanitize_text_field($_POST['api_key']));
            update_option('ox_mailchimp_campaign_list_id', sanitize_text_field($_POST['list_id']));
            
                    // Save field labels
        update_option('ox_mailchimp_campaign_label_subject', sanitize_text_field($_POST['label_subject']));
        update_option('ox_mailchimp_campaign_label_title', sanitize_text_field($_POST['label_title']));
        update_option('ox_mailchimp_campaign_label_from_name', sanitize_text_field($_POST['label_from_name']));
        update_option('ox_mailchimp_campaign_label_from_email', sanitize_text_field($_POST['label_from_email']));
        update_option('ox_mailchimp_campaign_label_reply_to', sanitize_text_field($_POST['label_reply_to']));
        update_option('ox_mailchimp_campaign_label_tag', sanitize_text_field($_POST['label_tag']));
        update_option('ox_mailchimp_campaign_label_content', sanitize_text_field($_POST['label_content']));
        update_option('ox_mailchimp_campaign_label_submit', sanitize_text_field($_POST['label_submit']));
        
        // Save to_name format
        update_option('ox_mailchimp_campaign_to_name_format', sanitize_text_field($_POST['to_name_format']));
        
        // Save from_email_override
        update_option('ox_mailchimp_campaign_from_email_override', sanitize_email($_POST['from_email_override']));
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'ox-mailchimp-campaign') . '</p></div>';
        }
        
        $api_key = get_option('ox_mailchimp_campaign_api_key', '');
        $list_id = get_option('ox_mailchimp_campaign_list_id', '');
        
        // Get field labels with defaults
        $label_subject = get_option('ox_mailchimp_campaign_label_subject', __('Subject Line', 'ox-mailchimp-campaign'));
        $label_title = get_option('ox_mailchimp_campaign_label_title', __('Campaign Title', 'ox-mailchimp-campaign'));
        $label_from_name = get_option('ox_mailchimp_campaign_label_from_name', __('From Name', 'ox-mailchimp-campaign'));
        $label_from_email = get_option('ox_mailchimp_campaign_label_from_email', __('From Email', 'ox-mailchimp-campaign'));
        $label_reply_to = get_option('ox_mailchimp_campaign_label_reply_to', __('Reply-To Email', 'ox-mailchimp-campaign'));
        $label_tag = get_option('ox_mailchimp_campaign_label_tag', __('Select Members tagged as', 'ox-mailchimp-campaign'));
        $label_content = get_option('ox_mailchimp_campaign_label_content', __('Email Content', 'ox-mailchimp-campaign'));
        $label_submit = get_option('ox_mailchimp_campaign_label_submit', __('Send Campaign', 'ox-mailchimp-campaign'));
        
        // Get to_name format
        $to_name_format = get_option('ox_mailchimp_campaign_to_name_format', 'first_name');
        
        // Get from_email_override
        $from_email_override = get_option('ox_mailchimp_campaign_from_email_override', '');
        
        include MCF_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    /**
     * Render the campaign form
     */
    public function render_form($atts) {
        $atts = shortcode_atts(array(
            'title' => __('Send Campaign', 'ox-mailchimp-campaign')
        ), $atts);
        
        ob_start();
        include MCF_PLUGIN_DIR . 'templates/campaign-form.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for creating campaign
     */
    public function ajax_create_campaign() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ox_mailchimp_campaign_nonce')) {
            wp_die(__('Security check failed', 'ox-mailchimp-campaign'));
        }
        

        
        // Create a unique campaign identifier to prevent duplicates
        // Use original title (without timestamp) for hash to prevent same content being sent multiple times
        $campaign_hash = md5(
            sanitize_text_field($_POST['subject']) . 
            sanitize_text_field($_POST['title']) . 
            sanitize_text_field($_POST['from_name']) . 
            sanitize_email($_POST['from_email']) . 
            sanitize_text_field($_POST['tag']) . 
            wp_kses_post($_POST['content'])
        );
        
        // Check if this campaign was already created recently (within 5 minutes)
        $recent_campaigns = get_transient('mcf_recent_campaigns');
        if (!$recent_campaigns) {
            $recent_campaigns = array();
        }
        
        if (in_array($campaign_hash, $recent_campaigns)) {
            wp_send_json_error(__('This campaign was already sent recently. Please wait a few minutes before sending again.', 'ox-mailchimp-campaign'));
        }
        
        // Add this campaign to recent campaigns
        $recent_campaigns[] = $campaign_hash;
        set_transient('mcf_recent_campaigns', $recent_campaigns, 300); // 5 minutes
        
        // Get form data
        $campaign_data = array(
            'subject' => sanitize_text_field($_POST['subject']),
            'from_name' => sanitize_text_field($_POST['from_name']),
            'from_email' => sanitize_email($_POST['from_email']),
            'reply_to' => sanitize_email($_POST['reply_to']),
            'title' => sanitize_text_field($_POST['title']),
            'content' => wp_kses_post($_POST['content']),
            'tag' => sanitize_text_field($_POST['tag'])
        );
        
        // Convert template variables to Mailchimp merge tags
        $email_templates = new MCF_Email_Templates();
        $campaign_data['content'] = $email_templates->convert_variables($campaign_data['content']);
        
        // Validate required fields
        $required_fields = array('subject', 'from_name', 'from_email', 'reply_to', 'title', 'content', 'tag');
        foreach ($required_fields as $field) {
            if (empty($campaign_data[$field])) {
                wp_send_json_error(__('All fields are required', 'ox-mailchimp-campaign'));
            }
        }
        
        // Validate tag ID is a valid integer
        if (!is_numeric($campaign_data['tag']) || intval($campaign_data['tag']) <= 0) {
            wp_send_json_error(__('Invalid tag selected', 'ox-mailchimp-campaign'));
        }
        
        // Create campaign
        $result = $this->create_mailchimp_campaign($campaign_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(__('Campaign created and sent successfully!', 'ox-mailchimp-campaign'));
        }
    }
    
    /**
     * Create Mailchimp campaign
     */
    private function create_mailchimp_campaign($data) {
        $api_key = get_option('ox_mailchimp_campaign_api_key');
        $list_id = get_option('ox_mailchimp_campaign_list_id');
        
        if (empty($api_key) || empty($list_id)) {
            return new WP_Error('missing_config', __('Mailchimp API key or List ID not configured', 'ox-mailchimp-campaign'));
        }
        
        // Extract datacenter from API key
        $dc = substr($api_key, strpos($api_key, '-') + 1);
        $api_url = "https://{$dc}.api.mailchimp.com/3.0";
        
        // Append timestamp to campaign title to ensure uniqueness
        $original_title = $data['title'];
        $timestamp = current_time('Y-m-d H:i:s');
        $unique_title = $original_title . ' - ' . $timestamp;
        
        // Update the title in the data array
        $data['title'] = $unique_title;
        
        // Create campaign with proper From and Reply-To handling
        $campaign_data = array(
            'type' => 'regular',
            'recipients' => array(
                'list_id' => $list_id,
                'segment_opts' => array(
                    'saved_segment_id' => intval($data['tag'])
                )
            ),
            'settings' => array(
                'subject_line' => $data['subject'],
                'title' => $data['title'],
                'from_name' => $data['from_name'],
                'from_email' => $data['from_email'], // Use the form email (Mailchimp will ignore this anyway)
                'reply_to' => $this->get_reply_to_email($data['reply_to']), // Use override if set
                'to_name' => $this->get_to_name_format()
            )
        );
        

        

        
        $response = wp_remote_post($api_url . '/campaigns', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($campaign_data)
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        

        
        $campaign = json_decode($body, true);
        
        if (empty($campaign['id'])) {
            if ($response_code !== 200) {
                return new WP_Error('campaign_creation_failed', 'HTTP ' . $response_code . ': ' . $body);
            }
            return new WP_Error('campaign_creation_failed', __('Failed to create campaign', 'ox-mailchimp-campaign'));
        }
        

        
        // Set campaign content
        $content_data = array(
            'html' => $data['content']
        );
        
        $content_response = wp_remote_post($api_url . '/campaigns/' . $campaign['id'] . '/content', array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($content_data)
        ));
        
        if (is_wp_error($content_response)) {
            return $content_response;
        }
        
        $content_response_code = wp_remote_retrieve_response_code($content_response);
        $content_body = wp_remote_retrieve_body($content_response);
        
        if ($content_response_code !== 200) {
            return new WP_Error('content_setting_failed', 'HTTP ' . $content_response_code . ': ' . $content_body);
        }
        
        // Send campaign
        $send_response = wp_remote_post($api_url . '/campaigns/' . $campaign['id'] . '/actions/send', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)
            )
        ));
        
        if (is_wp_error($send_response)) {
            return $send_response;
        }
        
        $send_response_code = wp_remote_retrieve_response_code($send_response);
        $send_body = wp_remote_retrieve_body($send_response);
        
        if ($send_response_code !== 200 && $send_response_code !== 204) {
            return new WP_Error('campaign_sending_failed', 'HTTP ' . $send_response_code . ': ' . $send_body);
        }
        
        return true;
    }
    
    /**
     * AJAX handler for getting tags
     */
    public function ajax_get_tags() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ox_mailchimp_campaign_nonce')) {
            wp_die(__('Security check failed', 'ox-mailchimp-campaign'));
        }
        
        $tags = $this->get_mailchimp_tags();
        
        if (is_wp_error($tags)) {
            wp_send_json_error($tags->get_error_message());
        } else {
            wp_send_json_success($tags);
        }
    }
    
    /**
     * Get tags from Mailchimp
     */
    private function get_mailchimp_tags() {
        $api_key = get_option('ox_mailchimp_campaign_api_key');
        $list_id = get_option('ox_mailchimp_campaign_list_id');
        
        if (empty($api_key) || empty($list_id)) {
            return new WP_Error('missing_config', __('Mailchimp API key or List ID not configured', 'ox-mailchimp-campaign'));
        }
        
        // Extract datacenter from API key
        $dc = substr($api_key, strpos($api_key, '-') + 1);
        $api_url = "https://{$dc}.api.mailchimp.com/3.0";
        
        // Get segments (tags) from the list
        $response = wp_remote_get($api_url . '/lists/' . $list_id . '/segments?count=1000', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        $segments = json_decode($body, true);
        
        if (empty($segments['segments'])) {
            // Try alternative method - get tags directly
            $tags_response = wp_remote_get($api_url . '/lists/' . $list_id . '/tag-search', array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)
                )
            ));
            
            if (is_wp_error($tags_response)) {
                return array();
            }
            
            $tags_body = wp_remote_retrieve_body($tags_response);
            $tags_data = json_decode($tags_body, true);
            
            if (!empty($tags_data['tags'])) {
                $tags = array();
                foreach ($tags_data['tags'] as $tag) {
                    $tags[] = array(
                        'id' => $tag['id'],
                        'name' => $tag['name']
                    );
                }
                return $tags;
            }
            
            return array();
        }
        
        $tags = array();
        foreach ($segments['segments'] as $segment) {
            // Include both 'tag' and 'static' type segments as they can be used for targeting
            if ($segment['type'] === 'tag' || $segment['type'] === 'static') {
                $tags[] = array(
                    'id' => $segment['id'],
                    'name' => $segment['name']
                );
            }
        }
        
        return $tags;
    }
    
    /**
     * Get the to_name format based on settings
     */
    private function get_to_name_format() {
        $format = get_option('ox_mailchimp_campaign_to_name_format', 'first_name');
        
        switch ($format) {
            case 'full_name':
                return '*|FNAME|* *|LNAME|*';
            case 'last_name_first':
                return '*|LNAME|*, *|FNAME|*';
            case 'first_name_only':
            default:
                return '*|FNAME|*';
        }
    }
    
    /**
     * Get the from_email, using override if set
     */
    private function get_from_email($form_email) {
        $override = get_option('ox_mailchimp_campaign_from_email_override', '');
        
        if (!empty($override)) {
            return $override;
        }
        
        return $form_email;
    }
    
    /**
     * Get the reply_to email, using override if set (since Mailchimp ignores from_email)
     */
    private function get_reply_to_email($form_reply_to) {
        $override = get_option('ox_mailchimp_campaign_from_email_override', '');
        
        if (!empty($override)) {
            return $override;
        }
        
        return $form_reply_to;
    }
    
    /**
     * AJAX handler for getting templates
     */
    public function ajax_get_templates() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ox_mailchimp_campaign_nonce')) {
            wp_die(__('Security check failed', 'ox-mailchimp-campaign'));
        }
        
        $email_templates = new MCF_Email_Templates();
        $templates = $email_templates->get_templates();
        wp_send_json_success($templates);
    }
    
    /**
     * AJAX handler for getting template content
     */
    public function ajax_get_template_content() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ox_mailchimp_campaign_nonce')) {
            wp_die(__('Security check failed', 'ox-mailchimp-campaign'));
        }
        
        $template_id = intval($_POST['template_id']);
        $email_templates = new MCF_Email_Templates();
        $content = $email_templates->get_template_content($template_id);
        
        if ($content) {
            wp_send_json_success($content);
        } else {
            wp_send_json_error(__('Template not found', 'ox-mailchimp-campaign'));
        }
    }
}

// Initialize plugin
new MailchimpCampaignForm();

// Activation hook
register_activation_hook(__FILE__, 'mcf_activate');
function mcf_activate() {
    // Flush rewrite rules on activation
    add_option('ox_mailchimp_campaign_flush_rewrite_rules', true);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'mcf_deactivate');
function mcf_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}

// Version upgrade check and routine
add_action('plugins_loaded', 'mcf_plugin_upgrade_check');

/**
 * Checks for plugin version upgrades and runs upgrade routines if needed.
 */
function mcf_plugin_upgrade_check() {
    $stored_version = get_option('mcf_plugin_version');
    if ($stored_version === false) {
        // First install: set the version
        update_option('mcf_plugin_version', MCF_PLUGIN_VERSION);
        return;
    }
    if (version_compare($stored_version, MCF_PLUGIN_VERSION, '<')) {
        // Run upgrade routines here if needed
        
        // Version 1.1.5: Added image insertion functionality
        if (version_compare($stored_version, '1.1.5', '<')) {
            // No database changes needed for image functionality
            // TinyMCE configuration is handled automatically
        }
        
        // Example for future versions:
        // if (version_compare($stored_version, '1.2.0', '<')) {
        //     // Add upgrade logic here
        // }

        // Update the stored version
        update_option('mcf_plugin_version', MCF_PLUGIN_VERSION);
    }
} 