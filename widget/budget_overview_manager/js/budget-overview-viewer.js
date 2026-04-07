/**
 * Budget overview viewer script
 */

jQuery(document).ready(function ($) {
    $("#budget-overview-search").on("keyup", debounce(function () {
        const searchTerm = $(this).val();

        if (searchTerm.length < 2) {
            location.reload();
            return;
        }

        $.ajax({
            type: "POST",
            url: budgetOverviewViewerData.ajaxurl,
            data: {
                action: "search_budget_overviews",
                nonce: budgetOverviewViewerData.nonce,
                search_term: searchTerm,
            },
            success: function (response) {
                if (response.success) {
                    renderBudgetList(response.data);
                }
            },
        });
    }, 300));

    $(document).on("click", ".btn-delete", function () {
        const postId = $(this).data("post-id");
        if (!budgetOverviewViewerData.canDelete) {
            return;
        }

        if (confirm("Are you sure you want to delete this budget overview?")) {
            $.ajax({
                type: "POST",
                url: budgetOverviewViewerData.ajaxurl,
                data: {
                    action: "delete_budget_overview",
                    nonce: budgetOverviewViewerData.nonce,
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

    function renderBudgetList(data) {
        let html = "";

        if (data.length === 0) {
            html = '<div class="budget-overview-empty"><p>No budget overviews found.</p></div>';
        } else {
            data.forEach(function (item) {
                const downloadBtn = item.pdf_url
                    ? `<a href="${item.pdf_url}" class="btn btn-download" download="${item.pdf_title || item.title}">Download PDF</a>`
                    : '<span class="btn btn-download disabled">No PDF</span>';
                const deleteBtn = budgetOverviewViewerData.canDelete
                    ? `<button class="btn btn-delete" data-post-id="${item.ID}">Delete</button>`
                    : "";

                html += `
                    <div class="budget-overview-item">
                        <div class="budget-overview-content">
                            <h3 class="budget-overview-title">${item.title}</h3>
                            <div class="budget-overview-meta">
                                <span class="budget-overview-year">Year: ${item.year || "N/A"}</span>
                                <span class="budget-overview-ordinance">Ordinance No.: ${item.ordinance_no || "N/A"}</span>
                                <span class="budget-overview-total">Total Budget: ${item.total_budget || "N/A"}</span>
                                <span class="budget-overview-date">${item.date}</span>
                            </div>
                        </div>
                        <div class="budget-overview-actions">
                            ${downloadBtn}
                            ${deleteBtn}
                        </div>
                    </div>
                `;
            });
        }

        $("#budget-overview-list").html(html);
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
