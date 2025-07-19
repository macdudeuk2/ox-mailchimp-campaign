<?php
/**
 * Uninstall script for Mailchimp Campaign Form
 * 
 * This file is executed when the plugin is deleted from WordPress admin.
 * It cleans up all plugin data including options, transients, and custom post types.
 * 
 * @package MailchimpCampaignForm
 * @version 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('ox_mailchimp_api_key');
delete_option('ox_mailchimp_list_id');
delete_option('ox_mailchimp_label_subject');
delete_option('ox_mailchimp_label_title');
delete_option('ox_mailchimp_label_from_name');
delete_option('ox_mailchimp_label_from_email');
delete_option('ox_mailchimp_label_reply_to');
delete_option('ox_mailchimp_label_tag');
delete_option('ox_mailchimp_label_content');
delete_option('ox_mailchimp_label_submit');
delete_option('ox_mailchimp_flush_rewrite_rules');

// Delete transients
delete_transient('ox_mailchimp_recent_campaigns');

// Delete migration options
delete_option('ox_mailchimp_migration_needed');
delete_option('ox_mailchimp_migration_completed');
delete_option('ox_mailchimp_migration_dismissed');

// Get all email template posts
$templates = get_posts(array(
    'post_type' => 'mc_email_template',
    'numberposts' => -1,
    'post_status' => 'any'
));

// Delete all email template posts and their meta
foreach ($templates as $template) {
    wp_delete_post($template->ID, true);
}

// Flush rewrite rules to remove custom post type
flush_rewrite_rules(); 