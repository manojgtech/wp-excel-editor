jQuery(document).ready(function($) {
    $('input[name="upload_excel"]').on('click', function(e) {
        if (!$('input[name="excel_file"]').val()) {
            e.preventDefault();
            alert('Please select an Excel file to upload.');
        }
    });

    $('.delete-record').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this record?')) {
            e.preventDefault();
        }
    });

    $('.doaction').on('click', function(e) {
        if ($(this).val() === 'delete' && !confirm('Are you sure you want to delete the selected records?')) {
            e.preventDefault();
        } else if ($(this).val() === 'delete') {
            var recordIds = $('input[name="record[]"]:checked').map(function() {
                return $(this).val();
            }).get();
            if (recordIds.length === 0) {
                e.preventDefault();
                alert('Please select at least one record to delete.');
                return;
            }
            $.ajax({
                url: excelProcessing.ajaxurl,
                method: 'POST',
                data: {
                    action: 'excel_processing_delete',
                    nonce: excelProcessing.nonce,
                    record_ids: recordIds
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
    });

    $('.copy-link').on('click', function(e) {
        e.preventDefault();
        var link = $(this).data('link');
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(link).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Link copied to clipboard!');
    });
});