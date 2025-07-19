# OX Mailchimp Shortcodes - Deployment Guide

## Overview

This guide explains how to deploy the new **OX Mailchimp Shortcodes** plugin when replacing the old **Mailchimp Campaign Form** plugin.

## ⚠️ Important Notes

- **This is NOT an automatic upgrade** - WordPress will treat this as a completely new plugin
- **Old settings will be preserved** but need to be migrated to new option names
- **Shortcodes in content need to be updated** from `[mailchimp_campaign_form]` to `[ox_mailchimp_shortcodes]`

## 🔄 Migration Process

### Step 1: Backup Current Site
```bash
# Backup database
wp db export backup-before-migration.sql

# Backup plugin directory
cp -r wp-content/plugins/mailchimp-campaign-form wp-content/plugins/mailchimp-campaign-form-backup
```

### Step 2: Deactivate Old Plugin
1. Go to **WordPress Admin → Plugins**
2. Find **Mailchimp Campaign Form**
3. Click **Deactivate**

### Step 3: Install New Plugin
1. Upload `ox-mailchimp-shortcodes-1.1.2-beta.zip` via **Plugins → Add New → Upload Plugin**
2. Click **Install Now**
3. Click **Activate Plugin**

### Step 4: Migrate Settings
The plugin will automatically detect old settings and show a migration notice:

1. **Look for the migration notice** in the WordPress admin area
2. **Click "Migrate Settings"** to copy all settings from the old plugin
3. **Verify the migration** by checking Settings → OX Mailchimp Shortcodes

### Step 5: Update Content
Search and replace shortcodes in your content:

**Find:** `[mailchimp_campaign_form]`
**Replace:** `[ox_mailchimp_shortcodes]`

**Find:** `[mailchimp_campaign_form title="Custom Title"]`
**Replace:** `[ox_mailchimp_shortcodes title="Custom Title"]`

### Step 6: Remove Old Plugin
1. Go to **WordPress Admin → Plugins**
2. Find **Mailchimp Campaign Form**
3. Click **Delete**

## 🛠️ Manual Migration (If Needed)

If the automatic migration doesn't work, you can manually migrate settings:

### Option 1: Using WP-CLI
```bash
# Check migration status
wp option get ox_mailchimp_migration_status

# Run manual migration
wp eval 'echo OX_Mailchimp_Migration::manual_migrate();'
```

### Option 2: Using Database Queries
```sql
-- Copy API key
UPDATE wp_options SET option_name = 'ox_mailchimp_api_key' WHERE option_name = 'mcf_api_key';

-- Copy list ID
UPDATE wp_options SET option_name = 'ox_mailchimp_list_id' WHERE option_name = 'mcf_list_id';

-- Copy all label options
UPDATE wp_options SET option_name = 'ox_mailchimp_label_subject' WHERE option_name = 'mcf_label_subject';
UPDATE wp_options SET option_name = 'ox_mailchimp_label_title' WHERE option_name = 'mcf_label_title';
UPDATE wp_options SET option_name = 'ox_mailchimp_label_from_name' WHERE option_name = 'mcf_label_from_name';
UPDATE wp_options SET option_name = 'ox_mailchimp_label_from_email' WHERE option_name = 'mcf_label_from_email';
UPDATE wp_options SET option_name = 'ox_mailchimp_label_reply_to' WHERE option_name = 'mcf_label_reply_to';
UPDATE wp_options SET option_name = 'ox_mailchimp_label_tag' WHERE option_name = 'mcf_label_tag';
UPDATE wp_options SET option_name = 'ox_mailchimp_label_content' WHERE option_name = 'mcf_label_content';
UPDATE wp_options SET option_name = 'ox_mailchimp_label_submit' WHERE option_name = 'mcf_label_submit';

-- Copy format options
UPDATE wp_options SET option_name = 'ox_mailchimp_to_name_format' WHERE option_name = 'mcf_to_name_format';
UPDATE wp_options SET option_name = 'ox_mailchimp_from_email_override' WHERE option_name = 'mcf_from_email_override';
```

## 🔍 Verification Steps

### 1. Check Plugin Activation
- Go to **Plugins** page
- Verify **OX Mailchimp Shortcodes** is active
- Verify old **Mailchimp Campaign Form** is not present

### 2. Check Settings Migration
- Go to **Settings → OX Mailchimp Shortcodes**
- Verify API key and list ID are populated
- Verify all form labels are correct

### 3. Test Functionality
- Create a test page with `[ox_mailchimp_shortcodes]`
- Test form submission
- Verify email campaigns are created in Mailchimp

### 4. Check Content
- Search your site for `[mailchimp_campaign_form]`
- Update any remaining shortcodes
- Test updated shortcodes

## 🚨 Troubleshooting

### Migration Notice Not Appearing
```php
// Check if old options exist
wp option get mcf_api_key

// Check migration status
wp option get ox_mailchimp_migration_needed
```

### Settings Not Migrated
```php
// Run manual migration
wp eval 'echo OX_Mailchimp_Migration::manual_migrate();'

// Check what was migrated
wp option list | grep ox_mailchimp
```

### Shortcode Not Working
1. Check if plugin is active
2. Verify shortcode syntax: `[ox_mailchimp_shortcodes]`
3. Check browser console for JavaScript errors
4. Verify AJAX endpoints are working

### Form Not Submitting
1. Check Mailchimp API key is valid
2. Verify list ID is correct
3. Check WordPress debug log for errors
4. Verify nonce is being generated correctly

## 📋 Pre-Deployment Checklist

- [ ] Site backup completed
- [ ] Old plugin deactivated
- [ ] New plugin installed and activated
- [ ] Settings migrated successfully
- [ ] Content shortcodes updated
- [ ] Functionality tested
- [ ] Old plugin removed
- [ ] Post-deployment verification completed

## 🔧 Post-Deployment Cleanup

### Remove Migration Options (Optional)
```sql
-- Only after confirming everything works
DELETE FROM wp_options WHERE option_name LIKE 'ox_mailchimp_migration_%';
```

### Update Documentation
- Update any internal documentation
- Update user guides
- Update training materials

## 📞 Support

If you encounter issues during deployment:

1. **Check the troubleshooting section above**
2. **Review WordPress debug logs**
3. **Verify all steps were completed**
4. **Contact support with specific error messages**

## 🔄 Rollback Plan

If you need to rollback:

1. **Deactivate new plugin**
2. **Restore old plugin from backup**
3. **Restore database from backup**
4. **Reactivate old plugin**

---

**Version:** 1.1.2  
**Last Updated:** January 2025  
**Plugin:** OX Mailchimp Shortcodes 