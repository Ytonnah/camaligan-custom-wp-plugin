// Media Gallery Manager JavaScript

(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('Media gallery script loaded');
        if (typeof mediaGalleryData === 'undefined') {
            console.error('mediaGalleryData not defined');
            return;
        }
        initializeGalleryControls();
        initializeFormSubmit();
    });

    function initializeGalleryControls() {
        // New gallery button
        $('#new-gallery-btn').on('click', function() {
            $('#new-gallery-form').toggle();
        });

        // Cancel new gallery form
        $('#cancel-gallery-btn').on('click', function() {
            $('#new-gallery-form').hide();
            $('#gallery-name').val('');
            $('#gallery-description').val('');
        });

        // Create new gallery
        $('#create-gallery-btn').on('click', function() {
            createNewGallery();
        });

        // Gallery selection change
        $('#gallery-selector').on('change', function() {
            var galleryId = $(this).val();
            if (galleryId) {
                $('#image-upload-section, #gallery-images-section').show();
                loadGalleryImages(galleryId);
            } else {
                $('#image-upload-section, #gallery-images-section').hide();
            }
        });
    }

    function createNewGallery() {
        var galleryName = $('#gallery-name').val().trim();
        var galleryDesc = $('#gallery-description').val().trim();

        if (!galleryName) {
            alert('Please enter a gallery name');
            return;
        }

        $.ajax({
            url: mediaGalleryData.ajaxurl,
            type: 'POST',
            data: {
                action: 'create_gallery',
                nonce: mediaGalleryData.nonce,
                gallery_name: galleryName,
                gallery_description: galleryDesc
            },
            beforeSend: function() {
                $('#create-gallery-btn').prop('disabled', true).text('Creating...');
            },
            success: function(response) {
                if (response.success) {
                    // Add new gallery to selector
                    var option = $('<option>').val(response.data.gallery_id).text(galleryName);
                    $('#gallery-selector').append(option).val(response.data.gallery_id).trigger('change');

                    // Reset form
                    $('#gallery-name').val('');
                    $('#gallery-description').val('');
                    $('#new-gallery-form').hide();

                    // Show success
                    showStatus('✓ ' + response.data.message, 'success');
                } else {
                    showStatus('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showStatus('Error creating gallery', 'error');
            },
            complete: function() {
                $('#create-gallery-btn').prop('disabled', false).text('Create Gallery');
            }
        });
    }

    function initializeFormSubmit() {
        $('#media-upload-form').on('submit', function(e) {
            e.preventDefault();

            var galleryId = $('#gallery-selector').val();
            if (!galleryId) {
                alert('Please select a gallery first');
                return;
            }

            var files = $('#gallery-images')[0].files;
            if (files.length === 0) {
                alert('Please select at least one image');
                return;
            }

            uploadImages(files, galleryId);
        });
    }

    function uploadImages(files, galleryId) {
        var formData = new FormData();
        formData.append('action', 'upload_gallery_image');
        formData.append('nonce', mediaGalleryData.nonce);
        formData.append('gallery_id', galleryId);

        // Add all files
        for (var i = 0; i < files.length; i++) {
            formData.append('images[]', files[i]);
        }

        $.ajax({
            url: mediaGalleryData.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            progress: function(e) {
                if (e.lengthComputable) {
                    var percentComplete = (e.loaded / e.total) * 100;
                    updateProgress(percentComplete);
                }
            },
            beforeSend: function() {
                $('#upload-progress').show();
                $('#media-upload-form button[type="submit"]').prop('disabled', true).text('Uploading...');
            },
            success: function(response) {
                if (response.success) {
                    showStatus('✓ ' + response.data.message, 'success');
                    $('#media-upload-form')[0].reset();
                    $('#gallery-images').val('');
                    loadGalleryImages(galleryId);
                } else {
                    showStatus('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showStatus('Error uploading images', 'error');
            },
            complete: function() {
                $('#upload-progress').hide();
                $('#media-upload-form button[type="submit"]').prop('disabled', false).text('Upload Images');
            }
        });
    }

    function loadGalleryImages(galleryId) {
        $.ajax({
            url: mediaGalleryData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_gallery_images',
                nonce: mediaGalleryData.nonce,
                gallery_id: galleryId
            },
            success: function(response) {
                if (response.success) {
                    renderGalleryImages(response.data);
                }
            }
        });
    }

    function renderGalleryImages(images) {
        var html = '';

        if (images.length === 0) {
            html = '<p style="text-align:center;color:#999;">No images in this gallery yet</p>';
        } else {
            images.forEach(function(image) {
                html += '<div class="gallery-item" data-image-id="' + image.id + '">';
                html += '<img src="' + image.thumb + '" alt="' + image.caption + '">';
                html += '<div class="gallery-item-overlay">';
                html += '<div class="gallery-item-actions">';
                html += '<button class="btn btn-primary btn-sm view-image" data-image-id="' + image.id + '">View</button>';
                html += '<button class="btn btn-danger btn-sm delete-image" data-image-id="' + image.id + '">Delete</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
        }

        $('#gallery-images-list').html(html);

        // Bind delete buttons
        $('.delete-image').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            deleteImage($(this).data('image-id'));
        });

        // Bind view buttons
        $('.view-image').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var imageId = $(this).data('image-id');
            var image = images.find(img => img.id == imageId);
            if (image) {
                showImageDetail(image);
            }
        });
    }

    function deleteImage(imageId) {
        if (!confirm('Delete this image?')) {
            return;
        }

        var galleryId = $('#gallery-selector').val();

        $.ajax({
            url: mediaGalleryData.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_gallery_image',
                nonce: mediaGalleryData.nonce,
                image_id: imageId,
                gallery_id: galleryId
            },
            success: function(response) {
                if (response.success) {
                    showStatus('✓ Image deleted', 'success');
                    loadGalleryImages(galleryId);
                } else {
                    showStatus('Error: ' + response.data, 'error');
                }
            }
        });
    }

    function showImageDetail(image) {
        $('#modal-image').attr('src', image.url);
        $('#modal-title').text(image.title);
        $('#modal-caption').text(image.caption || 'No caption');
        $('#caption-input').val(image.caption);
        $('#image-detail-modal').data('image-id', image.id).show();
    }

    function updateProgress(percent) {
        $('.progress-bar').css('width', percent + '%');
        $('.progress-text').text(Math.round(percent) + '%');
    }

    function showStatus(message, type) {
        var statusDiv = $('#upload-status');
        statusDiv.removeClass('success error loading').addClass(type).text(message).fadeIn();

        setTimeout(function() {
            statusDiv.fadeOut();
        }, 3000);
    }

    // Modal close handlers
    $(document).on('click', '.modal-close', function() {
        $(this).closest('.modal').hide();
    });

    $(document).on('click', '.modal', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });

    // Save caption
    $(document).on('click', '#save-caption-btn', function() {
        var imageId = $('#image-detail-modal').data('image-id');
        var caption = $('#caption-input').val();

        $.ajax({
            url: mediaGalleryData.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_image_caption',
                nonce: mediaGalleryData.nonce,
                image_id: imageId,
                caption: caption
            },
            success: function(response) {
                if (response.success) {
                    showStatus('✓ Caption updated', 'success');
                } else {
                    showStatus('Error updating caption', 'error');
                }
            }
        });
    });

})(jQuery);
