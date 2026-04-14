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
            $('#image-preview').html('<img src="' + attachment.sizes.medium.url + '" style="max-width:200px;">');
        });
        mediaUploader.open();
    });

    $('#barangay-upload-form').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'upload_barangay');
        
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
                    setTimeout(() => location.reload(), 1500);
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
    });

    // Edit functionality - populate form
    $('.btn-edit').click(function() {
        var id = $(this).data('id');
        $.post(barangayUploadData.ajaxurl, {
            action: 'get_barangay_detail',
            post_id: id,
            nonce: barangayUploadData.nonce
        }, function(res) {
            if (res.success) {
                var data = res.data;
                $('#barangay_name').val(data.post_title);
                $('#barangay_description').val(data.post_content);
                $('#barangay_demographics').val(data.barangay_demographics);
                $('#barangay_patron_saint').val(data.barangay_patron_saint);
                $('#barangay_topography').val(data.barangay_topography);
                $('#barangay_location').val(data.barangay_location);
                $('#barangay_population').val(data.barangay_population);
                $('#barangay_image_id').val(data.featured_image_id);
                $('#image-preview').html(data.featured_image_url ? '<img src="' + data.featured_image_url + '" style="max-width:200px;">' : '');
                if (data.barangay_featured) $('#barangay_featured').prop('checked', true);
                formData.action = 'update_barangay';
                formData.append('post_id', id);
            }
        });
    });

    $('.btn-delete').click(function() {
        if (confirm('Delete?')) {
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
