<?php
/**
 * Email Templates Custom Post Type
 * 
 * @package OXMailchimpCampaign
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MCF_Email_Templates {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_action('wp_ajax_ox_mailchimp_campaign_get_templates', array($this, 'ajax_get_templates'));
        add_action('wp_ajax_ox_mailchimp_campaign_get_template_content', array($this, 'ajax_get_template_content'));
        add_action('admin_init', array($this, 'maybe_flush_rewrite_rules'));
        
        // Force classic editor for new email templates
        add_filter('admin_url', array($this, 'force_classic_editor_for_new_templates'), 10, 3);
        add_action('admin_bar_menu', array($this, 'modify_admin_bar_new_template_link'), 999);
        add_action('admin_init', array($this, 'force_classic_editor_preference'));
        add_filter('use_block_editor_for_post_type', array($this, 'disable_block_editor_for_templates'), 10, 2);
    }
    
    /**
     * Register the email template post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Email Templates', 'ox-mailchimp-campaign'),
            'singular_name'      => __('Email Template', 'ox-mailchimp-campaign'),
            'add_new'            => __('Add New', 'ox-mailchimp-campaign'),
            'add_new_item'       => __('Add New Email Template', 'ox-mailchimp-campaign'),
            'edit_item'          => __('Edit Email Template', 'ox-mailchimp-campaign'),
            'new_item'           => __('New Email Template', 'ox-mailchimp-campaign'),
            'view_item'          => __('View Email Template', 'ox-mailchimp-campaign'),
            'search_items'       => __('Search Email Templates', 'ox-mailchimp-campaign'),
            'not_found'          => __('No email templates found', 'ox-mailchimp-campaign'),
            'not_found_in_trash' => __('No email templates found in Trash', 'ox-mailchimp-campaign'),
            'menu_name'          => __('Email Templates', 'ox-mailchimp-campaign'),
        );
        
        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true, // Enable Gutenberg
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'menu_position'       => 30,
            'menu_icon'           => 'dashicons-email-alt',
            'supports'            => array('title', 'editor', 'revisions', 'custom-fields'),
            'has_archive'         => false,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
        );
        
        register_post_type('mc_email_template', $args);
    }
    
    /**
     * Add meta boxes for template settings
     */
    public function add_meta_boxes() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        add_meta_box(
            'mc_template_settings',
            __('Template Settings', 'ox-mailchimp-campaign'),
            array($this, 'render_template_settings'),
            'mc_email_template',
            'side',
            'high'
        );
        
        add_meta_box(
            'mc_template_variables',
            __('Available Variables', 'ox-mailchimp-campaign'),
            array($this, 'render_template_variables'),
            'mc_email_template',
            'side',
            'default'
        );
    }
    
    /**
     * Render template settings meta box
     */
    public function render_template_settings($post) {
        wp_nonce_field('mc_template_settings', 'mc_template_nonce');
        
        $category = get_post_meta($post->ID, '_mc_template_category', true);
        $description = get_post_meta($post->ID, '_mc_template_description', true);
        
        ?>
        <p>
            <label for="mc_template_category"><?php _e('Category:', 'ox-mailchimp-campaign'); ?></label>
            <select name="mc_template_category" id="mc_template_category">
                <option value=""><?php _e('-- Select Category --', 'ox-mailchimp-campaign'); ?></option>
                <option value="newsletter" <?php selected($category, 'newsletter'); ?>><?php _e('Newsletter', 'ox-mailchimp-campaign'); ?></option>
                <option value="promotional" <?php selected($category, 'promotional'); ?>><?php _e('Promotional', 'ox-mailchimp-campaign'); ?></option>
                <option value="announcement" <?php selected($category, 'announcement'); ?>><?php _e('Announcement', 'ox-mailchimp-campaign'); ?></option>
                <option value="event" <?php selected($category, 'event'); ?>><?php _e('Event', 'ox-mailchimp-campaign'); ?></option>
                <option value="general" <?php selected($category, 'general'); ?>><?php _e('General', 'ox-mailchimp-campaign'); ?></option>
            </select>
        </p>
        <p>
            <label for="mc_template_description"><?php _e('Description:', 'ox-mailchimp-campaign'); ?></label>
            <textarea name="mc_template_description" id="mc_template_description" rows="3" style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
        </p>
        <?php
    }
    
    /**
     * Render template variables meta box
     */
    public function render_template_variables($post) {
        ?>
        <p><?php _e('You can use these variables in your template:', 'ox-mailchimp-campaign'); ?></p>
        <ul style="margin-left: 20px;">
            <li><code>{{first_name}}</code> - <?php _e('Subscriber first name', 'ox-mailchimp-campaign'); ?></li>
            <li><code>{{last_name}}</code> - <?php _e('Subscriber last name', 'ox-mailchimp-campaign'); ?></li>
            <li><code>{{email}}</code> - <?php _e('Subscriber email', 'ox-mailchimp-campaign'); ?></li>
            <li><code>{{company}}</code> - <?php _e('Company name', 'ox-mailchimp-campaign'); ?></li>
            <li><code>{{date}}</code> - <?php _e('Current date', 'ox-mailchimp-campaign'); ?></li>
            <li><code>{{unsubscribe}}</code> - <?php _e('Unsubscribe link', 'ox-mailchimp-campaign'); ?></li>
        </ul>
        <p><em><?php _e('These will be automatically converted to Mailchimp merge tags when the campaign is created.', 'ox-mailchimp-campaign'); ?></em></p>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id) {
        // Check if nonce is valid
        if (!isset($_POST['mc_template_nonce']) || !wp_verify_nonce($_POST['mc_template_nonce'], 'mc_template_settings')) {
            return;
        }
        
        // Check if user has permissions
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if not an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check if this is our custom post type
        if (get_post_type($post_id) !== 'mc_email_template') {
            return;
        }
        
        // Save template category
        if (isset($_POST['mc_template_category'])) {
            update_post_meta($post_id, '_mc_template_category', sanitize_text_field($_POST['mc_template_category']));
        }
        
        // Save template description
        if (isset($_POST['mc_template_description'])) {
            update_post_meta($post_id, '_mc_template_description', sanitize_textarea_field($_POST['mc_template_description']));
        }
    }
    
    /**
     * AJAX handler for getting templates
     */
    public function ajax_get_templates() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ox_mailchimp_campaign_nonce')) {
            wp_die(__('Security check failed', 'ox-mailchimp-campaign'));
        }
        
        $templates = $this->get_templates();
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
        $content = $this->get_template_content($template_id);
        
        // Process shortcodes before returning
        if ($content) {
            $content = do_shortcode($content);
            wp_send_json_success($content);
        } else {
            wp_send_json_error(__('Template not found', 'ox-mailchimp-campaign'));
        }
    }
    
    /**
     * Get all templates
     */
    public function get_templates() {
        $args = array(
            'post_type' => 'mc_email_template',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        $templates = array();
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $category = get_post_meta(get_the_ID(), '_mc_template_category', true);
                $description = get_post_meta(get_the_ID(), '_mc_template_description', true);
                
                $templates[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'category' => $category,
                    'description' => $description
                );
            }
        }
        
        wp_reset_postdata();
        return $templates;
    }
    
    /**
     * Get template content by ID
     */
    public function get_template_content($template_id) {
        $post = get_post($template_id);
        
        if (!$post || $post->post_type !== 'mc_email_template') {
            return false;
        }
        
        return $post->post_content;
    }
    
    /**
     * Convert template variables to Mailchimp merge tags
     */
    public function convert_variables($content) {
        $variables = array(
            '{{first_name}}' => '*|FNAME|*',
            '{{last_name}}' => '*|LNAME|*',
            '{{email}}' => '*|EMAIL|*',
            '{{company}}' => '*|COMPANY|*',
            '{{date}}' => '*|DATE|*',
            '{{unsubscribe}}' => '*|UNSUB|*'
        );
        
        return str_replace(array_keys($variables), array_values($variables), $content);
    }
    
    /**
     * Force classic editor for new email templates
     */
    public function force_classic_editor_for_new_templates($url, $path, $blog_id) {
        if ($path === 'post-new.php?post_type=mc_email_template') {
            return admin_url('post-new.php?post_type=mc_email_template&classic-editor=1');
        }
        return $url;
    }
    
    /**
     * Modify admin bar "New Template" link to force classic editor
     */
    public function modify_admin_bar_new_template_link($wp_admin_bar) {
        $node = $wp_admin_bar->get_node('new-mc_email_template');
        if ($node) {
            $node->href = admin_url('post-new.php?post_type=mc_email_template&classic-editor=1');
            $wp_admin_bar->add_node($node);
        }
    }
    
    /**
     * Force classic editor preference for email templates
     */
    public function force_classic_editor_preference() {
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'mc_email_template') {
            update_user_meta(get_current_user_id(), 'classic-editor-replace', 'replace');
        }
    }
    
    /**
     * Disable block editor for email templates
     */
    public function disable_block_editor_for_templates($use_block_editor, $post_type) {
        if ($post_type === 'mc_email_template') {
            return false;
        }
        return $use_block_editor;
    }
    
    /**
     * Flush rewrite rules if needed
     */
    public function maybe_flush_rewrite_rules() {
        if (get_option('ox_mailchimp_campaign_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('ox_mailchimp_campaign_flush_rewrite_rules');
        }
    }
}

// Initialize the email templates class
new MCF_Email_Templates(); 