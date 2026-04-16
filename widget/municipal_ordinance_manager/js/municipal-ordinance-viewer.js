/**
 * Municipal ordinance viewer script
 */

jQuery(document).ready(function ($) {
    $('#municipal-ordinance-search').on('keyup', debounce(function () {
        const searchTerm = $(this).val();

        if (searchTerm.length < 2) {
            location.reload();
            return;
        }

        $.ajax({
            type: 'POST',
            url: municipalOrdinanceViewerData.ajaxurl,
            data: {
                action: 'search_municipal_ordinances',
                nonce: municipalOrdinanceViewerData.nonce,
                search_term: searchTerm,
            },
            success: function (response) {
                if (response.success) {
                    renderOrdinanceList(response.data);
                }
            },
        });
    }, 300));

    $(document).on('click', '.btn-delete', function () {
        const postId = $(this).data('post-id');
        if (!municipalOrdinanceViewerData.canDelete) {
            return;
        }

        if (confirm('Are you sure you want to delete this municipal ordinance?')) {
            $.ajax({
                type: 'POST',
                url: municipalOrdinanceViewerData.ajaxurl,
                data: {
                    action: 'delete_municipal_ordinance',
                    nonce: municipalOrdinanceViewerData.nonce,
                    post_id: postId,
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || 'Delete failed');
                    }
                },
            });
        }
    });

    function renderOrdinanceList(data) {
        let html = '';

        if (data.length === 0) {
            html = '<div class="municipal-ordinance-empty"><p>No municipal ordinances found.</p></div>';
        } else {
            data.forEach(function (item) {
                const downloadBtn = item.pdf_url
                    ? `<a href="${item.pdf_url}" class="btn btn-download" download="${item.pdf_title || item.title}">Download PDF</a>`
                    : '<span class="btn btn-download disabled">No PDF</span>';
                const deleteBtn = municipalOrdinanceViewerData.canDelete
                    ? `<button class="btn btn-delete" data-post-id="${item.ID}">Delete</button>`
                    : '';

                html += `
                    <div class="municipal-ordinance-item">
                        <div class="municipal-ordinance-content">
                            <h3 class="municipal-ordinance-title">${item.title}</h3>
                            <div class="municipal-ordinance-meta">
                                <span class="municipal-ordinance-category">${item.category || 'Uncategorized'}</span>
                                <span class="municipal-ordinance-date">${item.date}</span>
                            </div>
                        </div>
                        <div class="municipal-ordinance-actions">
                            ${downloadBtn}
                            ${deleteBtn}
                        </div>
                    </div>
                `;
            });
        }

        $('#municipal-ordinance-list').html(html);
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
