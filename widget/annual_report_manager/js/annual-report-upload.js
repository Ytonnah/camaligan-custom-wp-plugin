/**
 * Annual report upload script
 */

jQuery(document).ready(function ($) {
    let uploadedPdfId = 0;

    $("#upload_annual_report_pdf").on("click", function (e) {
        e.preventDefault();

        if (wp.media === undefined) {
            alert("Media uploader not available");
            return;
        }

        const frame = wp.media({
            title: "Select Annual Report PDF",
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
            $("#annual_report_pdf_id").val(attachment.id);

            const preview = `<div class="file-preview-item">
                <div class="file-icon">PDF</div>
                <div class="file-info">
                    <div class="file-name">${attachment.filename}</div>
                    <div class="file-size">${attachment.filesizeformatted || "Unknown size"}</div>
                </div>
                <button type="button" class="file-preview-remove">Remove</button>
            </div>`;

            $("#annual-report-file-preview").html(preview).addClass("active");

            $(".file-preview-remove").on("click", function (event) {
                event.preventDefault();
                uploadedPdfId = 0;
                $("#annual_report_pdf_id").val("");
                $("#annual-report-file-preview").html("").removeClass("active");
            });
        });

        frame.open();
    });

    $("#annual-report-upload-form").on("submit", function (e) {
        e.preventDefault();

        if ($("#annual_report_pdf_id").val() === "") {
            showStatus("Please select a PDF file", "error");
            return;
        }

        const formData = {
            action: "upload_annual_report",
            nonce: annualReportUploadData.nonce,
            annual_report_title: $("#annual_report_title").val(),
            annual_report_year: $("#annual_report_year").val(),
            annual_report_pdf_id: $("#annual_report_pdf_id").val(),
        };

        showStatus("Uploading annual report...", "loading");

        $.ajax({
            type: "POST",
            url: annualReportUploadData.ajaxurl,
            data: formData,
            success: function (response) {
                if (response.success) {
                    showStatus(response.data.message, "success");
                    $("#annual-report-upload-form")[0].reset();
                    $("#annual-report-file-preview").html("").removeClass("active");
                    uploadedPdfId = 0;

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
        const statusDiv = $("#annual-report-upload-status");
        statusDiv.removeClass("success error loading").addClass(type).html(message).show();

        if (type === "success") {
            setTimeout(function () {
                statusDiv.fadeOut();
            }, 3000);
        }
    }
});
