jQuery(document).ready(function($) {
    var mediaUploader;

    $('#upload_barangay_image').click(function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Select Barangay Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#barangay_image_id').val(attachment.id);
            var imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
            $('#image-preview').html('<img src="' + imageUrl + '" style="max-width:200px;">');
        });

        mediaUploader.open();
    });

    $('#barangay-upload-form').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', $('#barangay-upload-form').data('action') || 'upload_barangay');

        $('#upload-status').html('<p>Saving...</p>').show();
        $.ajax({
            url: barangayUploadData.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#upload-status').html('<p style="color:green;">' + res.data.message + '</p>');
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    $('#upload-status').html('<p style="color:red;">Error: ' + res.data + '</p>');
                }
            }
        });
    });

    $('#clear-form').click(function() {
        $('#barangay-upload-form')[0].reset();
        $('#image-preview').empty();
        $('#barangay_image_id').val('');
        $('#barangay_post_id').remove();
        $('#barangay-upload-form').removeData('action');
    });

    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.post(barangayUploadData.ajaxurl, {
            action: 'get_barangay_detail',
            post_id: id,
            nonce: barangayUploadData.nonce
        }, function(res) {
            if (res.success) {
                var data = res.data;
                $('#barangay_name').val(data.barangay_name);
                $('#barangay_profile').val(data.barangay_profile);
                $('#barangay_origin_of_name').val(data.barangay_origin_of_name);
                $('#barangay_demographic_profile').val(data.barangay_demographic_profile);
                $('#barangay_image_id').val(data.barangay_image_id);
                $('#image-preview').html(data.barangay_image_url ? '<img src="' + data.barangay_image_url + '" style="max-width:200px;">' : '');

                if (!$('#barangay_post_id').length) {
                    $('#barangay-upload-form').append('<input type="hidden" id="barangay_post_id" name="post_id">');
                }
                $('#barangay_post_id').val(id);
                $('#barangay-upload-form').data('action', 'update_barangay');
                $('.nav-tab[href="#barangay-upload"]').trigger('click');
            }
        });
    });

    $(document).on('click', '.btn-delete', function() {
        if (confirm('Delete this barangay profile?')) {
            $.post(barangayUploadData.ajaxurl, {
                action: 'delete_barangay',
                post_id: $(this).data('id'),
                nonce: barangayUploadData.nonce
            }, function(res) {
                if (res.success) location.reload();
            });
        }
    });
});