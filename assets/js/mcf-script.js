/**
 * Mailchimp Campaign Form JavaScript
 */

jQuery(document).ready(function($) {
    
    // Initialize form functionality
    initMailchimpCampaignForm();
    
    // Store the currently selected template ID for event re-processing
    var currentTemplateId = null;
    
    // Flag to prevent event handler firing during programmatic changes
    var isResettingEvent = false;
    
    // Flag to prevent handlers during initial page load
    var isInitializing = true;
    
    function initMailchimpCampaignForm() {
        // Handle form submission
        $('#mcf-campaign-form').on('submit', handleFormSubmission);
        
        // Handle template selection
        $('#mcf-template').on('change', handleTemplateSelection);
        
        // Handle event selection
        $('#mcf-event').on('change', handleEventSelection);
        
        // Handle clear content button
        $('#mcf-clear-content').on('click', handleClearContent);
        
        // Real-time validation
        setupFormValidation();
        
        // Setup dropdown functionality
        setupDropdownFields();
        
        // Load data AFTER handlers are bound
        // Use callbacks to know when loading is complete
        loadTags();
        loadTemplates(function() {
            loadEvents(function() {
                // All loading complete, allow handlers to work
                isInitializing = false;
            });
        });
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
    
    function loadTemplates(callback) {
        var $templateSelect = $('#mcf-template');
        
        if ($templateSelect.length === 0) {
            if (typeof callback === 'function') callback();
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
                if (typeof callback === 'function') callback();
            },
            error: function(xhr, status, error) {
                $templateSelect.html('<option value="">Error loading templates</option>');
                if (typeof callback === 'function') callback();
            }
        });
    }
    
    function loadEvents(callback) {
        var $eventSelect = $('#mcf-event');
        
        if ($eventSelect.length === 0) {
            if (typeof callback === 'function') callback();
            return;
        }
        
        $.ajax({
            url: ox_mailchimp_campaign_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ox_mailchimp_campaign_get_events',
                nonce: ox_mailchimp_campaign_ajax.nonce
            },
            beforeSend: function() {
                $eventSelect.html('<option value="">Loading events...</option>');
            },
            success: function(response) {
                $eventSelect.empty();
                $eventSelect.append('<option value=""></option>');
                
                if (response.success && response.data && response.data.length > 0) {
                    $.each(response.data, function(index, event) {
                        $eventSelect.append('<option value="' + event.id + '">' + event.title + '</option>');
                    });
                }
                // If no events found, the dropdown just shows the default option
                if (typeof callback === 'function') callback();
            },
            error: function(xhr, status, error) {
                $eventSelect.html('<option value=""></option>');
                if (typeof callback === 'function') callback();
            }
        });
    }
    
    function handleTemplateSelection() {
        // Skip during initial page load
        if (isInitializing) {
            return;
        }
        
        var templateId = $(this).val();
        
        if (!templateId) {
            currentTemplateId = null;
            return;
        }
        
        // Store the template ID for potential re-processing with different event
        currentTemplateId = templateId;
        
        // Reset event selection to default when template changes
        // Use flag to prevent handleEventSelection from firing
        isResettingEvent = true;
        $('#mcf-event').val('');
        isResettingEvent = false;
        
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
    
    function handleEventSelection() {
        // Skip during initial page load or programmatic reset
        if (isInitializing || isResettingEvent) {
            return;
        }
        
        var eventId = $(this).val();
        
        // If no template is selected, event selection does nothing
        if (!currentTemplateId) {
            return;
        }
        
        // If event is reset to default (empty), reload template with next-event
        if (!eventId) {
            // Re-fetch the template with default (next-event) processing
            $.ajax({
                url: ox_mailchimp_campaign_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ox_mailchimp_campaign_get_template_content',
                    template_id: currentTemplateId,
                    nonce: ox_mailchimp_campaign_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        if (typeof tinymce !== 'undefined' && tinymce.get('mcf-content')) {
                            tinymce.get('mcf-content').setContent(response.data);
                        } else {
                            $('#mcf-content').val(response.data);
                        }
                    }
                }
            });
            return;
        }
        
        // Process template with the selected event
        $.ajax({
            url: ox_mailchimp_campaign_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ox_mailchimp_campaign_process_template_with_event',
                template_id: currentTemplateId,
                event_id: eventId,
                nonce: ox_mailchimp_campaign_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Replace content in TinyMCE editor with event-processed content
                    if (typeof tinymce !== 'undefined' && tinymce.get('mcf-content')) {
                        tinymce.get('mcf-content').setContent(response.data);
                    } else {
                        $('#mcf-content').val(response.data);
                    }
                }
            },
            error: function(xhr, status, error) {
                showMessage('Error loading event content. Please try again.', 'error');
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
            
            // Clear event selection (with flag to prevent handler)
            isResettingEvent = true;
            $('#mcf-event').val('');
            isResettingEvent = false;
            
            // Reset stored template ID
            currentTemplateId = null;
            
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
        
        // Reset stored template ID
        currentTemplateId = null;
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
    
    /**
     * Setup autocomplete functionality for from name and from email fields
     */
    function setupDropdownFields() {
        // Setup From Name autocomplete
        setupAutocompleteField('#mcf-from-name', '#mcf-from-name-suggestions');
        
        // Setup From Email autocomplete
        setupAutocompleteField('#mcf-from-email', '#mcf-from-email-suggestions', function(selectedValue) {
            // Auto-sync to Reply-To field if it's not readonly
            var $replyTo = $('#mcf-reply-to');
            if (!$replyTo.prop('readonly')) {
                $replyTo.val(selectedValue);
            }
        });
        
        // Setup Reply-To autocomplete (only if not readonly)
        var $replyTo = $('#mcf-reply-to');
        if (!$replyTo.prop('readonly')) {
            setupAutocompleteField('#mcf-reply-to', '#mcf-reply-to-suggestions');
        }
        
        // Handle manual typing in From Email field - sync to Reply-To
        $('#mcf-from-email').on('input', function() {
            var emailValue = $(this).val();
            var $replyTo = $('#mcf-reply-to');
            if (!$replyTo.prop('readonly') && emailValue && isValidEmail(emailValue)) {
                $replyTo.val(emailValue);
            }
        });
        
        // Handle manual typing in Reply-To field - clear any validation errors if email is valid
        $('#mcf-reply-to').on('input', function() {
            var emailValue = $(this).val();
            if (emailValue && isValidEmail(emailValue)) {
                clearFieldError($(this));
            }
        });
    }
    
    /**
     * Setup autocomplete for a specific field
     */
    function setupAutocompleteField(inputSelector, suggestionsSelector, onSelectCallback) {
        var $input = $(inputSelector);
        var $suggestions = $(suggestionsSelector);
        var $field = $input.closest('.mcf-autocomplete-field');
        
        // Show/hide suggestions based on input
        $input.on('input focus', function() {
            var inputValue = $(this).val().toLowerCase();
            
            if (inputValue.length > 0) {
                // Filter suggestions
                $suggestions.find('.mcf-suggestion').each(function() {
                    var suggestionText = $(this).text().toLowerCase();
                    if (suggestionText.indexOf(inputValue) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                
                // Show suggestions if any match
                var visibleSuggestions = $suggestions.find('.mcf-suggestion:visible').length;
                if (visibleSuggestions > 0) {
                    $suggestions.show();
                } else {
                    $suggestions.hide();
                }
            } else {
                // Show all suggestions when input is empty
                $suggestions.find('.mcf-suggestion').show();
                $suggestions.show();
            }
        });
        
        // Hide suggestions when input loses focus (with delay for clicking)
        $input.on('blur', function() {
            setTimeout(function() {
                // Only hide if no suggestion was clicked
                if (!$suggestions.data('clicked')) {
                    $suggestions.hide();
                }
                $suggestions.removeData('clicked');
            }, 200);
        });
        
        // Handle suggestion clicks
        $suggestions.on('click', '.mcf-suggestion', function() {
            // Mark that a suggestion was clicked to prevent blur validation
            $suggestions.data('clicked', true);
            
            var selectedValue = $(this).data('value');
            $input.val(selectedValue);
            $suggestions.hide();
            
            // Call callback if provided
            if (typeof onSelectCallback === 'function') {
                onSelectCallback(selectedValue);
            }
            
            // Clear any existing validation errors
            clearFieldError($input);
            
            // Trigger change event
            $input.trigger('change');
        });
        
        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest($field).length) {
                $suggestions.hide();
            }
        });
        
        // Handle keyboard navigation
        $input.on('keydown', function(e) {
            var $visibleSuggestions = $suggestions.find('.mcf-suggestion:visible');
            var $active = $visibleSuggestions.filter('.active');
            
            if (e.keyCode === 40) { // Down arrow
                e.preventDefault();
                if ($active.length === 0) {
                    $visibleSuggestions.first().addClass('active');
                } else {
                    $active.removeClass('active').next().addClass('active');
                }
            } else if (e.keyCode === 38) { // Up arrow
                e.preventDefault();
                if ($active.length === 0) {
                    $visibleSuggestions.last().addClass('active');
                } else {
                    $active.removeClass('active').prev().addClass('active');
                }
            } else if (e.keyCode === 13) { // Enter
                e.preventDefault();
                if ($active.length > 0) {
                    $active.click();
                }
            } else if (e.keyCode === 27) { // Escape
                $suggestions.hide();
                $visibleSuggestions.removeClass('active');
            }
        });
    }
    
}); 