jQuery(document).ready(function($) {
    console.log('Excel Processing Popup JS Loaded');

    // Create enhanced popup dialog
    var $dialog = $('#excel-comment-popup');
    if (!$dialog.length) {
        $dialog = $('<div id="excel-comment-popup" title="Edit Comments"></div>').appendTo('body');
    }

    // Enhanced dialog configuration
    $dialog.dialog({
        autoOpen: false,
        modal: true,
        width: 700,
        height: 'auto',
        maxHeight: $(window).height() * 0.8,
        resizable: true,
        draggable: true,
        closeOnEscape: true,
        buttons: {
            "Save": {
                text: "Save Changes",
                class: "save-button",
                click: function() {
                    saveComments($(this));
                }
            },
            "Cancel": {
                text: "Cancel",
                class: "cancel-button",
                click: function() {
                    $(this).dialog('close');
                }
            }
        },
        open: function() {
            // Add loading state
            $(this).addClass('loading');
            
            // Focus on first textarea
            setTimeout(function() {
                $dialog.find('textarea:first').focus();
                $dialog.removeClass('loading');
            }, 300);
            
            // Add character count
            addCharacterCount();
            
            // Add auto-resize for textareas
            autoResizeTextareas();
        },
        close: function() {
            // Clean up
            $(this).find('form').trigger('reset');
            $(this).removeClass('loading');
        }
    });

    // Enhanced edit comment click handler
    $(document).on('click', '.edit-comment', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var recordId = $(this).data('id');
        var field = $(this).data('field');
        var $button = $(this);
        
        console.log('Edit clicked for record ID:', recordId, 'Field:', field);

        // Show loading state
        $button.addClass('loading');
        
        // Create enhanced form
        createEnhancedForm(recordId);

        // Fetch current comments with loading state
        fetchComments(recordId, $button);
    });

    // Create enhanced form
    function createEnhancedForm(recordId) {
        $dialog.html(`
            <div class="form-container">
                <div class="form-header">
                    <h3>Edit Comments for Record #${recordId}</h3>
                    <p class="form-subtitle">Update the comments for this record. All fields are optional.</p>
                </div>
                
                <form id="excel-comment-form" class="enhanced-form">
                    <div class="form-section">
                        <h4>Comments</h4>
                        <div class="form-group">
                            <label for="beebe_comments">
                                <span class="label-text">Beebe Comments</span>
                                <span class="char-count" id="beebe-count">0/1000</span>
                            </label>
                            <textarea 
                                name="beebe_comments" 
                                id="beebe_comments" 
                                rows="4" 
                                maxlength="1000"
                                placeholder="Enter Beebe comments here..."
                                class="enhanced-textarea"
                            ></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="aec_comments">
                                <span class="label-text">AEC Comments</span>
                                <span class="char-count" id="aec-count">0/1000</span>
                            </label>
                            <textarea 
                                name="aec_comments" 
                                id="aec_comments" 
                                rows="4" 
                                maxlength="1000"
                                placeholder="Enter AEC comments here..."
                                class="enhanced-textarea"
                            ></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="epic_comments">
                                <span class="label-text">Epic Comments</span>
                                <span class="char-count" id="epic-count">0/1000</span>
                            </label>
                            <textarea 
                                name="epic_comments" 
                                id="epic_comments" 
                                rows="4" 
                                maxlength="1000"
                                placeholder="Enter Epic comments here..."
                                class="enhanced-textarea"
                            ></textarea>
                        </div>
                    </div>
                    
                    <input type="hidden" name="record_id" value="${recordId}">
                    
                    <div class="form-actions">
                        <div class="form-info">
                            <span class="info-text">Press Ctrl+S to save, Esc to cancel</span>
                        </div>
                    </div>
                </form>
            </div>
        `);

        // Add form validation
        addFormValidation();
        
        // Add keyboard shortcuts
        addKeyboardShortcuts();
    }

    // Fetch comments with enhanced error handling
    function fetchComments(recordId, $button) {
        $.ajax({
            url: excelProcessing.ajaxurl,
            method: 'POST',
            data: {
                action: 'excel_processing_get_comments',
                record_id: recordId
            },
            success: function(response) {
                console.log('Fetch response:', response);
                if (response.success) {
                    populateForm(response.data);
                    $dialog.dialog('open');
                } else {
                    showErrorMessage('Error fetching comments: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.log('Fetch AJAX error:', status, error);
                showErrorMessage('Failed to fetch comments: ' + error);
            },
            complete: function() {
                $button.removeClass('loading');
            }
        });
    }

    // Populate form with data
    function populateForm(data) {
        $dialog.find('textarea[name="beebe_comments"]').val(data.beebe_comments || '');
        $dialog.find('textarea[name="aec_comments"]').val(data.aec_comments || '');
        $dialog.find('textarea[name="epic_comments"]').val(data.epic_comments || '');
        
        // Update character counts
        updateCharacterCounts();
        
        // Trigger auto-resize
        $dialog.find('textarea').each(function() {
            autoResizeTextarea($(this));
        });
    }

    // Save comments with enhanced functionality
    function saveComments($dialog) {
        var $form = $dialog.find('form');
        var formData = $form.serialize() + '&action=excel_processing_update_comments';
        
        // Show saving state
        $dialog.addClass('saving');
        $dialog.find('.save-button').prop('disabled', true).text('Saving...');
        
        console.log('Saving comments:', formData);
        
        $.ajax({
            url: excelProcessing.ajaxurl,
            method: 'POST',
            data: formData,
            success: function(response) {
                console.log('Save response:', response);
                if (response.success) {
                    showSuccessMessage('Comments updated successfully!');
                    $dialog.dialog('close');
                    
                    // Update the table row
                    updateTableRow(response.data);
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showErrorMessage('Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.log('Save AJAX error:', status, error);
                showErrorMessage('Failed to save comments: ' + error);
            },
            complete: function() {
                $dialog.removeClass('saving');
                $dialog.find('.save-button').prop('disabled', false).text('Save Changes');
            }
        });
    }

    // Add character count functionality
    function addCharacterCount() {
        $dialog.find('textarea').on('input', function() {
            updateCharacterCount($(this));
        });
    }

    // Update character count for a specific textarea
    function updateCharacterCount($textarea) {
        var fieldName = $textarea.attr('name');
        var count = $textarea.val().length;
        var maxLength = $textarea.attr('maxlength');
        var $countElement = $dialog.find('#' + fieldName.replace('_', '-') + '-count');
        
        $countElement.text(count + '/' + maxLength);
        
        // Add visual feedback
        if (count > maxLength * 0.9) {
            $countElement.addClass('warning');
        } else {
            $countElement.removeClass('warning');
        }
    }

    // Update all character counts
    function updateCharacterCounts() {
        $dialog.find('textarea').each(function() {
            updateCharacterCount($(this));
        });
    }

    // Auto-resize textareas
    function autoResizeTextareas() {
        $dialog.find('textarea').each(function() {
            autoResizeTextarea($(this));
        });
    }

    function autoResizeTextarea($textarea) {
        $textarea.on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Initial resize
        $textarea.trigger('input');
    }

    // Add form validation
    function addFormValidation() {
        $dialog.find('form').on('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            var isValid = true;
            var $form = $(this);
            
            $form.find('textarea').each(function() {
                var $textarea = $(this);
                var value = $textarea.val().trim();
                
                if (value.length > 1000) {
                    showFieldError($textarea, 'Maximum 1000 characters allowed');
                    isValid = false;
                } else {
                    clearFieldError($textarea);
                }
            });
            
            if (isValid) {
                saveComments($dialog);
            }
        });
    }

    // Add keyboard shortcuts
    function addKeyboardShortcuts() {
        $dialog.on('keydown', function(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                saveComments($(this));
            }
            
            // Escape to close
            if (e.key === 'Escape') {
                $(this).dialog('close');
            }
        });
    }

    // Show success message
    function showSuccessMessage(message) {
        var $message = $('<div class="message success">' + message + '</div>');
        $('.excel-comments-edit').prepend($message);
        
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Show error message
    function showErrorMessage(message) {
        var $message = $('<div class="message error">' + message + '</div>');
        $('.excel-comments-edit').prepend($message);
        
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Show field error
    function showFieldError($field, message) {
        $field.addClass('error');
        var $error = $('<div class="field-error">' + message + '</div>');
        $field.after($error);
    }

    // Clear field error
    function clearFieldError($field) {
        $field.removeClass('error');
        $field.siblings('.field-error').remove();
    }

    // Update table row after save
    function updateTableRow(data) {
        // This function can be used to update the table row without reloading
        // For now, we'll just reload the page
    }

    // Add enhanced CSS for the popup
    addEnhancedStyles();
});

// Add enhanced styles for the popup
function addEnhancedStyles() {
    var styles = `
        <style>
            .form-container {
                padding: 0;
            }
            
            .form-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid #e9ecef;
            }
            
            .form-header h3 {
                margin: 0 0 5px 0;
                color: #333;
                font-size: 18px;
            }
            
            .form-subtitle {
                margin: 0;
                color: #666;
                font-size: 14px;
            }
            
            .form-section {
                margin-bottom: 25px;
            }
            
            .form-section h4 {
                margin: 0 0 15px 0;
                color: #495057;
                font-size: 16px;
                font-weight: 600;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
                font-weight: 500;
                color: #495057;
            }
            
            .char-count {
                font-size: 12px;
                color: #6c757d;
                font-weight: normal;
            }
            
            .char-count.warning {
                color: #dc3545;
            }
            
            .enhanced-textarea {
                width: 100%;
                padding: 12px;
                border: 2px solid #e1e5e9;
                border-radius: 8px;
                font-size: 14px;
                font-family: inherit;
                resize: vertical;
                transition: all 0.3s ease;
                min-height: 80px;
            }
            
            .enhanced-textarea:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .enhanced-textarea.error {
                border-color: #dc3545;
            }
            
            .field-error {
                color: #dc3545;
                font-size: 12px;
                margin-top: 5px;
            }
            
            .form-actions {
                margin-top: 25px;
                padding-top: 15px;
                border-top: 1px solid #e9ecef;
            }
            
            .form-info {
                text-align: center;
            }
            
            .info-text {
                color: #6c757d;
                font-size: 12px;
                font-style: italic;
            }
            
            .ui-dialog.saving .ui-dialog-content {
                opacity: 0.6;
                pointer-events: none;
            }
            
            .save-button {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            }
            
            .save-button:hover {
                background: linear-gradient(135deg, #218838 0%, #1ea085 100%) !important;
            }
            
            .cancel-button {
                background: #6c757d !important;
            }
            
            .cancel-button:hover {
                background: #5a6268 !important;
            }
        </style>
    `;
    
    $('head').append(styles);
}