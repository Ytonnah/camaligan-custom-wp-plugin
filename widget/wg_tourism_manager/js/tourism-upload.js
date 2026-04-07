// Tourism Upload JavaScript

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeMediaUpload();
        initializeFormSubmit();
    });

    function initializeMediaUpload() {
        var mediaFrame;
        var imagePreview = $('#image-preview');

        $(document).on('click', '#upload_tourism_image', function(e) {
            e.preventDefault();

            // Create a new media frame
            mediaFrame = wp.media({
                title: 'Select Tourism Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            // Handle image selection
            mediaFrame.on('select', function() {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                
                $('#tourism_image_id').val(attachment.id);
                imagePreview.html('<img src="' + attachment.url + '" alt="Preview">');
            });

            mediaFrame.open();
        });
    }

    function initializeFormSubmit() {
        $('#tourism-upload-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var formData = new FormData(this);
            var statusDiv = $('#upload-status');
            var isEditing = form.data('edit-id');
            var action = isEditing ? 'update_tourism' : 'upload_tourism';

            formData.append('action', action);
            if (!formData.has('nonce')) {
                formData.append('nonce', tourismUploadData.nonce);
            }
            if (isEditing) {
                formData.append('post_id', isEditing);
            }

            $.ajax({
                url: tourismUploadData.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    statusDiv.removeClass('success error').addClass('loading').show()
                        .text('Processing tourism destination...');
                },
                success: function(response) {
                    if (response.success) {
                        statusDiv.removeClass('error loading').addClass('success')
                            .text('✓ ' + response.data.message);
                        form[0].reset();
                        form.removeData('edit-id');
                        form.attr('data-editing', 'false');
                        $('button[type="submit"]').text('Upload Destination');
                        $('#image-preview').html('');
                        
                        setTimeout(function() {
                            statusDiv.fadeOut();
                            location.reload();
                        }, 2000);
                    } else {
                        statusDiv.removeClass('loading').addClass('error')
                            .text('✗ ' + response.data);
                    }
                },
                error: function() {
                    statusDiv.removeClass('loading').addClass('error')
                        .text('✗ An error occurred while processing.');
                }
            });
        });
    }

})(jQuery);
