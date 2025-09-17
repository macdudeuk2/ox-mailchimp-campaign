<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get available tags from Mailchimp
$tags = array(); // This will be populated via AJAX

// Get field labels with defaults
$label_subject = get_option('ox_mailchimp_campaign_label_subject', __('Subject Line', 'ox-mailchimp-campaign'));
$label_title = get_option('ox_mailchimp_campaign_label_title', __('Campaign Title', 'ox-mailchimp-campaign'));
$label_from_name = get_option('ox_mailchimp_campaign_label_from_name', __('From Name', 'ox-mailchimp-campaign'));
$label_from_email = get_option('ox_mailchimp_campaign_label_from_email', __('From Email', 'ox-mailchimp-campaign'));
$label_reply_to = get_option('ox_mailchimp_campaign_label_reply_to', __('Reply-To Email', 'ox-mailchimp-campaign'));
$label_tag = get_option('ox_mailchimp_campaign_label_tag', __('Select Members tagged as', 'ox-mailchimp-campaign'));
$label_content = get_option('ox_mailchimp_campaign_label_content', __('Email Content', 'ox-mailchimp-campaign'));
$label_submit = get_option('ox_mailchimp_campaign_label_submit', __('Send Campaign', 'ox-mailchimp-campaign'));

// Check if override email is set
$from_email_override = get_option('ox_mailchimp_campaign_from_email_override', '');
$has_override = !empty($from_email_override);


?>

<div class="mcf-campaign-form">
    <h2><?php echo esc_html($atts['title']); ?></h2>
    
    <form id="mcf-campaign-form" method="post">
        <div class="mcf-form-row">
            <label for="mcf-subject"><?php echo esc_html($label_subject); ?> *</label>
            <input type="text" id="mcf-subject" name="subject" required />
        </div>
        
        <div class="mcf-form-row">
            <label for="mcf-title"><?php echo esc_html($label_title); ?> *</label>
            <input type="text" id="mcf-title" name="title" required />
            <small><?php _e('Internal name for the campaign', 'ox-mailchimp-campaign'); ?></small>
        </div>
        
        <div class="mcf-form-row mcf-two-column">
            <div class="mcf-column">
                <label for="mcf-from-name"><?php echo esc_html($label_from_name); ?> *</label>
                <input type="text" id="mcf-from-name" name="from_name" required />
            </div>
            <div class="mcf-column">
                <label for="mcf-from-email"><?php echo esc_html($label_from_email); ?> *</label>
                <input type="email" id="mcf-from-email" name="from_email" required />
            </div>
        </div>
        
        <div class="mcf-form-row mcf-two-column">
            <div class="mcf-column">
                <?php if ($has_override): ?>
                    <label for="mcf-reply-to"><?php echo esc_html($label_reply_to); ?></label>
                    <input type="email" id="mcf-reply-to" name="reply_to" value="<?php echo esc_attr($from_email_override); ?>" readonly />
                    <small style="color: #666;"><?php _e('Using override email', 'ox-mailchimp-campaign'); ?></small>
                <?php else: ?>
                    <label for="mcf-reply-to"><?php echo esc_html($label_reply_to); ?> *</label>
                    <input type="email" id="mcf-reply-to" name="reply_to" required />
                <?php endif; ?>
            </div>
            <div class="mcf-column">
                <label for="mcf-tag"><?php echo esc_html($label_tag); ?> *</label>
                <select id="mcf-tag" name="tag" required>
                    <option value=""><?php _e('Loading tags...', 'ox-mailchimp-campaign'); ?></option>
                </select>
            </div>
        </div>
        
        <div class="mcf-form-row">
            <label for="mcf-template"><?php _e('Email Template', 'ox-mailchimp-campaign'); ?></label>
            <select id="mcf-template" name="template">
                <option value=""><?php _e('Select a template (optional)', 'ox-mailchimp-campaign'); ?></option>
            </select>
            <small><?php _e('Choose a template to pre-fill the content area', 'ox-mailchimp-campaign'); ?></small>
        </div>
        
        <div class="mcf-form-row">
            <div class="mcf-content-header">
                <label for="mcf-content"><?php echo esc_html($label_content); ?> *</label>
                <button type="button" id="mcf-clear-content" class="button button-secondary">
                    <?php _e('Clear Content', 'ox-mailchimp-campaign'); ?>
                </button>
            </div>
            <?php
            // Set up TinyMCE
            $editor_settings = array(
                'textarea_name' => 'content',
                'textarea_rows' => 15,
                'media_buttons' => true,
                'tinymce' => array(
                    'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,image,wp_more,spellchecker,fullscreen,wp_adv',
                    'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                    'toolbar3' => '',
                    'toolbar4' => '',
                    'content_css' => get_stylesheet_directory_uri() . '/style.css',
                    'content_style' => '.aligncenter { display: block !important; margin: 0 auto !important; text-align: center !important; } .alignleft { float: left !important; margin-right: 15px !important; margin-bottom: 15px !important; } .alignright { float: right !important; margin-left: 15px !important; margin-bottom: 15px !important; } .alignnone { margin: 0 !important; }',
                    'height' => 600,
                    // Image plugin configuration
                    'image_advtab' => true,
                    'image_description' => false,
                    'image_title' => false,
                    'image_caption' => false,
                ),
                'quicktags' => true,
                'drag_drop_upload' => false
            );
            
            wp_editor('', 'mcf-content', $editor_settings);
            ?>
        </div>
        
        <div class="mcf-form-row">
            <button type="submit" id="mcf-submit" class="button button-primary">
                <?php echo esc_html($label_submit); ?>
            </button>
            <span id="mcf-loading" style="display: none;">
                <?php _e('Sending...', 'ox-mailchimp-campaign'); ?>
            </span>
        </div>
        
        <div id="mcf-message"></div>
    </form>
</div> 