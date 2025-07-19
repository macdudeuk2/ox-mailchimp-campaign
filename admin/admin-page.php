<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="api_key"><?php _e('Mailchimp API Key', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                    <p class="description">
                        <?php _e('Enter your Mailchimp API key. You can find this in your Mailchimp account under Account > Extras > API Keys.', 'ox-mailchimp-campaign'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="list_id"><?php _e('Mailchimp List ID', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="list_id" name="list_id" value="<?php echo esc_attr($list_id); ?>" class="regular-text" />
                    <p class="description">
                        <?php _e('Enter your Mailchimp List/Audience ID. You can find this in your Mailchimp account under Audience > Settings > Audience name and defaults.', 'ox-mailchimp-campaign'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        </table>
        
        <h2><?php _e('Campaign Settings', 'ox-mailchimp-campaign'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="to_name_format"><?php _e('Recipient Name Format', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <select id="to_name_format" name="to_name_format">
                        <option value="first_name_only" <?php selected($to_name_format, 'first_name_only'); ?>>
                            <?php _e('First Name Only (*|FNAME|*)', 'ox-mailchimp-campaign'); ?>
                        </option>
                        <option value="full_name" <?php selected($to_name_format, 'full_name'); ?>>
                            <?php _e('Full Name (*|FNAME|* *|LNAME|*)', 'ox-mailchimp-campaign'); ?>
                        </option>
                        <option value="last_name_first" <?php selected($to_name_format, 'last_name_first'); ?>>
                            <?php _e('Last, First (*|LNAME|*, *|FNAME|*)', 'ox-mailchimp-campaign'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('Choose how recipient names appear in the "To" field. If names appear reversed, try a different format or check your Mailchimp audience data.', 'ox-mailchimp-campaign'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="from_email_override"><?php _e('Override From Email', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="email" id="from_email_override" name="from_email_override" value="<?php echo esc_attr($from_email_override); ?>" class="regular-text" />
                    <p class="description">
                        <?php _e('Enter a different email address here (e.g., noreply@yourdomain.com) to use as the Reply-To address. When set, this will override the Reply-To field in the form and be used as the sender email.', 'ox-mailchimp-campaign'); ?>
                    </p>
                    <?php if (!empty($from_email_override)): ?>
                        <p class="description" style="color: green;">
                            <strong><?php _e('Current override:', 'ox-mailchimp-campaign'); ?></strong> <?php echo esc_html($from_email_override); ?>
                            <br><small><?php _e('This email will be used as the Reply-To field in campaigns.', 'ox-mailchimp-campaign'); ?></small>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>

        </table>
        
        <h2><?php _e('Field Labels', 'ox-mailchimp-campaign'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="label_subject"><?php _e('Subject Line Label', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="label_subject" name="label_subject" value="<?php echo esc_attr($label_subject); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="label_title"><?php _e('Campaign Title Label', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="label_title" name="label_title" value="<?php echo esc_attr($label_title); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="label_from_name"><?php _e('From Name Label', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="label_from_name" name="label_from_name" value="<?php echo esc_attr($label_from_name); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="label_from_email"><?php _e('From Email Label', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="label_from_email" name="label_from_email" value="<?php echo esc_attr($label_from_email); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="label_reply_to"><?php _e('Reply-To Email Label', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="label_reply_to" name="label_reply_to" value="<?php echo esc_attr($label_reply_to); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="label_tag"><?php _e('Tag Selection Label', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="label_tag" name="label_tag" value="<?php echo esc_attr($label_tag); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="label_content"><?php _e('Email Content Label', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="label_content" name="label_content" value="<?php echo esc_attr($label_content); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="label_submit"><?php _e('Submit Button Text', 'ox-mailchimp-campaign'); ?></label>
                </th>
                <td>
                    <input type="text" id="label_submit" name="label_submit" value="<?php echo esc_attr($label_submit); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <h2><?php _e('Usage', 'ox-mailchimp-campaign'); ?></h2>
    <p><?php _e('Use the shortcode below to display the campaign form on any page or post:', 'ox-mailchimp-campaign'); ?></p>
    <code>[ox_mailchimp_campaign_form]</code>
    
    <h3><?php _e('Shortcode Parameters', 'ox-mailchimp-campaign'); ?></h3>
    <ul>
        <li><strong>title</strong> - <?php _e('Custom title for the form (default: "Send Campaign")', 'ox-mailchimp-campaign'); ?></li>
    </ul>
    
    <h3><?php _e('Example', 'ox-mailchimp-campaign'); ?></h3>
    <code>[ox_mailchimp_campaign_form title="Send Newsletter"]</code>
</div> 