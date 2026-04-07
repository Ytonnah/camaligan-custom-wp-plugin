// Beneficiaries Upload Form JavaScript

(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('Beneficiaries upload script loaded');
        initializeMediaUpload();
        initializeFormSubmit();
    });

    function initializeMediaUpload() {
        $('#upload-image-btn').on('click', function(e) {
            e.preventDefault();
            
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                alert('WordPress Media Library not available');
                return;
            }
            
            var frame = wp.media({
                title: 'Select Beneficiary Photo',
                button: { text: 'Use this image' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#beneficiary_image_id').val(attachment.id);
                
                var preview = '<img src="' + attachment.url + '" alt="Preview">';
                $('#image-preview').html(preview);
            });

            frame.open();
        });
    }

    function initializeFormSubmit() {
        $('#beneficiaries-upload-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var formData = new FormData(this);
            var statusDiv = $('#upload-status');
            var isEditing = form.data('edit-id');
            var action = isEditing ? 'update_beneficiary' : 'upload_beneficiary';

            formData.append('action', action);
            if (!formData.has('nonce')) {
                formData.append('nonce', beneficiariesUploadData.nonce);
            }
            if (isEditing) {
                formData.append('post_id', isEditing);
            }

            $.ajax({
                url: beneficiariesUploadData.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    statusDiv.removeClass('success error').addClass('loading').show()
                        .text('Processing beneficiary...');
                },
                success: function(response) {
                    if (response.success) {
                        statusDiv.removeClass('error loading').addClass('success')
                            .text('✓ ' + response.data.message);
                        form[0].reset();
                        form.removeData('edit-id');
                        form.attr('data-editing', 'false');
                        $('button[type="submit"]').text('Add Beneficiary');
                        $('#image-preview').html('');
                        
                        setTimeout(function() {
                            statusDiv.fadeOut();
                            location.reload();
                        }, 2000);
                    } else {
                        statusDiv.removeClass('loading').addClass('error')
                            .text('❌ ' + response.data);
                    }
                },
                error: function() {
                    statusDiv.removeClass('loading').addClass('error')
                        .text('❌ Error processing beneficiary');
                }
            });
        });
    }

})(jQuery);
