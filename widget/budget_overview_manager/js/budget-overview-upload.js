/**
 * Budget overview upload script
 */

jQuery(document).ready(function ($) {
    $("#upload_budget_overview_pdf").on("click", function (e) {
        e.preventDefault();

        if (wp.media === undefined) {
            alert("Media uploader not available");
            return;
        }

        const frame = wp.media({
            title: "Select Budget Overview PDF",
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
            $("#budget_overview_pdf_id").val(attachment.id);

            const preview = `<div class="file-preview-item">
                <div class="file-icon">PDF</div>
                <div class="file-info">
                    <div class="file-name">${attachment.filename}</div>
                    <div class="file-size">${attachment.filesizeformatted || "Unknown size"}</div>
                </div>
                <button type="button" class="file-preview-remove">Remove</button>
            </div>`;

            $("#budget-overview-file-preview").html(preview).addClass("active");

            $(".file-preview-remove").on("click", function (event) {
                event.preventDefault();
                $("#budget_overview_pdf_id").val("");
                $("#budget-overview-file-preview").html("").removeClass("active");
            });
        });

        frame.open();
    });

    $("#budget-overview-upload-form").on("submit", function (e) {
        e.preventDefault();

        if ($("#budget_overview_pdf_id").val() === "") {
            showStatus("Please select a PDF file", "error");
            return;
        }

        const formData = {
            action: "upload_budget_overview",
            nonce: budgetOverviewUploadData.nonce,
            budget_overview_year: $("#budget_overview_year").val(),
            budget_overview_ordinance_no: $("#budget_overview_ordinance_no").val(),
            budget_overview_total_budget: $("#budget_overview_total_budget").val(),
            budget_overview_pdf_id: $("#budget_overview_pdf_id").val(),
        };

        showStatus("Uploading budget overview...", "loading");

        $.ajax({
            type: "POST",
            url: budgetOverviewUploadData.ajaxurl,
            data: formData,
            success: function (response) {
                if (response.success) {
                    showStatus(response.data.message, "success");
                    $("#budget-overview-upload-form")[0].reset();
                    $("#budget-overview-file-preview").html("").removeClass("active");

                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                } else {
                    showStatus(response.data || "An error occurred", "error");
                }
            },
            error: function () {
                showStatus("Network error. Please try again.", "error");
            },
        });
    });

    function showStatus(message, type) {
        const statusDiv = $("#budget-overview-upload-status");
        statusDiv.removeClass("success error loading").addClass(type).html(message).show();

        if (type === "success") {
            setTimeout(function () {
                statusDiv.fadeOut();
            }, 3000);
        }
    }
});
