/**
 * Municipal ordinance upload script
 */

jQuery(document).ready(function ($) {
    let uploadedPdfId = 0;

    $('#upload_municipal_ordinance_pdf').on('click', function (e) {
        e.preventDefault();

        if (wp.media === undefined) {
            alert('Media uploader not available');
            return;
        }

        const frame = wp.media({
            title: 'Select Municipal Ordinance PDF',
            button: {
                text: 'Select',
            },
            multiple: false,
            library: {
                type: 'application/pdf',
            },
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            uploadedPdfId = attachment.id;
            $('#municipal_ordinance_pdf_id').val(attachment.id);

            const preview = `<div class="file-preview-item">
                <div class="file-icon">PDF</div>
                <div class="file-info">
                    <div class="file-name">${attachment.filename}</div>
                    <div class="file-size">${attachment.filesizeformatted || 'Unknown size'}</div>
                </div>
                <button type="button" class="file-preview-remove">Remove</button>
            </div>`;

            $('#municipal-ordinance-file-preview').html(preview).addClass('active');

            $('.file-preview-remove').on('click', function (event) {
                event.preventDefault();
                uploadedPdfId = 0;
                $('#municipal_ordinance_pdf_id').val('');
                $('#municipal-ordinance-file-preview').html('').removeClass('active');
            });
        });

        frame.open();
    });

    $('#municipal-ordinance-upload-form').on('submit', function (e) {
        e.preventDefault();

        if ($('#municipal_ordinance_pdf_id').val() === '') {
            showStatus('Please select a PDF file', 'error');
            return;
        }

        const formData = {
            action: 'upload_municipal_ordinance',
            nonce: municipalOrdinanceUploadData.nonce,
            municipal_ordinance_title: $('#municipal_ordinance_title').val(),
            municipal_ordinance_pdf_id: $('#municipal_ordinance_pdf_id').val(),
        };

        showStatus('Uploading municipal ordinance...', 'loading');

        $.ajax({
            type: 'POST',
            url: municipalOrdinanceUploadData.ajaxurl,
            data: formData,
            success: function (response) {
                if (response.success) {
                    showStatus(response.data.message, 'success');
                    $('#municipal-ordinance-upload-form')[0].reset();
                    $('#municipal-ordinance-file-preview').html('').removeClass('active');
                    uploadedPdfId = 0;

                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                } else {
                    showStatus(response.data || 'An error occurred', 'error');
                }
            },
            error: function () {
                showStatus('Network error. Please try again.', 'error');
            },
        });
    });

    $('#municipal-ordinance-upload-form').on('reset', function () {
        uploadedPdfId = 0;
        $('#municipal_ordinance_pdf_id').val('');
        $('#municipal-ordinance-file-preview').html('').removeClass('active');
    });

    function showStatus(message, type) {
        const statusDiv = $('#municipal-ordinance-upload-status');
        statusDiv.removeClass('success error loading').addClass(type).html(message).show();

        if (type === 'success') {
            setTimeout(function () {
                statusDiv.fadeOut();
            }, 3000);
        }
    }
});
