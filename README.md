# OX Mailchimp Campaign

**Version: 1.1.5**

A WordPress plugin that generates forms for sending email campaigns using Mailchimp API with tag-based audience segmentation.

[![GitHub](https://img.shields.io/badge/GitHub-Repository-blue.svg)](https://github.com/macdudeuk2/ox-mailchimp-campaign)
[![WordPress](https://img.shields.io/badge/WordPress-Plugin-green.svg)](https://wordpress.org/plugins/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## Features

- **Email Campaign Creation**: Create and send Mailchimp campaigns directly from WordPress
- **Tag-Based Segmentation**: Target specific audience segments using Mailchimp tags
- **Customizable Email Templates**: Create and manage email templates using Gutenberg editor
- **Rich Text Editor**: TinyMCE integration for professional email content
- **Duplicate Prevention**: Multiple layers of protection against duplicate submissions
- **Customizable Labels**: All form labels are configurable in the admin settings
- **Responsive Design**: Mobile-friendly form layout
- **Template Variables**: Support for Mailchimp merge tags and custom variables
- **From Email Override**: Use a different email address to avoid contact lookup issues in email clients
- **Shortcode Support in Templates**: WordPress shortcodes in email templates are processed before loading into the message field
- **Image Support**: Insert images via URL with full parameter control (width, height, alignment, alt text)

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Mailchimp account with API access
- Active Mailchimp audience/list

## Installation

### New Installation
1. Upload the `ox-mailchimp-campaign` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Settings > Mailchimp Campaign Form' to configure your API settings

### Migration from Old Plugin
If you're upgrading from a previous version:

1. **Deactivate** the old plugin if upgrading from a different plugin
2. **Install and activate** this OX Mailchimp Campaign plugin
3. **Settings are preserved** automatically when upgrading
4. **Update shortcodes** in your content if migrating from a different plugin
5. **Remove** the old plugin when ready

For detailed migration instructions, see [DEPLOYMENT.md](DEPLOYMENT.md).

## Configuration

### Mailchimp API Setup

1. **Get your API Key**:
   - Log in to your Mailchimp account
   - Go to Account → Extras → API Keys
   - Create a new API key or copy an existing one

2. **Get your Audience ID**:
   - Go to Audience → Settings → Audience name and defaults
   - Copy the Audience ID (it looks like: `a1b2c3d4e5`)

3. **Configure the Plugin**:
   - In WordPress admin, go to Settings → Mailchimp Campaign Form
   - Enter your API Key and Audience ID
   - Save the settings

### Creating Email Templates

1. Go to 'Email Templates' in your WordPress admin menu
2. Click 'Add New' to create a new template
3. Use the Gutenberg editor to design your email
4. Set a category and description for organization
5. Use template variables like `{{first_name}}`, `{{email}}`, etc.
6. **You can also use WordPress shortcodes in your template content. These will be processed and expanded before the template is loaded into the message field.**

## Usage

### Shortcode

Use the shortcode `[ox_mailchimp_campaign_form]` to display the campaign form on any page or post.

**Example:**
```
[ox_mailchimp_campaign_form title="Send Newsletter"]
```

**Note:** The shortcode accepts an optional `title` parameter to customize the form heading.

### Form Fields

- **Subject Line**: The email subject that recipients will see
- **Campaign Title**: Internal name for the campaign (auto-appends timestamp)
- **From Name**: The sender name displayed to recipients
- **From Email**: The sender email address
- **Reply-To Email**: Email address for replies
- **Select Members tagged as**: Choose which Mailchimp segment to target
- **Email Template**: Optional template to pre-fill content
- **Email Content**: Rich text editor for email content with image insertion support

### Template Variables

You can use these variables in your email templates:

- `{{first_name}}` - Subscriber's first name
- `{{last_name}}` - Subscriber's last name
- `{{email}}` - Subscriber's email address
- `{{company}}` - Company name
- `{{date}}` - Current date
- `{{unsubscribe}}` - Unsubscribe link

These will be automatically converted to Mailchimp merge tags when the campaign is created.

### Image Insertion

The email content editor includes full image support:

1. **Click the image button** in the TinyMCE toolbar
2. **Paste an image URL** in the Source field
3. **Set image parameters**:
   - Width and Height
   - Alternative Text (for accessibility)
   - Alignment (left, center, right)
4. **Advanced options** (in Advanced tab):
   - Border settings
   - Vertical and horizontal spacing
   - Custom CSS styles

**Supported image formats**: JPG, PNG, GIF, WebP

## Customization

### Customizing Form Labels

All form labels can be customized in the plugin settings:

1. Go to Settings → Mailchimp Campaign Form
2. Scroll down to the "Field Labels" section
3. Modify any label text
4. Save changes

### From Email Override

Some email clients (like Apple Mail) may display contact information from their address book instead of the email headers. To avoid this:

1. Go to Settings → Mailchimp Campaign Form
2. In the "Campaign Settings" section, find "Override From Email"
3. Enter a different email address (e.g., `noreply@yourdomain.com`) that's not in your contacts
4. Save changes

This will use the override email for sending while keeping your "From Name" as entered in the form.

### Styling

The plugin includes basic CSS styling that can be overridden with your theme's CSS. The form uses these CSS classes:

- `.ox-mailchimp-campaign-form` - Main form container
- `.ox-mailchimp-form-row` - Form row wrapper
- `.ox-mailchimp-two-column` - Two-column layout
- `.ox-mailchimp-column` - Column wrapper
- `.ox-mailchimp-success` - Success message styling
- `.ox-mailchimp-error` - Error message styling

## Security Features

- **Nonce Verification**: All form submissions are protected with WordPress nonces
- **Input Sanitization**: All user inputs are properly sanitized
- **Capability Checks**: Only users with `manage_options` capability can access admin features
- **Duplicate Prevention**: Multiple layers of protection against duplicate submissions

## Troubleshooting

### Common Issues

1. **"No tags found" error**:
   - Ensure your Mailchimp audience has segments/tags created
   - Verify your API key and Audience ID are correct
   - Check that your API key has the necessary permissions

2. **Campaign creation fails**:
   - Verify all required fields are filled
   - Check that the selected tag/segment exists in Mailchimp
   - Ensure your Mailchimp account is active and not suspended

3. **Form not displaying**:
   - Make sure the shortcode is properly placed
   - Check for JavaScript conflicts with your theme
   - Verify the plugin is activated

### Debug Mode

If you need to troubleshoot issues, you can temporarily enable WordPress debug mode by adding this to your `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Changelog

### Version 1.1.2
- **Changed**: Set the TinyMCE editor height in the campaign form to a minimum of 600px for improved editing experience.

### Version 1.1.1
- **Added**: WordPress shortcodes in email templates are now processed before loading into the message field
- **Docs**: Updated documentation to reflect shortcode support in templates

### Version 1.1.0
- **Fixed**: From Email override system and field handling
- **Improved**: Admin settings descriptions and form field labels
- **Enhanced**: User experience with clearer form layout
- **Confirmed**: Proper Mailchimp API integration and email header handling

### Version 1.0.0
- Initial release
- Email campaign creation with Mailchimp API
- Tag-based audience segmentation
- Customizable email templates
- Rich text editor integration
- Duplicate submission prevention
- Responsive form design

## Development

### Contributing

This plugin is open source and contributions are welcome! 

**GitHub Repository:** https://github.com/macdudeuk2/ox-mailchimp-campaign

**To contribute:**
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Local Development

**Requirements:**
- WordPress 5.0+
- PHP 7.4+
- Mailchimp API access
- Active Mailchimp audience

**Setup:**
1. Clone the repository: `git clone https://github.com/macdudeuk2/ox-mailchimp-campaign.git`
2. Copy to your WordPress plugins directory: `wp-content/plugins/ox-mailchimp-campaign/`
3. Activate the plugin in WordPress admin
4. Configure your Mailchimp API settings
5. Start developing!

### Building for Distribution

The plugin includes a build script for creating distribution packages:

```bash
./build-distribution.sh
```

This creates a zip file ready for distribution.

## Support

For support, please visit:
- [Plugin Documentation](https://github.com/macdudeuk2/ox-mailchimp-campaign)
- [WordPress.org Support Forums](https://wordpress.org/support/)

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- Built with WordPress and Mailchimp API
- Uses TinyMCE for rich text editing
- Gutenberg integration for template management

---

**Note**: This plugin requires a valid Mailchimp account and API access. Please ensure you comply with Mailchimp's terms of service and API usage guidelines. 