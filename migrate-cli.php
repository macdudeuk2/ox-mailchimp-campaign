<?php
/**
 * CLI Migration Script for OX Mailchimp Shortcodes
 * 
 * This script can be run independently to migrate settings from the old
 * mailchimp-campaign-form plugin to the new ox-mailchimp-shortcodes plugin.
 * 
 * Usage: php migrate-cli.php
 * 
 * @package OXMailchimpShortcodes
 * @since 1.1.2
 */

// Prevent direct web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    die("WordPress not found. Please run this script from the plugin directory.\n");
}

require_once $wp_load_path;

// Ensure we're in WordPress context
if (!defined('ABSPATH')) {
    die("WordPress not loaded properly.\n");
}

echo "OX Mailchimp Shortcodes - Migration Script\n";
echo "==========================================\n\n";

// Check if old plugin options exist
$old_api_key = get_option('mcf_api_key');
if (empty($old_api_key)) {
    echo "❌ No old plugin settings found. Nothing to migrate.\n";
    echo "Make sure the old Mailchimp Campaign Form plugin was previously installed.\n\n";
    exit(1);
}

echo "✅ Found old plugin settings. Starting migration...\n\n";

// Define migration mapping
$migration_map = array(
    'mcf_api_key' => 'ox_mailchimp_api_key',
    'mcf_list_id' => 'ox_mailchimp_list_id',
    'mcf_label_subject' => 'ox_mailchimp_label_subject',
    'mcf_label_title' => 'ox_mailchimp_label_title',
    'mcf_label_from_name' => 'ox_mailchimp_label_from_name',
    'mcf_label_from_email' => 'ox_mailchimp_label_from_email',
    'mcf_label_reply_to' => 'ox_mailchimp_label_reply_to',
    'mcf_label_tag' => 'ox_mailchimp_label_tag',
    'mcf_label_content' => 'ox_mailchimp_label_content',
    'mcf_label_submit' => 'ox_mailchimp_label_submit',
    'mcf_to_name_format' => 'ox_mailchimp_to_name_format',
    'mcf_from_email_override' => 'ox_mailchimp_from_email_override',
    'mcf_plugin_version' => 'ox_mailchimp_plugin_version'
);

$migrated_count = 0;
$errors = array();

// Migrate each option
foreach ($migration_map as $old_option => $new_option) {
    $old_value = get_option($old_option);
    if ($old_value !== false) {
        $result = update_option($new_option, $old_value);
        if ($result) {
            echo "✅ Migrated: {$old_option} → {$new_option}\n";
            $migrated_count++;
        } else {
            echo "❌ Failed to migrate: {$old_option}\n";
            $errors[] = "Failed to migrate {$old_option}";
        }
    } else {
        echo "⚠️  Skipped: {$old_option} (not found)\n";
    }
}

// Migrate transients
$old_transient = get_transient('mcf_recent_campaigns');
if ($old_transient !== false) {
    $result = set_transient('ox_mailchimp_recent_campaigns', $old_transient, 300);
    if ($result) {
        echo "✅ Migrated: mcf_recent_campaigns → ox_mailchimp_recent_campaigns\n";
        $migrated_count++;
    } else {
        echo "❌ Failed to migrate: mcf_recent_campaigns\n";
        $errors[] = "Failed to migrate mcf_recent_campaigns";
    }
} else {
    echo "⚠️  Skipped: mcf_recent_campaigns (not found)\n";
}

// Mark migration as completed
update_option('ox_mailchimp_migration_completed', true);
delete_option('ox_mailchimp_migration_needed');

echo "\n" . str_repeat("=", 50) . "\n";
echo "Migration Summary\n";
echo str_repeat("=", 50) . "\n";
echo "Total settings migrated: {$migrated_count}\n";

if (!empty($errors)) {
    echo "Errors encountered: " . count($errors) . "\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
    echo "\n⚠️  Some settings failed to migrate. Please check manually.\n";
} else {
    echo "✅ All settings migrated successfully!\n";
}

// Verify migration
echo "\nVerification:\n";
$new_api_key = get_option('ox_mailchimp_api_key');
$new_list_id = get_option('ox_mailchimp_list_id');

if (!empty($new_api_key)) {
    echo "✅ API Key: " . substr($new_api_key, 0, 8) . "...\n";
} else {
    echo "❌ API Key: Not found\n";
}

if (!empty($new_list_id)) {
    echo "✅ List ID: {$new_list_id}\n";
} else {
    echo "❌ List ID: Not found\n";
}

echo "\nNext Steps:\n";
echo "1. Activate the OX Mailchimp Shortcodes plugin\n";
echo "2. Go to Settings → OX Mailchimp Shortcodes to verify settings\n";
echo "3. Update any shortcodes in your content from [mailchimp_campaign_form] to [ox_mailchimp_shortcodes]\n";
echo "4. Test the functionality\n";
echo "5. Remove the old plugin when ready\n\n";

echo "Migration completed!\n"; 