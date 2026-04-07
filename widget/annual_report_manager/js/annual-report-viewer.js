/**
 * Annual report viewer script
 */

jQuery(document).ready(function ($) {
    $("#annual-report-search").on("keyup", debounce(function () {
        const searchTerm = $(this).val();

        if (searchTerm.length < 2) {
            location.reload();
            return;
        }

        $.ajax({
            type: "POST",
            url: annualReportViewerData.ajaxurl,
            data: {
                action: "search_annual_reports",
                nonce: annualReportViewerData.nonce,
                search_term: searchTerm,
            },
            success: function (response) {
                if (response.success) {
                    renderReportList(response.data);
                }
            },
        });
    }, 300));

    $(document).on("click", ".btn-delete", function () {
        const postId = $(this).data("post-id");
        if (!annualReportViewerData.canDelete) {
            return;
        }

        if (confirm("Are you sure you want to delete this annual report?")) {
            $.ajax({
                type: "POST",
                url: annualReportViewerData.ajaxurl,
                data: {
                    action: "delete_annual_report",
                    nonce: annualReportViewerData.nonce,
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

    function renderReportList(data) {
        let html = "";

        if (data.length === 0) {
            html = '<div class="annual-report-empty"><p>No annual reports found.</p></div>';
        } else {
            data.forEach(function (item) {
                const downloadBtn = item.pdf_url
                    ? `<a href="${item.pdf_url}" class="btn btn-download" download="${item.pdf_title || item.title}">Download PDF</a>`
                    : '<span class="btn btn-download disabled">No PDF</span>';
                const deleteBtn = annualReportViewerData.canDelete
                    ? `<button class="btn btn-delete" data-post-id="${item.ID}">Delete</button>`
                    : "";

                html += `
                    <div class="annual-report-item">
                        <div class="annual-report-content">
                            <h3 class="annual-report-title">${item.title}</h3>
                            <div class="annual-report-meta">
                                <span class="annual-report-year">Year: ${item.year || "N/A"}</span>
                                <span class="annual-report-date">${item.date}</span>
                            </div>
                        </div>
                        <div class="annual-report-actions">
                            ${downloadBtn}
                            ${deleteBtn}
                        </div>
                    </div>
                `;
            });
        }

        $("#annual-report-list").html(html);
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
