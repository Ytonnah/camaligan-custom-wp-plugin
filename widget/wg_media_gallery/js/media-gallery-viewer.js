// Media Gallery Viewer JavaScript

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeSearch();
        initializeGalleryButtons();
    });

    function initializeSearch() {
        var searchTimeout;
        $('#gallery-search').on('keyup', function() {
            clearTimeout(searchTimeout);
            var searchTerm = $(this).val();

            searchTimeout = setTimeout(function() {
                if (searchTerm.length > 0) {
                    performSearch(searchTerm);
                } else {
                    location.reload();
                }
            }, 500);
        });
    }

    function performSearch(searchTerm) {
        $.ajax({
            url: mediaGalleryViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'search_galleries',
                search_term: searchTerm,
                nonce: mediaGalleryViewerData.nonce
            },
            beforeSend: function() {
                $('#gallery-list').html('<div class="gallery-empty"><p>Searching...</p></div>');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderSearchResults(response.data);
                } else {
                    $('#gallery-list').html('<div class="gallery-empty"><p>No galleries found.</p></div>');
                }
            },
            error: function() {
                $('#gallery-list').html('<div class="gallery-empty"><p>Error searching galleries.</p></div>');
            }
        });
    }

    function renderSearchResults(galleries) {
        var html = '';

        galleries.forEach(function(gallery) {
            html += '<div class="gallery-card" data-gallery-id="' + gallery.id + '">';
            
            if (gallery.cover_image) {
                html += '<img src="' + gallery.cover_image + '" alt="' + gallery.title + '" class="gallery-cover">';
            } else {
                html += '<div class="gallery-cover placeholder">No images</div>';
            }

            html += '<div class="gallery-info">';
            html += '<h3>' + gallery.title + '</h3>';
            html += '<p class="image-count">' + gallery.image_count + ' image' + (gallery.image_count !== 1 ? 's' : '') + '</p>';
            
            if (gallery.description) {
                html += '<p class="gallery-description">' + gallery.description.substring(0, 50) + '...</p>';
            }

            html += '</div>';
            html += '<div class="gallery-actions">';
            html += '<button class="btn btn-primary btn-sm view-gallery-btn" data-gallery-id="' + gallery.id + '">View</button>';
            html += '<button class="btn btn-danger btn-sm delete-gallery-btn" data-gallery-id="' + gallery.id + '">Delete</button>';
            html += '</div>';
            html += '</div>';
        });

        $('#gallery-list').html(html);
        initializeGalleryButtons();
    }

    function initializeGalleryButtons() {
        // Delete gallery
        $(document).off('click', '.delete-gallery-btn').on('click', '.delete-gallery-btn', function() {
            if (!confirm('Delete this entire gallery? This action cannot be undone.')) {
                return;
            }

            var galleryId = $(this).data('gallery-id');
            deleteGallery(galleryId);
        });

        // View gallery images
        $(document).off('click', '.view-gallery-btn').on('click', '.view-gallery-btn', function() {
            var galleryId = $(this).data('gallery-id');
            viewGalleryImages(galleryId);
        });
    }

    function deleteGallery(galleryId) {
        $.ajax({
            url: mediaGalleryViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_gallery',
                nonce: mediaGalleryViewerData.nonce,
                gallery_id: galleryId
            },
            success: function(response) {
                if (response.success) {
                    alert('✓ Gallery deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error deleting gallery');
            }
        });
    }

    function viewGalleryImages(galleryId) {
        $.ajax({
            url: mediaGalleryViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_gallery_images',
                nonce: mediaGalleryViewerData.nonce,
                gallery_id: galleryId
            },
            success: function(response) {
                if (response.success) {
                    displayImageModal(response.data);
                } else {
                    alert('Error loading gallery images');
                }
            }
        });
    }

    function displayImageModal(images) {
        if (images.length === 0) {
            alert('No images in this gallery');
            return;
        }

        var html = '<div class="gallery-modal-grid">';
        images.forEach(function(image, index) {
            html += '<div class="gallery-modal-item">';
            html += '<img src="' + image.thumb + '" alt="' + image.caption + '" data-full-url="' + image.url + '" data-index="' + index + '" class="modal-gallery-image">';
            if (image.caption) {
                html += '<p class="image-caption">' + image.caption + '</p>';
            }
            html += '</div>';
        });
        html += '</div>';

        // Create extended modal
        var modalContent = '<div class="modal-content gallery-images-modal">';
        modalContent += '<span class="modal-close">&times;</span>';
        modalContent += '<h3>Gallery Images</h3>';
        modalContent += html;
        modalContent += '</div>';

        var modal = $('<div class="modal"></div>').html(modalContent);
        $('body').append(modal);

        // Click to view full size
        $('.modal-gallery-image').on('click', function() {
            var fullUrl = $(this).data('full-url');
            var caption = $(this).parent().find('.image-caption').text();
            
            showImageDetail(fullUrl, caption);
        });

        // Close modal
        $('.modal-close').on('click', function() {
            $(this).closest('.modal').remove();
        });

        $(document).on('click', '.modal', function(e) {
            if (e.target === this) {
                $(this).remove();
            }
        });
    }

    function showImageDetail(imageUrl, caption) {
        var detailHtml = '<div class="modal-content image-detail-modal">';
        detailHtml += '<span class="modal-close">&times;</span>';
        detailHtml += '<img src="' + imageUrl + '" alt="" class="modal-full-image">';
        if (caption) {
            detailHtml += '<p class="image-caption">' + caption + '</p>';
        }
        detailHtml += '</div>';

        var modal = $('<div class="modal"></div>').html(detailHtml);
        $('body').append(modal);

        // Close handlers
        $('.modal-close').on('click', function() {
            $(this).closest('.modal').remove();
        });

        $(document).on('click', '.modal', function(e) {
            if (e.target === this) {
                $(this).remove();
            }
        });
    }

})(jQuery);
