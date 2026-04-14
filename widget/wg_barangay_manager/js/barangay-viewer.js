jQuery(document).ready(function($) {
    $('#barangay-search, #barangay-filter').on('input change', function() {
        loadBarangays();
    });

    $('.btn-edit').on('click', function() {
        // Populate edit form in modal or switch tab
        var id = $(this).data('id');
        // AJAX get detail and populate upload form
        $.post(barangayViewerData.ajaxurl, {
            action: 'get_barangay_detail',
            post_id: id,
            nonce: barangayViewerData.nonce
        }, function(res) {
            if (res.success) {
                // Assume upload tab/form handles populate, or show modal
                $('[data-tab="barangay-upload"]').show();
                // Trigger edit logic in upload.js
            }
        });
    });

    $('.btn-delete').on('click', function() {
        if (confirm('Delete barangay profile?')) {
            $.post(barangayViewerData.ajaxurl, {
                action: 'delete_barangay',
                post_id: $(this).data('id'),
                nonce: barangayViewerData.nonce
            }).done(function() {
                location.reload();
            });
        }
    });

    function loadBarangays(search = '', filter = '') {
        $.post(barangayViewerData.ajaxurl, {
            action: 'search_barangay',
            search_term: search,
            featured: filter,
            nonce: barangayViewerData.nonce
        }, function(res) {
            var html = '';
            if (res.success && res.data.length) {
                res.data.forEach(function(item) {
                    html += `<div class="barangay-card">
                        <img src="${item.image || ''}" alt="${item.title}">
                        <h3>${item.title}</h3>
                        <p>${item.excerpt}</p>
                        <button class="btn-edit" data-id="${item.ID}">Edit</button>
                        <button class="btn-delete" data-id="${item.ID}">Delete</button>
                    </div>`;
                });
            } else {
                html = '<p>No profiles found.</p>';
            }
            $('#barangay-list').html(html);
        });
    }
});

