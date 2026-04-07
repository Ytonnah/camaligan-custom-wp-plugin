// Beneficiaries Viewer JavaScript

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeSearchFilter();
        initializeTypeFilter();
        initializeStatusFilter();
        initializeDetailButtons();
    });

    function initializeSearchFilter() {
        var searchInput = $('#beneficiaries-search');
        var searchTimeout;

        searchInput.on('keyup', function() {
            clearTimeout(searchTimeout);
            var searchTerm = $(this).val();

            searchTimeout = setTimeout(function() {
                if (searchTerm.length > 0) {
                    performSearch(searchTerm);
                } else {
                    location.reload();
                }
            }, 500);
        });
    }

    function performSearch(searchTerm) {
        $.ajax({
            url: beneficiariesViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'search_beneficiaries',
                search_term: searchTerm,
                nonce: beneficiariesViewerData.nonce
            },
            beforeSend: function() {
                $('#beneficiaries-list').html('<div class="beneficiaries-empty"><p>Searching...</p></div>');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderResults(response.data);
                } else {
                    $('#beneficiaries-list').html('<div class="beneficiaries-empty"><p>No beneficiaries found.</p></div>');
                }
            },
            error: function() {
                $('#beneficiaries-list').html('<div class="beneficiaries-empty"><p>Error searching.</p></div>');
            }
        });
    }

    function initializeTypeFilter() {
        $('#beneficiaries-type-filter').on('change', function() {
            applyFilters();
        });
    }

    function initializeStatusFilter() {
        $('#beneficiaries-status-filter').on('change', function() {
            applyFilters();
        });
    }

    function applyFilters() {
        var type = $('#beneficiaries-type-filter').val();
        var status = $('#beneficiaries-status-filter').val();

        $.ajax({
            url: beneficiariesViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_beneficiaries',
                type: type,
                status: status,
                nonce: beneficiariesViewerData.nonce
            },
            beforeSend: function() {
                $('#beneficiaries-list').html('<div class="beneficiaries-empty"><p>Filtering...</p></div>');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderResults(response.data);
                } else {
                    $('#beneficiaries-list').html('<div class="beneficiaries-empty"><p>No beneficiaries match your filters.</p></div>');
                }
                initializeDetailButtons();
            },
            error: function() {
                $('#beneficiaries-list').html('<div class="beneficiaries-empty"><p>Error filtering results.</p></div>');
            }
        });
    }

    function renderResults(beneficiaries) {
        var html = '';

        beneficiaries.forEach(function(beneficiary) {
            var imageSrc = beneficiary.image || '';
            var imageHtml = imageSrc 
                ? '<img src="' + imageSrc + '" alt="' + beneficiary.name + '" class="beneficiary-image">'
                : '<div class="beneficiary-image placeholder">No Photo</div>';

            var statusClass = beneficiary.status.toLowerCase();

            html += '<div class="beneficiary-card" data-post-id="' + beneficiary.id + '">';
            html += imageHtml;
            html += '<div class="beneficiary-badges">';
            html += '<span class="badge badge-type">' + escapeHtml(beneficiary.type) + '</span>';
            html += '<span class="badge badge-status ' + statusClass + '">' + escapeHtml(beneficiary.status) + '</span>';
            html += '</div>';
            html += '<div class="beneficiary-info">';
            html += '<h3>' + escapeHtml(beneficiary.name) + '</h3>';
            html += '<p class="beneficiary-barangay">📍 ' + escapeHtml(beneficiary.barangay) + '</p>';
            html += '<p class="beneficiary-program">' + escapeHtml(beneficiary.program) + '</p>';
            html += '</div>';
            html += '<div class="beneficiary-actions">';
            html += '<button class="btn btn-primary btn-sm view-beneficiary-btn" data-post-id="' + beneficiary.id + '">View</button>';
            html += '<button class="btn btn-secondary btn-sm edit-beneficiary-btn" data-post-id="' + beneficiary.id + '">Edit</button>';
            html += '<button class="btn btn-danger btn-sm delete-beneficiary-btn" data-post-id="' + beneficiary.id + '">Delete</button>';
            html += '</div>';
            html += '</div>';
        });

        $('#beneficiaries-list').html(html);
    }

    function initializeDetailButtons() {
        $(document).on('click', '.view-beneficiary-btn', function() {
            var postId = $(this).data('post-id');
            loadBeneficiaryDetail(postId);
        });

        $(document).on('click', '.edit-beneficiary-btn', function(e) {
            e.preventDefault();
            var postId = $(this).data('post-id');
            loadBeneficiaryForEdit(postId);
        });

        $(document).on('click', '.delete-beneficiary-btn', function() {
            var postId = $(this).data('post-id');
            if (confirm('Are you sure you want to delete this beneficiary?')) {
                deleteBeneficiary(postId);
            }
        });
    }

    function loadBeneficiaryDetail(postId) {
        $.ajax({
            url: beneficiariesViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_beneficiary_detail',
                post_id: postId,
                nonce: beneficiariesViewerData.nonce
            },
            success: function(response) {
                if (response.success) {
                    showDetailModal(response.data);
                } else {
                    alert('Error loading beneficiary details');
                }
            },
            error: function() {
                alert('Error loading beneficiary details');
            }
        });
    }

    function loadBeneficiaryForEdit(postId) {
        $.ajax({
            url: beneficiariesViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_beneficiary_detail',
                post_id: postId,
                nonce: beneficiariesViewerData.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data);
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                    $('.tab-btn').eq(0).click();
                } else {
                    alert('Error loading beneficiary details');
                }
            },
            error: function() {
                alert('Error loading beneficiary details');
            }
        });
    }

    function populateEditForm(item) {
        $('#beneficiaries-upload-form')[0].reset();
        $('#beneficiaries-upload-form').data('edit-id', item.ID);
        $('#beneficiary_name').val(item.name);
        $('#beneficiary_description').val(item.description);
        $('#beneficiary_barangay').val(item.barangay);
        $('#beneficiary_type').val(item.type);
        $('#beneficiary_contact').val(item.contact);
        $('#beneficiary_program').val(item.program);
        $('#beneficiary_date').val(item.date);
        $('#beneficiary_status').val(item.status);
        $('#beneficiary_image_id').val(item.image_id);
        
        if (item.image) {
            $('#image-preview').html('<img src="' + item.image + '" alt="Preview"><p style="font-size:11px;margin-top:5px;"><small>Current Photo</small></p>');
        }
        
        $('button[type="submit"]').text('Update Beneficiary');
        $('#beneficiaries-upload-form').attr('data-editing', 'true');
    }

    function showDetailModal(item) {
        var statusClass = item.status.toLowerCase();
        var imageHtml = item.image 
            ? '<img src="' + item.image + '" alt="' + item.name + '" class="detail-image">'
            : '<div class="detail-image placeholder">No Photo</div>';

        var html = imageHtml;
        html += '<div class="detail-header-info">';
        html += '<h2>' + escapeHtml(item.name) + '</h2>';
        html += '<div class="detail-badges">';
        html += '<span class="badge badge-type">' + escapeHtml(item.type) + '</span>';
        html += '<span class="badge badge-status ' + statusClass + '">' + escapeHtml(item.status) + '</span>';
        html += '</div></div>';

        var modal = '<span class="modal-close">&times;</span>';
        modal += '<div class="detail-header">' + html + '</div>';
        modal += '<div class="detail-body">';
        modal += '<div class="detail-section"><h3>Information</h3>';
        modal += '<div class="detail-grid">';
        modal += '<div class="detail-item"><label>Barangay:</label><p>' + escapeHtml(item.barangay) + '</p></div>';
        modal += '<div class="detail-item"><label>Type:</label><p>' + escapeHtml(item.type) + '</p></div>';
        modal += '<div class="detail-item"><label>Program/Assistance:</label><p>' + escapeHtml(item.program) + '</p></div>';
        modal += '<div class="detail-item"><label>Status:</label><p>' + escapeHtml(item.status) + '</p></div>';
        modal += '<div class="detail-item"><label>Contact:</label><p>' + escapeHtml(item.contact || 'N/A') + '</p></div>';
        modal += '<div class="detail-item"><label>Date Registered:</label><p>' + escapeHtml(item.date) + '</p></div>';
        modal += '</div></div>';
        modal += '<div class="detail-section"><h3>Description</h3>';
        modal += '<div class="detail-description">' + item.description + '</div></div>';
        modal += '</div>';
        modal += '<div class="detail-actions">';
        modal += '<button class="btn btn-primary btn-sm" data-post-id="' + item.ID + '" onclick="editBeneficiaryFromModal(' + item.ID + ')">Edit</button>';
        modal += '<button class="btn btn-danger btn-sm" data-post-id="' + item.ID + '" onclick="deleteBeneficiaryFromModal(' + item.ID + ')">Delete</button>';
        modal += '<button class="btn btn-secondary btn-sm" onclick="closeBeneficiaryModal()">Close</button>';
        modal += '</div>';

        $('#beneficiary-detail-modal').html('<div class="modal-content beneficiary-modal-content">' + modal + '</div>').show();
    }

    function deleteBeneficiary(postId) {
        $.ajax({
            url: beneficiariesViewerData.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_beneficiary',
                post_id: postId,
                nonce: beneficiariesViewerData.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ ' + response.data);
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.data);
                }
            },
            error: function() {
                alert('❌ Error deleting beneficiary.');
            }
        });
    }

    window.editBeneficiaryFromModal = function(postId) {
        closeBeneficiaryModal();
        loadBeneficiaryForEdit(postId);
    };

    window.deleteBeneficiaryFromModal = function(postId) {
        closeBeneficiaryModal();
        if (confirm('Are you sure you want to delete this beneficiary?')) {
            deleteBeneficiary(postId);
        }
    };

    window.closeBeneficiaryModal = function() {
        $('#beneficiary-detail-modal').hide();
    };

    $(document).on('click', '.modal-close', function() {
        closeBeneficiaryModal();
    });

    $(document).on('click', '#beneficiary-detail-modal', function(e) {
        if (e.target === this) {
            closeBeneficiaryModal();
        }
    });

    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

})(jQuery);
