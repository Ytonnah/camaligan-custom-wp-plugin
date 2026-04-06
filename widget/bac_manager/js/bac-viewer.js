/**
 * BAC Viewer Script
 */

jQuery(document).ready(function ($) {
    // Search functionality
    $("#bac-search").on("keyup", debounce(function () {
        const searchTerm = $(this).val();

        if (searchTerm.length < 2) {
            location.reload();
            return;
        }

        $.ajax({
            type: "POST",
            url: bacViewerData.ajaxurl,
            data: {
                action: "search_bac",
                nonce: bacViewerData.nonce,
                search_term: searchTerm,
            },
            success: function (response) {
                if (response.success) {
                    renderBacList(response.data);
                }
            },
        });
    }, 300));

    // Delete button
    $(document).on("click", ".btn-delete", function () {
        const postId = $(this).data("post-id");
        if (confirm("Are you sure you want to delete this BAC item?")) {
            $.ajax({
                type: "POST",
                url: bacViewerData.ajaxurl,
                data: {
                    action: "delete_bac",
                    nonce: bacViewerData.nonce,
                    post_id: postId,
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || "Delete failed");
                    }
                },
            });
        }
    });

    // Render BAC list from data
    function renderBacList(data) {
        let html = "";

        if (data.length === 0) {
            html = '<div class="bac-empty"><p>No BAC documents found.</p></div>';
        } else {
            data.forEach(function (item) {
                const downloadBtn = item.pdf_url
                    ? `<a href="${item.pdf_url}" class="btn btn-download" download="${item.pdf_title || item.title}">📥 Download PDF</a>`
                    : `<span class="btn btn-download disabled">No PDF</span>`;
                const deleteBtn = `<button class="btn btn-delete" data-post-id="${item.ID}">🗑️ Delete</button>`;

                html += `
                    <div class="bac-item">
                        <div class="bac-content">
                            <h3 class="bac-title">${item.title}</h3>
                            <div class="bac-meta">
                                <span class="bac-date">${item.date}</span>
                            </div>
                        </div>
                        <div class="bac-actions">
                            ${downloadBtn}
                            ${deleteBtn}
                        </div>
                    </div>
                `;
            });
        }

        $("#bac-list").html(html);
    }

    // Debounce function
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
