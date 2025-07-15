<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

add_action('admin_menu', 'excel_processing_admin_menu');

function excel_processing_admin_menu() {
    add_menu_page(
        'Excel Processing',
        'Excel Processing',
        'manage_options',
        'excel-processing',
        'excel_processing_admin_page',
        'dashicons-media-spreadsheet',
        30
    );
    
    add_submenu_page(
        'excel-processing',
        'User Guide',
        'User Guide',
        'manage_options',
        'excel-processing-guide',
        'excel_processing_user_guide_page'
    );
}

function excel_processing_admin_page() {
    // Handle file upload
    if (isset($_POST['upload_excel']) && check_admin_referer('excel_upload_nonce')) {
        $olddta = isset($_POST['olddta']) ? $_POST['olddta'] : 'n';
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['excel_file'];
            $allowed_types = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if($olddta == 'y'){
                $wpdb->query("DELETE FROM $table_name");
            }
            if (in_array($file['type'], $allowed_types) || pathinfo($file['name'], PATHINFO_EXTENSION) === 'xlsx' || pathinfo($file['name'], PATHINFO_EXTENSION) === 'xls') {
                require_once EXCEL_PROCESSING_PLUGIN_DIR . 'vendor/autoload.php';
                
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();
                    
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'excel_data';
                    
                    // Skip header row and insert data
                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        if (count($row) >= 14) {
                            $wpdb->insert(
                                $table_name,
                                [
                                    'invoice_id' => $row[0] ?? '',
                                    'dispatch_id' => $row[1] ?? '',
                                    'date_time_of_service' => $row[2] ?? null,
                                    'patient_full_name' => $row[3] ?? '',
                                    'billable_service_code' => $row[4] ?? '',
                                    'origin_name' => $row[5] ?? '',
                                    'origin_address' => $row[6] ?? '',
                                    'destination_name' => $row[7] ?? '',
                                    'destination_address' => $row[8] ?? '',
                                    'mileage' => floatval($row[9] ?? 0),
                                    'price_invoiced' => floatval($row[10] ?? 0),
                                    'amount_received' => floatval($row[11] ?? 0),
                                    'current_amount_due' => floatval($row[12] ?? 0),
                                    'billing_notes' => $row[13] ?? '',
                                ],
                                ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%s']
                            );
                        }
                    }
                    echo '<div class="updated"><p>Excel file uploaded and processed successfully!</p></div>';
                } catch (Exception $e) {
                    echo '<div class="error"><p>Error processing Excel file: ' . esc_html($e->getMessage()) . '</p></div>';
                }
            } else {
                echo '<div class="error"><p>Please upload a valid Excel file (.xls or .xlsx).</p></div>';
            }
        }
    }

    $share_link = esc_url(site_url('/beebe-invoices'));
    ?>
    <div class="wrap">
        <h1>Excel Processing</h1>
        
        <div class="quick-info-section">
            <h2>📤 Upload Excel File</h2>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('excel_upload_nonce'); ?>
                <input type="file" name="excel_file" accept=".xls,.xlsx" required>
               
                <p>
                <input type="checkbox" name="olddta" value='y' />Delete old data?
                </p>
                <input type="submit" name="upload_excel" class="button button-primary" value="Upload Excel">
            </form>
        </div>

        <div class="quick-info-section">
            <h2>🔗 Comments Editing Access</h2>
            <p>Share this link with users who have the <code>excel_editor</code> role to allow them to edit comments:</p>
            <input type="text" value="<?php echo $share_link; ?>" readonly class="share-link-input" style="width: 400px;">
            <button class="button copy-link" data-link="<?php echo $share_link; ?>">Copy Link</button>
            <p><strong>Note:</strong> Billing Notes are read-only and cannot be edited.</p>
        </div>

        <div class="quick-info-section">
            <h2>📋 Excel Data</h2>
            <form method="get" action="">
                <input type="hidden" name="page" value="excel-processing">
                <p class="search-box">
                    <label class="screen-reader-text" for="excel-search-input">Search Records:</label>
                    <input type="search" id="excel-search-input" name="s" value="<?php echo esc_attr(get_query_var('s')); ?>">
                    <input type="submit" class="button" value="Search Records">
                </p>
            </form>
            <?php
            $table = new Excel_Data_Table();
            $table->prepare_items();
            $table->display();
            ?>
        </div>
    </div>
    <?php
}

function excel_processing_user_guide_page() {
    $share_link = esc_url(site_url('/beebe-invoices'));
    ?>
    <div class="wrap">
        <h1>📖 Excel Processing - User Guide</h1>
        
        <div class="user-guide-section">
            <div class="guide-content">
                <div class="guide-section">
                    <h3>🚀 Getting Started</h3>
                    <ol>
                        <li><strong>Upload Excel File:</strong> Use the upload form in the main Excel Processing page</li>
                        <li><strong>Create Excel Editor Users:</strong> Go to Users > Add New and assign the "Excel Editor" role</li>
                        <li><strong>Share Access:</strong> Share the comments editing link with authorized users</li>
                    </ol>
                </div>
                
                <div class="guide-section">
                    <h3>👥 User Management</h3>
                    <div class="user-management-info">
                        <h4>Creating Excel Editor Users:</h4>
                        <ol>
                            <li>Go to <strong>Users > Add New</strong> in WordPress admin</li>
                            <li>Fill in the user details (username, email, password)</li>
                            <li>Set the role to <strong>"Excel Editor"</strong></li>
                            <li>Click <strong>"Add New User"</strong></li>
                        </ol>
                        
                        <h4>Excel Editor Permissions:</h4>
                        <ul>
                            <li>✅ Access to comments editing page</li>
                            <li>✅ Edit Beebe, AEC, and Epic comments</li>
                            <li>❌ Cannot edit Billing Notes (read-only)</li>
                            <li>❌ Cannot access admin panel</li>
                            <li>❌ Cannot upload or delete records</li>
                        </ul>
                    </div>
                </div>
                
                <div class="guide-section">
                    <h3>🔗 Comments Editing Access</h3>
                    <div class="access-info">
                        <p><strong>Shareable Link:</strong> <code><?php echo $share_link; ?></code></p>
                        <button class="button copy-link" data-link="<?php echo $share_link; ?>">Copy Link</button>
                        
                        <h4>How it works:</h4>
                        <ol>
                            <li>Users click the shared link</li>
                            <li>Custom login page appears (if not logged in)</li>
                            <li>Users login with their WordPress credentials</li>
                            <li>Only users with "Excel Editor" role can access</li>
                            <li>Comments can be edited using the popup dialog</li>
                        </ol>
                    </div>
                </div>
                
                <div class="guide-section">
                    <h3>📊 Data Display</h3>
                    <div class="display-info">
                        <h4>Shortcode Usage:</h4>
                        <p>Add this shortcode to any page or post to display the Excel data:</p>
                        <code>[excel_data]</code>
                        
                        <h4>Features:</h4>
                        <ul>
                            <li>✅ Responsive table design</li>
                            <li>✅ Horizontal scrolling on mobile</li>
                            <li>✅ Clean date format (no time)</li>
                            <li>✅ Hover effects and modern styling</li>
                        </ul>
                    </div>
                </div>
                
                <div class="guide-section">
                    <h3>🔧 Troubleshooting</h3>
                    <div class="troubleshooting-info">
                        <h4>Common Issues:</h4>
                        <ul>
                            <li><strong>Access Denied:</strong> User doesn't have "Excel Editor" role</li>
                            <li><strong>Login Loop:</strong> Clear browser cache and cookies</li>
                            <li><strong>Table Not Loading:</strong> Check if Excel file is uploaded</li>
                            <li><strong>Comments Not Saving:</strong> Check user permissions and network connection</li>
                        </ul>
                    </div>
                </div>
                
                <div class="guide-section">
                    <h3>📋 Excel File Requirements</h3>
                    <div class="requirements-info">
                        <h4>File Format:</h4>
                        <ul>
                            <li>Supported formats: .xls, .xlsx</li>
                            <li>First row should contain headers</li>
                            <li>Minimum 14 columns required</li>
                            <li>Date format: YYYY-MM-DD HH:MM:SS</li>
                        </ul>
                        
                        <h4>Column Order:</h4>
                        <ol>
                            <li>Invoice ID</li>
                            <li>Dispatch ID</li>
                            <li>Date/Time of Service</li>
                            <li>Patient Name</li>
                            <li>Service Code</li>
                            <li>Origin Name</li>
                            <li>Origin Address</li>
                            <li>Destination Name</li>
                            <li>Destination Address</li>
                            <li>Mileage</li>
                            <li>Price Invoiced</li>
                            <li>Amount Received</li>
                            <li>Current Amount Due</li>
                            <li>Billing Notes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// AJAX handler for comment updates
add_action('wp_ajax_excel_processing_update_comments', 'excel_processing_update_comments');
add_action('wp_ajax_nopriv_excel_processing_update_comments', 'excel_processing_update_comments');
function excel_processing_update_comments() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'excel_data';
    $record_id = intval($_POST['record_id']);

    $data = [
        'beebe_comments' => isset($_POST['beebe_comments']) ? sanitize_textarea_field($_POST['beebe_comments']) : '',
        'aec_comments' => isset($_POST['aec_comments']) ? sanitize_textarea_field($_POST['aec_comments']) : '',
        'epic_comments' => isset($_POST['epic_comments']) ? sanitize_textarea_field($_POST['epic_comments']) : '',
    ];

    $result = $wpdb->update(
        $table_name,
        $data,
        ['id' => $record_id],
        ['%s', '%s', '%s'],
        ['%d']
    );

    if ($result !== false) {
        wp_send_json_success('Comments updated successfully');
    } else {
        wp_send_json_error('Failed to update comments');
    }
}

// AJAX handler for fetching comments
add_action('wp_ajax_excel_processing_get_comments', 'excel_processing_get_comments');
add_action('wp_ajax_nopriv_excel_processing_get_comments', 'excel_processing_get_comments');
function excel_processing_get_comments() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'excel_data';
    $record_id = intval($_POST['record_id']);
    
    $record = $wpdb->get_row($wpdb->prepare("SELECT beebe_comments, aec_comments, epic_comments FROM $table_name WHERE id = %d", $record_id), ARRAY_A);
    
    if ($record) {
        wp_send_json_success($record);
    } else {
        wp_send_json_error('Record not found');
    }
}
?>