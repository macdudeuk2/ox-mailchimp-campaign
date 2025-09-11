/**
 * Mailchimp Campaign Form JavaScript
 */

jQuery(document).ready(function($) {
    
    // Initialize form functionality
    initMailchimpCampaignForm();
    
    function initMailchimpCampaignForm() {
        // Load tags when page loads
        loadTags();
        
        // Load templates when page loads
        loadTemplates();
        
        // Handle form submission
        $('#mcf-campaign-form').on('submit', handleFormSubmission);
        
        // Handle template selection
        $('#mcf-template').on('change', handleTemplateSelection);
        
        // Handle clear content button
        $('#mcf-clear-content').on('click', handleClearContent);
        
        // Real-time validation
        setupFormValidation();
    }
    
    function loadTags() {
        var $tagSelect = $('#mcf-tag');
        
        if ($tagSelect.length === 0) {
            return;
        }
        
        $.ajax({
            url: ox_mailchimp_campaign_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ox_mailchimp_campaign_get_tags',
                nonce: ox_mailchimp_campaign_ajax.nonce
            },
            beforeSend: function() {
                $tagSelect.html('<option value="">Loading tags...</option>');
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    $tagSelect.empty();
                    $tagSelect.append('<option value="">Select a tag</option>');
                    
                    $.each(response.data, function(index, tag) {
                        $tagSelect.append('<option value="' + tag.id + '">' + tag.name + '</option>');
                    });
                } else {
                    $tagSelect.html('<option value="">No tags found</option>');
                }
            },
            error: function(xhr, status, error) {
                $tagSelect.html('<option value="">Error loading tags</option>');
                showMessage('Error loading tags from Mailchimp. Please check your API configuration.', 'error');
            }
        });
    }
    
    function loadTemplates() {
        var $templateSelect = $('#mcf-template');
        
        if ($templateSelect.length === 0) {
            return;
        }
        
        $.ajax({
            url: ox_mailchimp_campaign_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ox_mailchimp_campaign_get_templates',
                nonce: ox_mailchimp_campaign_ajax.nonce
            },
            beforeSend: function() {
                $templateSelect.html('<option value="">Loading templates...</option>');
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    $templateSelect.empty();
                    $templateSelect.append('<option value="">Select a template (optional)</option>');
                    
                    $.each(response.data, function(index, template) {
                        var displayText = template.title;
                        if (template.category) {
                            displayText += ' (' + template.category + ')';
                        }
                        $templateSelect.append('<option value="' + template.id + '">' + displayText + '</option>');
                    });
                } else {
                    $templateSelect.html('<option value="">No templates found</option>');
                }
            },
            error: function(xhr, status, error) {
                $templateSelect.html('<option value="">Error loading templates</option>');
            }
        });
    }
    
    function handleTemplateSelection() {
        var templateId = $(this).val();
        
        if (!templateId) {
            return;
        }
        
        $.ajax({
            url: ox_mailchimp_campaign_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ox_mailchimp_campaign_get_template_content',
                template_id: templateId,
                nonce: ox_mailchimp_campaign_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Set content in TinyMCE editor
                    if (typeof tinymce !== 'undefined' && tinymce.get('mcf-content')) {
                        tinymce.get('mcf-content').setContent(response.data);
                    } else {
                        $('#mcf-content').val(response.data);
                    }
                }
            },
            error: function(xhr, status, error) {
                // Silent error handling for template loading
            }
        });
    }
    
    function handleClearContent() {
        // Show confirmation dialog
        if (confirm('Are you sure you want to clear all content? This action cannot be undone.')) {
            // Clear TinyMCE editor
            if (typeof tinymce !== 'undefined' && tinymce.get('mcf-content')) {
                tinymce.get('mcf-content').setContent('');
            } else {
                $('#mcf-content').val('');
            }
            
            // Clear template selection
            $('#mcf-template').val('');
            
            // Show success message
            showMessage('Content cleared successfully', 'success');
        }
    }
    
    function handleFormSubmission(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submit = $('#mcf-submit');
        var $loading = $('#mcf-loading');
        var $message = $('#mcf-message');
        
        // Prevent duplicate submissions
        if ($form.data('submitting')) {
            return false;
        }
        
        // Validate form
        if (!validateForm($form)) {
            return false;
        }
        
        // Get content from TinyMCE
        var content = getEditorContent();
        
        if (!content.trim()) {
            showMessage('Please enter email content', 'error');
            return false;
        }
        
        // Mark form as submitting to prevent duplicates
        $form.data('submitting', true);
        
        // Show loading state
        setLoadingState(true);
        
        // Prepare form data
        var formData = new FormData($form[0]);
        formData.append('action', 'ox_mailchimp_campaign_create_campaign');
        formData.append('nonce', ox_mailchimp_campaign_ajax.nonce);
        formData.set('content', content);
        
        // Send AJAX request
        $.ajax({
            url: ox_mailchimp_campaign_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showMessage(response.data, 'success');
                    resetForm($form);
                } else {
                    showMessage(response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                showMessage(ox_mailchimp_campaign_ajax.strings.error, 'error');
            },
            complete: function() {
                setLoadingState(false);
                // Reset submission flag
                $form.data('submitting', false);
            }
        });
        
        return false;
    }
    
    function validateForm($form) {
        var isValid = true;
        var requiredFields = ['subject', 'title', 'from_name', 'from_email', 'reply_to', 'tag'];
        
        // Clear previous validation messages
        $('.mcf-validation-error').remove();
        
        requiredFields.forEach(function(fieldName) {
            var $field = $form.find('[name="' + fieldName + '"]');
            var value = $field.val().trim();
            
            if (!value) {
                isValid = false;
                showFieldError($field, 'This field is required');
            }
        });
        
        // Validate email fields
        var emailFields = ['from_email', 'reply_to'];
        emailFields.forEach(function(fieldName) {
            var $field = $form.find('[name="' + fieldName + '"]');
            var value = $field.val().trim();
            
            if (value && !isValidEmail(value)) {
                isValid = false;
                showFieldError($field, 'Please enter a valid email address');
            }
        });
        
        return isValid;
    }
    
    function setupFormValidation() {
        // Real-time email validation
        $('#mcf-from-email, #mcf-reply-to').on('blur', function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !isValidEmail(value)) {
                showFieldError($field, 'Please enter a valid email address');
            } else {
                clearFieldError($field);
            }
        });
        
        // Real-time required field validation
        $('#mcf-subject, #mcf-title, #mcf-from-name, #mcf-tag').on('blur', function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (!value) {
                showFieldError($field, 'This field is required');
            } else {
                clearFieldError($field);
            }
        });
    }
    
    function getEditorContent() {
        if (typeof tinymce !== 'undefined' && tinymce.get('mcf-content')) {
            return tinymce.get('mcf-content').getContent();
        } else {
            return $('#mcf-content').val();
        }
    }
    
    function setLoadingState(loading) {
        var $submit = $('#mcf-submit');
        var $loading = $('#mcf-loading');
        
        if (loading) {
            $submit.prop('disabled', true).text('Sending...');
            $loading.show();
        } else {
            $submit.prop('disabled', false).text(ox_mailchimp_campaign_ajax.submit_text || 'Send Campaign');
            $loading.hide();
        }
    }
    
    function showMessage(message, type) {
        var $message = $('#mcf-message');
        var cssClass = type === 'success' ? 'mcf-success' : 'mcf-error';
        
        $message.html('<div class="' + cssClass + '">' + message + '</div>').show();
        
        // Auto-hide success messages after 10 seconds
        if (type === 'success') {
            setTimeout(function() {
                $message.fadeOut();
            }, 10000);
        }
    }
    
    function resetForm($form) {
        $form[0].reset();
        
        // Clear TinyMCE editor
        if (typeof tinymce !== 'undefined' && tinymce.get('mcf-content')) {
            tinymce.get('mcf-content').setContent('');
        }
        
        // Clear validation errors
        $('.mcf-validation-error').remove();
    }
    
    function showFieldError($field, message) {
        clearFieldError($field);
        
        $field.addClass('mcf-field-error');
        $field.after('<div class="mcf-validation-error">' + message + '</div>');
    }
    
    function clearFieldError($field) {
        $field.removeClass('mcf-field-error');
        $field.siblings('.mcf-validation-error').remove();
    }
    
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
}); 