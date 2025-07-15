jQuery(document).ready(function($) {
    console.log('Excel Processing Frontend JS Loaded');

    // Enhanced table functionality
    var $table = $('#excel-comments-table');
    if ($table.length) {
        // Add hover effects
        $table.find('tbody tr').hover(
            function() {
                $(this).addClass('row-hover');
            },
            function() {
                $(this).removeClass('row-hover');
            }
        );

        // Add click handlers for comment cells
        $table.on('click', '.comment-cell', function() {
            var $cell = $(this);
            var recordId = $cell.closest('tr').data('record-id');
            var field = $cell.data('field');
            
            // Trigger edit popup
            $cell.find('.edit-comment').click();
        });

        // Add keyboard navigation
        $table.on('keydown', function(e) {
            var $currentRow = $table.find('tbody tr.selected');
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if ($currentRow.length) {
                        var $nextRow = $currentRow.next('tr');
                        if ($nextRow.length) {
                            $currentRow.removeClass('selected');
                            $nextRow.addClass('selected');
                        }
                    } else {
                        $table.find('tbody tr:first').addClass('selected');
                    }
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    if ($currentRow.length) {
                        var $prevRow = $currentRow.prev('tr');
                        if ($prevRow.length) {
                            $currentRow.removeClass('selected');
                            $prevRow.addClass('selected');
                        }
                    }
                    break;
                    
                case 'Enter':
                    e.preventDefault();
                    if ($currentRow.length) {
                        $currentRow.find('.comment-cell:first .edit-comment').click();
                    }
                    break;
            }
        });

        // Add search functionality
        var $searchInput = $('#excel-search-input');
        if ($searchInput.length) {
            $searchInput.on('input', function() {
                var searchTerm = $(this).val().toLowerCase();
                var filterField = $('#excel-filter-field').val();
                
                $table.find('tbody tr').each(function() {
                    var $row = $(this);
                    var showRow = false;
                    
                    if (searchTerm === '') {
                        showRow = true;
                    } else {
                        if (filterField === 'all') {
                            // Search all visible cells
                            $row.find('td').each(function() {
                                var cellText = $(this).text().toLowerCase();
                                if (cellText.includes(searchTerm)) {
                                    showRow = true;
                                    return false; // break the loop
                                }
                            });
                        } else {
                            // Search specific field
                            var $targetCell = $row.find('td[data-field="' + filterField + '"]');
                            if ($targetCell.length) {
                                var cellText = $targetCell.text().toLowerCase();
                                showRow = cellText.includes(searchTerm);
                            }
                        }
                    }
                    
                    $row.toggle(showRow);
                });
                
                // Update stats
                updateSearchStats();
            });
        }

        // Add sorting functionality
        $table.on('click', '.sortable', function() {
            var $header = $(this);
            var sortField = $header.data('sort');
            var currentOrder = $header.data('order') || 'asc';
            var newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            
            // Update header indicators
            $table.find('.sortable').removeClass('sort-asc sort-desc').data('order', '');
            $header.addClass('sort-' + newOrder).data('order', newOrder);
            
            // Sort table rows
            var $rows = $table.find('tbody tr').get();
            $rows.sort(function(a, b) {
                var aVal = $(a).find('td[data-field="' + sortField + '"]').text();
                var bVal = $(b).find('td[data-field="' + sortField + '"]').text();
                
                // Handle numeric values
                if (sortField === 'mileage' || sortField === 'price_invoiced' || sortField === 'amount_received' || sortField === 'current_amount_due') {
                    aVal = parseFloat(aVal.replace(/[^0-9.-]+/g, '')) || 0;
                    bVal = parseFloat(bVal.replace(/[^0-9.-]+/g, '')) || 0;
                }
                
                if (newOrder === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
            
            $table.find('tbody').empty().append($rows);
        });

        // Add bulk actions
        var $bulkCheckbox = $('#select-all-records');
        if ($bulkCheckbox.length) {
            $bulkCheckbox.on('change', function() {
                var isChecked = $(this).is(':checked');
                $table.find('tbody input[type="checkbox"]').prop('checked', isChecked);
                updateBulkActions();
            });
            
            $table.on('change', 'tbody input[type="checkbox"]', function() {
                updateBulkActions();
            });
        }

        // Add export functionality
        $('#export-excel').on('click', function() {
            var $rows = $table.find('tbody tr:visible');
            var csv = [];
            
            // Add headers
            var headers = [];
            $table.find('thead th').each(function() {
                headers.push($(this).text().trim());
            });
            csv.push(headers.join(','));
            
            // Add data rows
            $rows.each(function() {
                var row = [];
                $(this).find('td').each(function() {
                    var cellText = $(this).text().trim().replace(/"/g, '""');
                    row.push('"' + cellText + '"');
                });
                csv.push(row.join(','));
            });
            
            // Download CSV
            var csvContent = csv.join('\n');
            var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'excel-comments-' + new Date().toISOString().split('T')[0] + '.csv';
            link.click();
        });

        // Initialize tooltips
        $table.find('[title]').tooltip({
            position: { my: 'left+5 center', at: 'right center' }
        });

        // Add auto-save indicator
        var autoSaveTimer;
        $table.on('change', 'input, textarea', function() {
            clearTimeout(autoSaveTimer);
            showAutoSaveIndicator('Saving...');
            
            autoSaveTimer = setTimeout(function() {
                hideAutoSaveIndicator();
            }, 2000);
        });
    }

    // Helper functions
    function updateSearchStats() {
        var visibleRows = $table.find('tbody tr:visible').length;
        var totalRows = $table.find('tbody tr').length;
        
        $('.search-stats').text('Showing ' + visibleRows + ' of ' + totalRows + ' records');
    }

    function updateBulkActions() {
        var checkedCount = $table.find('tbody input[type="checkbox"]:checked').length;
        var $bulkActions = $('.bulk-actions');
        
        if (checkedCount > 0) {
            $bulkActions.show();
            $bulkActions.find('.selected-count').text(checkedCount);
        } else {
            $bulkActions.hide();
        }
    }

    function showAutoSaveIndicator(message) {
        var $indicator = $('.auto-save-indicator');
        if (!$indicator.length) {
            $indicator = $('<div class="auto-save-indicator">' + message + '</div>');
            $('.excel-comments-edit').append($indicator);
        } else {
            $indicator.text(message);
        }
        $indicator.show();
    }

    function hideAutoSaveIndicator() {
        $('.auto-save-indicator').hide();
    }

    // Add enhanced styles
    addEnhancedStyles();
});

// Add enhanced styles
function addEnhancedStyles() {
    var styles = `
        <style>
            .excel-comments-edit {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                max-width: 100%;
                margin: 0 auto;
                padding: 20px;
                background: #f8f9fa;
                min-height: 100vh;
            }
            
            .header-section {
                background: white;
                border-radius: 12px;
                padding: 25px;
                margin-bottom: 25px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            
            .header-left h1 {
                margin: 0 0 8px 0;
                color: #2c3e50;
                font-size: 28px;
                font-weight: 700;
            }
            
            .header-subtitle {
                margin: 0;
                color: #6c757d;
                font-size: 16px;
            }
            
            .header-right .user-info {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            
            .user-name {
                font-weight: 600;
                color: #495057;
            }
            
            .logout-btn {
                display: flex;
                align-items: center;
                gap: 5px;
                background: #dc3545;
                color: white;
                padding: 8px 15px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 14px;
                transition: all 0.3s ease;
            }
            
            .logout-btn:hover {
                background: #c82333;
                color: white;
                text-decoration: none;
            }
            
            .stats-bar {
                display: flex;
                gap: 30px;
                padding: 15px 0;
                border-top: 1px solid #e9ecef;
            }
            
            .stat-item {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            
            .stat-label {
                font-size: 12px;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 5px;
            }
            
            .stat-value {
                font-size: 18px;
                font-weight: 700;
                color: #2c3e50;
            }
            
            .table-container {
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .excel-comments-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
            }
            
            .excel-comments-table th {
                background: #f8f9fa;
                padding: 15px 12px;
                text-align: left;
                font-weight: 600;
                color: #495057;
                border-bottom: 2px solid #dee2e6;
                position: sticky;
                top: 0;
                z-index: 10;
            }
            
            .excel-comments-table td {
                padding: 12px;
                border-bottom: 1px solid #e9ecef;
                vertical-align: top;
            }
            
            .excel-comments-table tbody tr:hover {
                background: #f8f9fa;
            }
            
            .excel-comments-table tbody tr.selected {
                background: #e3f2fd;
            }
            
            .comment-cell {
                cursor: pointer;
                position: relative;
                min-width: 150px;
                max-width: 200px;
            }
            
            .comment-cell:hover {
                background: #e3f2fd;
            }
            
            .comment-content {
                position: relative;
                padding: 8px;
                border-radius: 4px;
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                min-height: 40px;
                word-wrap: break-word;
            }
            
            .comment-content.has-content {
                background: #d4edda;
                border-color: #c3e6cb;
            }
            
            .comment-content.empty {
                background: #f8f9fa;
                border-color: #e9ecef;
                color: #6c757d;
                font-style: italic;
            }
            
            .edit-comment {
                position: absolute;
                top: 5px;
                right: 5px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 3px;
                padding: 2px 6px;
                font-size: 10px;
                cursor: pointer;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .comment-cell:hover .edit-comment {
                opacity: 1;
            }
            
            .edit-comment:hover {
                background: #0056b3;
            }
            
            .sortable {
                cursor: pointer;
                user-select: none;
            }
            
            .sortable:hover {
                background: #e9ecef;
            }
            
            .sort-icon {
                margin-left: 5px;
                opacity: 0.5;
            }
            
            .sort-asc .sort-icon::after {
                content: " ↑";
                opacity: 1;
            }
            
            .sort-desc .sort-icon::after {
                content: " ↓";
                opacity: 1;
            }
            
            .auto-save-indicator {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 10px 15px;
                border-radius: 6px;
                font-size: 14px;
                z-index: 1000;
                display: none;
            }
            
            .message {
                padding: 15px;
                margin: 15px 0;
                border-radius: 6px;
                font-size: 14px;
            }
            
            .message.success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            
            .message.error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            
            .message.info {
                background: #d1ecf1;
                color: #0c5460;
                border: 1px solid #bee5eb;
            }
            
            @media (max-width: 768px) {
                .excel-comments-edit {
                    padding: 10px;
                }
                
                .header-content {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 15px;
                }
                
                .stats-bar {
                    flex-direction: column;
                    gap: 15px;
                }
                
                .table-container {
                    overflow-x: auto;
                }
                
                .excel-comments-table {
                    min-width: 800px;
                }
            }
        </style>
    `;
    
    if (!document.getElementById('excel-enhanced-styles')) {
        var styleElement = document.createElement('div');
        styleElement.id = 'excel-enhanced-styles';
        styleElement.innerHTML = styles;
        document.head.appendChild(styleElement);
    }
}