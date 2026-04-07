// Tourism Viewer JavaScript

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeSearchFilter();
        initializeTypeFilter();
        initializeDetailButtons();
    });

    function initializeSearchFilter() {
        var searchInput = $('#tourism-search');
        var searchTimeout;

        searchInput.on('keyup', function() {
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
            url: tourismViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'search_tourism',
                search_term: searchTerm,
                nonce: tourismViewerData.nonce
            },
            beforeSend: function() {
                $('#tourism-list').html('<div class="tourism-empty"><p>Searching...</p></div>');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderResults(response.data);
                } else {
                    $('#tourism-list').html('<div class="tourism-empty"><p>No tourism destinations found.</p></div>');
                }
            },
            error: function() {
                $('#tourism-list').html('<div class="tourism-empty"><p>Error searching results.</p></div>');
            }
        });
    }

    function initializeTypeFilter() {
        $('#tourism-type-filter').on('change', function() {
            var type = $(this).val();
            filterByType(type);
        });
    }

    function filterByType(type) {
        $.ajax({
            url: tourismViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_tourism',
                type: type,
                nonce: tourismViewerData.nonce
            },
            beforeSend: function() {
                $('#tourism-list').html('<div class="tourism-empty"><p>Loading...</p></div>');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderResults(response.data);
                } else {
                    $('#tourism-list').html('<div class="tourism-empty"><p>No tourism destinations found.</p></div>');
                }
            },
            error: function() {
                $('#tourism-list').html('<div class="tourism-empty"><p>Error loading results.</p></div>');
            }
        });
    }

    function renderResults(results) {
        var html = '';
        
        $.each(results, function(index, item) {
            var featured = item.featured ? '<span class="featured-badge">Featured</span>' : '';
            var image = item.image ? '<div class="tourism-image"><img src="' + item.image + '" alt="' + item.title + '"></div>' : '';
            var type = item.type ? '<span class="tourism-type type-' + item.type + '">' + capitalizeFirstLetter(item.type) + '</span>' : '';
            var location = item.location ? '<span class="tourism-location">📍 ' + item.location + '</span>' : '';
            var rating = item.rating ? '<span class="tourism-rating">⭐ ' + item.rating + '/5</span>' : '';

            html += `
                <article class="tourism-item">
                    ${image}
                    <div class="tourism-content">
                        <div class="tourism-header">
                            <h3 class="tourism-title">${item.title}</h3>
                            ${featured}
                        </div>
                        <div class="tourism-meta">
                            ${type}
                            ${location}
                            ${rating}
                        </div>
                        <div class="tourism-text">${item.excerpt}</div>
                        <div class="tourism-actions">
                            <button class="btn btn-read-more" data-post-id="${item.ID}">Learn More</button>
                            <button class="btn btn-edit" data-post-id="${item.ID}">Edit</button>
                            <button class="btn btn-delete" data-post-id="${item.ID}">Delete</button>
                        </div>
                    </div>
                </article>
            `;
        });

        $('#tourism-list').html(html);
        initializeDetailButtons();
    }

    function initializeDetailButtons() {
        $(document).on('click', '.btn-read-more', function() {
            var postId = $(this).data('post-id');
            loadTourismDetail(postId);
        });

        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            var postId = $(this).data('post-id');
            loadTourismForEdit(postId);
        });

        $(document).on('click', '.btn-delete', function() {
            var postId = $(this).data('post-id');
            if (confirm('Are you sure you want to delete this tourism destination?')) {
                deleteTourism(postId);
            }
        });
    }

    function loadTourismDetail(postId) {
        $.ajax({
            url: tourismViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_tourism_detail',
                post_id: postId,
                nonce: tourismViewerData.nonce
            },
            success: function(response) {
                if (response.success) {
                    showDetailModal(response.data);
                } else {
                    alert('Error loading tourism details');
                }
            },
            error: function() {
                alert('Error loading tourism details');
            }
        });
    }

    function loadTourismForEdit(postId) {
        $.ajax({
            url: tourismViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_tourism_detail',
                post_id: postId,
                nonce: tourismViewerData.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data);
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                } else {
                    alert('Error loading tourism details');
                }
            },
            error: function() {
                alert('Error loading tourism details');
            }
        });
    }

    function populateEditForm(item) {
        $('#tourism-upload-form')[0].reset();
        $('#tourism-upload-form').data('edit-id', item.ID);
        $('#tourism_title').val(item.title);
        $('#tourism_description').val(item.content);
        $('#tourism_type').val(item.type);
        $('#tourism_location').val(item.location);
        $('#tourism_rating').val(item.rating);
        $('#tourism_featured').prop('checked', item.featured == 1);
        
        if (item.image) {
            $('#image-preview').html('<img src="' + item.image + '" alt="Preview"><p><small>Current Image</small></p>');
        }
        
        $('button[type="submit"]').text('Update Destination');
        $('#tourism-upload-form').attr('data-editing', 'true');
    }

    function showDetailModal(item) {
        var html = `
            <div class="tourism-detail-modal">
                <h2>${item.title}</h2>
                <div class="detail-meta">
                    <span class="badge">${capitalizeFirstLetter(item.type)}</span>
                    <span>📍 ${item.location}</span>
                    <span>⭐ ${item.rating}/5</span>
                </div>
                ${item.image ? '<img src="' + item.image + '" class="detail-image">' : ''}
                <div class="detail-content">${item.content}</div>
                <button class="btn btn-secondary" onclick="closeDetailModal()">Close</button>
            </div>
        `;
        
        var modal = $('<div class="tourism-modal-overlay"></div>').html(html);
        $('body').append(modal);
        
        $(document).on('click', '.tourism-modal-overlay', function(e) {
            if (e.target === this) {
                closeDetailModal();
            }
        });
    }

    function deleteTourism(postId) {
        $.ajax({
            url: tourismViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_tourism',
                post_id: postId,
                nonce: tourismViewerData.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ ' + response.data);
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.data);
                }
            },
            error: function() {
                alert('❌ Error deleting tourism destination.');
            }
        });
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    window.closeDetailModal = function() {
        $('.tourism-modal-overlay').remove();
    };

})(jQuery);
