/**
 * BAC Upload Script
 */

jQuery(document).ready(function ($) {
    let uploadedPdfId = 0;

    // Media upload button
    $("#upload_bac_pdf").on("click", function (e) {
        e.preventDefault();

        if (wp.media === undefined) {
            alert("Media uploader not available");
            return;
        }

        const frame = wp.media({
            title: "Select PDF",
            button: {
                text: "Select",
            },
            multiple: false,
            library: {
                type: "application/pdf",
            },
        });

        frame.on("select", function () {
            const attachment = frame.state().get("selection").first().toJSON();
            uploadedPdfId = attachment.id;
            $("#bac_pdf_id").val(attachment.id);

            // Display preview
            const fileSize = (attachment.filesizeformatted || "Unknown size");
            const preview = `<div class="file-preview-item">
                <div class="file-icon">📄</div>
                <div class="file-info">
                    <div class="file-name">${attachment.filename}</div>
                    <div class="file-size">${fileSize}</div>
                </div>
                <button type="button" class="file-preview-remove">Remove</button>
            </div>`;

            $("#file-preview").html(preview).addClass("active");

            // Remove preview on button click
            $(".file-preview-remove").on("click", function (e) {
                e.preventDefault();
                uploadedPdfId = 0;
                $("#bac_pdf_id").val("");
                $("#file-preview").html("").removeClass("active");
            });
        });

        frame.open();
    });

    // Form submission
    $("#bac-upload-form").on("submit", function (e) {
        e.preventDefault();

        if ($("#bac_pdf_id").val() === "") {
            showStatus("Please select a PDF file", "error");
            return;
        }

        const formData = {
            action: "upload_bac",
            nonce: bacUploadData.nonce,
            bac_title: $("#bac_title").val(),
            bac_pdf_id: $("#bac_pdf_id").val(),
        };

        // Show loading status
        showStatus("Uploading BAC...", "loading");

        $.ajax({
            type: "POST",
            url: bacUploadData.ajaxurl,
            data: formData,
            success: function (response) {
                if (response.success) {
                    showStatus(response.data.message, "success");
                    $("#bac-upload-form")[0].reset();
                    $("#file-preview").html("").removeClass("active");
                    uploadedPdfId = 0;

                    // Reload page after success
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
});
