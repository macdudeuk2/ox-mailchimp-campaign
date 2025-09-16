# Changelog

All notable changes to the Mailchimp Campaign Form plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-07-05

### Fixed
- **From Email Override System**: Fixed confusion with From Email vs Reply-To field handling
- **Admin Settings Description**: Updated to accurately reflect that override affects Reply-To field, not From Email
- **Form Field Labels**: Simplified and clarified field labeling to prevent user confusion
- **Email Headers**: Confirmed Mailchimp correctly uses Reply-To as sender when From Email is ignored
- **Debug Logging**: Removed debug logging from production code

### Changed
- **Form Layout**: Simplified form structure for better user experience
- **Override Behavior**: When override is set, Reply-To field shows override email as read-only
- **Field Descriptions**: Updated help text to be more accurate and user-friendly

### Technical Improvements
- **Code Clarity**: Improved method naming and documentation
- **Error Handling**: Enhanced error messages and validation
- **API Integration**: Confirmed proper Mailchimp API usage patterns

## [1.0.0] - 2025-01-15

### Added
- Initial plugin release
- Email campaign creation via Mailchimp API
- Tag-based audience segmentation
- Customizable email templates with Gutenberg editor
- Rich text editor (TinyMCE) integration
- Template variable support ({{first_name}}, {{email}}, etc.)
- Configurable form field labels
- Responsive form design
- Duplicate submission prevention
- Admin settings page
- Custom post type for email templates
- Template categories and descriptions
- AJAX form submission
- Form validation
- Success/error message handling
- Template loading functionality
- Security features (nonce verification, capability checks)

### Technical Features
- WordPress coding standards compliance
- Proper sanitization and validation
- Error handling and logging
- Activation/deactivation hooks
- Uninstall cleanup script
- Internationalization support
- Mobile-responsive CSS
- Cross-browser compatibility

### Security
- Nonce verification for all form submissions
- Input sanitization and validation
- Capability checks for admin functions
- Secure API communication
- XSS protection

## [1.1.1] - 2025-07-05

### Added
- WordPress shortcodes in email templates are now processed before loading into the message field
- Documentation updated to reflect shortcode support in templates

## [1.1.2] - 2025-07-06

### Changed
- Set the TinyMCE editor height in the campaign form to a minimum of 600px for improved editing experience.

## [1.1.5] - 2025-01-26

### Added
- **Image Insertion**: Added TinyMCE image plugin to enable URL-based image insertion in email campaigns
- **Image Dialog**: Users can now click image button in toolbar to insert images via URL
- **Image Parameters**: Support for setting image width, height, alignment, and alt text
- **Advanced Image Options**: Access to border, spacing, and style options through Advanced tab

### Improved
- **Email Content**: Enhanced email campaign creation with visual content support
- **User Experience**: Streamlined image insertion without requiring media upload permissions

## [1.1.4] - 2025-01-26

### Added
- **Clear Content Button**: Added a "Clear Content" button next to the Email Content label
- **Confirmation Dialog**: Clear content action now shows a confirmation dialog to prevent accidental data loss
- **Template Reset**: Clear content button also resets the template selection dropdown
- **Success Feedback**: Users receive confirmation when content is cleared successfully

### Improved
- **User Experience**: Users can now easily clear editor content and start fresh without manually selecting and deleting text
- **Template Workflow**: Better handling of template selection and content clearing workflow
- **Responsive Design**: Clear content button adapts to mobile layouts
- **Theme Integration**: Frontend form styling now aligns with Kadence theme color standards and design patterns
- **Visual Consistency**: Form elements use Kadence CSS custom properties for colors, spacing, and styling
- **Button Styling**: Clear content and submit buttons follow Kadence theme design language with hover effects and transitions

## [1.1.3] - 2025-07-07

### Changed
- Added version upgrade check on plugin load to ensure seamless upgrades and data preservation when installing new versions manually.
- Plugin now stores its version in the database and runs upgrade routines if needed, following WordPress best practices for custom plugins.

## Future Versions

### Planned for 1.1.0
- Campaign scheduling functionality
- A/B testing support
- Advanced template variables
- Campaign analytics integration
- Bulk template import/export
- Enhanced error reporting

### Planned for 1.2.0
- Multi-audience support
- Campaign templates library
- Advanced segmentation options
- Email preview functionality
- Campaign performance tracking 