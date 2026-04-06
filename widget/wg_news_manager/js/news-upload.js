/**
 * News Upload Script
 */

jQuery(document).ready(function ($) {
    let uploadedImageId = 0;

    // Set default date to today
    const today = new Date().toISOString().split("T")[0];
    $("#news_date").val(today);

    // Media upload button
    $("#upload_news_image").on("click", function (e) {
        e.preventDefault();

        if (wp.media === undefined) {
            alert("Media uploader not available");
            return;
        }

        const frame = wp.media({
            title: "Select Image",
            button: {
                text: "Select",
            },
            multiple: false,
            library: {
                type: "image",
            },
        });

        frame.on("select", function () {
            const attachment = frame.state().get("selection").first().toJSON();
            uploadedImageId = attachment.id;
            $("#news_image_id").val(attachment.id);

            // Display preview
            const preview = `<div class="image-preview-item">
                <img src="${attachment.sizes.thumbnail.url}" alt="${attachment.title}">
                <button type="button" class="image-preview-remove">×</button>
            </div>`;

            $("#image-preview").html(preview);

            // Remove preview on button click
            $(".image-preview-remove").on("click", function (e) {
                e.preventDefault();
                uploadedImageId = 0;
                $("#news_image_id").val("");
                $("#image-preview").html("");
            });
        });

        frame.open();
    });

    // Form submission
    $("#news-upload-form").on("submit", function (e) {
        e.preventDefault();

        const formData = {
            action: "upload_news",
            nonce: newsUploadData.nonce,
            news_title: $("#news_title").val(),
            news_content: $("#news_content").val(),
            news_category: $("#news_category").val(),
            news_priority: $("#news_priority").val(),
            news_image_id: $("#news_image_id").val(),
            news_date: $("#news_date").val(),
            news_featured: $("#news_featured").is(":checked") ? 1 : 0,
            news_active: $("#news_active").is(":checked") ? 1 : 0,
        };

        // Show loading status
        showStatus("Uploading news...", "loading");

        $.ajax({
            type: "POST",
            url: newsUploadData.ajaxurl,
            data: formData,
            success: function (response) {
                if (response.success) {
                    showStatus(response.data.message, "success");
                    $("#news-upload-form")[0].reset();
                    $("#image-preview").html("");
                    uploadedImageId = 0;
                    $("#news_date").val(today);

                    // Reload viewer if exists
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    showStatus(
                        response.data || "An error occurred",
                        "error"
                    );
                }
            },
            error: function () {
                showStatus("Network error. Please try again.", "error");
            },
        });
    });

    // Show status message
    function showStatus(message, type) {
        const statusDiv = $("#upload-status");
        statusDiv.removeClass("success error loading");
        statusDiv.addClass(type);
        statusDiv.html(message);
        statusDiv.show();

        if (type === "success") {
            setTimeout(function () {
                statusDiv.fadeOut();
            }, 3000);
        }
    }

    // Character counter for content
    $("#news_content").on("input", function () {
        const count = $(this).val().length;
        const maxLength = 5000;

        if (count > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
        }
    });
});
