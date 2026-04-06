/**
 * News Viewer Script
 */

jQuery(document).ready(function ($) {
    // Search functionality
    $("#news-search").on("keyup", debounce(function () {
        const searchTerm = $(this).val();

        if (searchTerm.length < 2) {
            location.reload();
            return;
        }

        $.ajax({
            type: "POST",
            url: newsViewerData.ajaxurl,
            data: {
                action: "search_news",
                nonce: newsViewerData.nonce,
                search_term: searchTerm,
            },
            success: function (response) {
                if (response.success) {
                    renderNewsList(response.data);
                }
            },
        });
    }, 300));

    // Filter functionality
    $("#news-category-filter").on("change", function () {
        const category = $(this).val();

        $.ajax({
            type: "POST",
            url: newsViewerData.ajaxurl,
            data: {
                action: "filter_news",
                nonce: newsViewerData.nonce,
                category: category,
                priority: "",
            },
            success: function (response) {
                if (response.success) {
                    renderNewsList(response.data);
                }
            },
        });
    });

    // Read more button
    $(document).on("click", ".btn-read-more", function () {
        const postId = $(this).data("post-id");
        // Implement modal or redirect to single news page
        alert("Reading news ID: " + postId);
    });

    // Edit button
    $(document).on("click", ".btn-edit", function () {
        const postId = $(this).data("post-id");
        // Load edit form via AJAX or redirect to edit page
        alert("Edit news ID: " + postId);
    });

    // Delete button
    $(document).on("click", ".btn-delete", function () {
        const postId = $(this).data("post-id");
        if (
            confirm("Are you sure you want to delete this news item?")
        ) {
            $.ajax({
                type: "POST",
                url: newsViewerData.ajaxurl,
                data: {
                    action: "delete_news",
                    nonce: newsViewerData.nonce,
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

    // Render news list from data
    function renderNewsList(data) {
        let html = "";

        if (data.length === 0) {
            html = '<div class="news-empty"><p>No news items found.</p></div>';
        } else {
            data.forEach(function (item) {
                const imageHtml = item.image
                    ? `<div class="news-image"><img src="${item.image}" alt="${item.title}"></div>`
                    : "";
                const categoryBadge = item.category
                    ? `<span class="news-category category-${item.category}">${
                          item.category.charAt(0).toUpperCase() +
                          item.category.slice(1)
                      }</span>`
                    : "";
                const priorityBadge = `<span class="news-priority priority-${item.priority}">${
                    item.priority.charAt(0).toUpperCase() + item.priority.slice(1)
                }</span>`;

                html += `
                    <article class="news-item priority-${item.priority}">
                        ${imageHtml}
                        <div class="news-content">
                            <div class="news-header">
                                <h3 class="news-title">${item.title}</h3>
                            </div>
                            <div class="news-meta">
                                ${categoryBadge}
                                ${priorityBadge}
                                <span class="news-date">${item.date}</span>
                            </div>
                            <div class="news-text">${item.excerpt}</div>
                            <div class="news-actions">
                                <button class="btn btn-read-more" data-post-id="${item.ID}">Read More</button>
                                <button class="btn btn-edit" data-post-id="${item.ID}">Edit</button>
                                <button class="btn btn-delete" data-post-id="${item.ID}">Delete</button>
                            </div>
                        </div>
                    </article>
                `;
            });
        }

        $("#news-list").html(html);
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
