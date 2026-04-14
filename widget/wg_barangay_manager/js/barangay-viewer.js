jQuery(document).ready(function($) {
    $('#barangay-search').on('input', function() {
        loadBarangays($(this).val());
    });

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function loadBarangays(search) {
        $.post(barangayViewerData.ajaxurl, {
            action: 'search_barangay',
            search_term: search || '',
            nonce: barangayViewerData.nonce
        }, function(res) {
            var html = '';
            if (res.success && res.data.length) {
                res.data.forEach(function(item) {
                    html += '<article class="barangay-card">';
                    if (item.image_url) {
                        html += '<div class="barangay-image"><img src="' + escapeHtml(item.image_url) + '" alt="' + escapeHtml(item.name) + '"></div>';
                    }
                    html += '<div class="barangay-info">';
                    html += '<h3>' + escapeHtml(item.name) + '</h3>';
                    html += '<p>' + escapeHtml(item.barangay_profile || '').substring(0, 180) + '</p>';
                    if (item.origin_of_name) {
                        html += '<p><strong>Origin of Name:</strong> ' + escapeHtml(item.origin_of_name).substring(0, 180) + '</p>';
                    }
                    if (item.demographic_profile) {
                        html += '<p><strong>Demographic Profile:</strong> ' + escapeHtml(item.demographic_profile).substring(0, 180) + '</p>';
                    }
                    html += '</div></article>';
                });
            } else {
                html = '<p>No barangay profiles found.</p>';
            }
            $('#barangay-list').html(html);
        });
    }
});