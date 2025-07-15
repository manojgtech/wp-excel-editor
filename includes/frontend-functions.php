<?php
add_shortcode('excel_data', 'excel_data_shortcode');

function excel_data_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'per_page' => 10,
        'show_search' => 'true',
        'show_pagination' => 'true',
        'show_stats' => 'true'
    ), $atts);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'excel_data';
    
    // Get current page
    $current_page = isset($_GET['excel_page']) ? max(1, intval($_GET['excel_page'])) : 1;
    $per_page = intval($atts['per_page']);
    $offset = ($current_page - 1) * $per_page;
    
    // Get total records count
    $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_records / $per_page);
    
    // Get records for current page
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ),
        ARRAY_A
    );

    ob_start();
    ?>
    <div class="excel-data-container">
        <?php if ($atts['show_stats'] === 'true'): ?>
        <div class="excel-stats-bar">
            <div class="stats-item">
                <span class="stats-label">Total Records:</span>
                <span class="stats-value"><?php echo number_format($total_records); ?></span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Page:</span>
                <span class="stats-value"><?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
            </div>
            <div class="stats-item">
                <span class="stats-label">Showing:</span>
                <span class="stats-value"><?php echo count($results); ?> records</span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_search'] === 'true'): ?>
        <div class="excel-search-section">
            <div class="search-box">
                <input type="text" id="excel-search-input" placeholder="Search records..." class="search-input">
                <button type="button" id="excel-search-btn" class="search-btn">
                    <span class="dashicons dashicons-search"></span>
                </button>
            </div>
            <div class="search-filters">
                <select id="excel-filter-field" class="filter-select">
                    <option value="all">All Fields</option>
                    <option value="invoice_id">Invoice ID</option>
                    <option value="patient_full_name">Patient Name</option>
                    <option value="billable_service_code">Service Code</option>
                    <option value="origin_name">Origin</option>
                    <option value="destination_name">Destination</option>
                </select>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="table-wrapper">
            <table class="excel-data-table" id="excel-data-table">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="invoice_id">Invoice ID <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="dispatch_id">Dispatch ID <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="date_time_of_service">Service Date <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="patient_full_name">Patient Name <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="billable_service_code">Service Code <span class="sort-icon">↕</span></th>
                        <th>Origin Name</th>
                        <th>Origin Address</th>
                        <th>Destination Name</th>
                        <th>Destination Address</th>
                        <th class="sortable" data-sort="mileage">Mileage <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="price_invoiced">Price Invoiced <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="amount_received">Amount Received <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="current_amount_due">Amount Due <span class="sort-icon">↕</span></th>
                        <th>Billing Notes</th>
                        <th>Beebe Comments</th>
                        <th>AEC Comments</th>
                        <th>Epic Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="17" class="no-data">
                                <div class="no-data-message">
                                    <span class="dashicons dashicons-database"></span>
                                    <p>No records found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $row): ?>
                            <tr class="data-row" data-record-id="<?php echo esc_attr($row['id']); ?>">
                                <td class="invoice-id">
                                    <strong><?php echo esc_html($row['invoice_id']); ?></strong>
                                </td>
                                <td><?php echo esc_html($row['dispatch_id']); ?></td>
                                <td class="date-cell">
                                    <span class="date-value"><?php echo formatShortcodeDate($row['date_time_of_service']); ?></span>
                                </td>
                                <td class="patient-name"><?php echo esc_html($row['patient_full_name']); ?></td>
                                <td class="service-code"><?php echo esc_html($row['billable_service_code']); ?></td>
                                <td class="origin-name"><?php echo esc_html($row['origin_name']); ?></td>
                                <td class="origin-address">
                                    <div class="address-content"><?php echo esc_html($row['origin_address']); ?></div>
                                </td>
                                <td class="destination-name"><?php echo esc_html($row['destination_name']); ?></td>
                                <td class="destination-address">
                                    <div class="address-content"><?php echo esc_html($row['destination_address']); ?></div>
                                </td>
                                <td class="mileage"><?php echo number_format($row['mileage'], 1); ?></td>
                                <td class="price-invoiced">$<?php echo number_format($row['price_invoiced'], 2); ?></td>
                                <td class="amount-received">$<?php echo number_format($row['amount_received'], 2); ?></td>
                                <td class="amount-due">
                                    <span class="amount-value <?php echo $row['current_amount_due'] > 0 ? 'outstanding' : 'paid'; ?>">
                                        $<?php echo number_format($row['current_amount_due'], 2); ?>
                                    </span>
                                </td>
                                <td class="billing-notes">
                                    <div class="notes-content"><?php echo esc_html($row['billing_notes']); ?></div>
                                </td>
                                <td class="comment-cell">
                                    <div class="comment-content <?php echo !empty($row['beebe_comments']) ? 'has-content' : 'empty'; ?>">
                                        <?php echo esc_html($row['beebe_comments']); ?>
                                    </div>
                                </td>
                                <td class="comment-cell">
                                    <div class="comment-content <?php echo !empty($row['aec_comments']) ? 'has-content' : 'empty'; ?>">
                                        <?php echo esc_html($row['aec_comments']); ?>
                                    </div>
                                </td>
                                <td class="comment-cell">
                                    <div class="comment-content <?php echo !empty($row['epic_comments']) ? 'has-content' : 'empty'; ?>">
                                        <?php echo esc_html($row['epic_comments']); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($atts['show_pagination'] === 'true' && $total_pages > 1): ?>
        <div class="excel-pagination">
            <div class="pagination-info">
                <span>Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_records); ?> of <?php echo number_format($total_records); ?> records</span>
            </div>
            <div class="pagination-controls">
                <?php if ($current_page > 1): ?>
                    <a href="<?php echo add_query_arg('excel_page', 1); ?>" class="pagination-btn first-page" title="First Page">
                        <span class="dashicons dashicons-controls-skipback"></span>
                    </a>
                    <a href="<?php echo add_query_arg('excel_page', $current_page - 1); ?>" class="pagination-btn prev-page" title="Previous Page">
                        <span class="dashicons dashicons-controls-back"></span>
                    </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<span class="page-ellipsis">...</span>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active_class = ($i == $current_page) ? 'active' : '';
                        echo '<a href="' . add_query_arg('excel_page', $i) . '" class="pagination-btn page-number ' . $active_class . '">' . $i . '</a>';
                    }
                    
                    if ($end_page < $total_pages) {
                        echo '<span class="page-ellipsis">...</span>';
                    }
                    ?>
                </div>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?php echo add_query_arg('excel_page', $current_page + 1); ?>" class="pagination-btn next-page" title="Next Page">
                        <span class="dashicons dashicons-controls-forward"></span>
                    </a>
                    <a href="<?php echo add_query_arg('excel_page', $total_pages); ?>" class="pagination-btn last-page" title="Last Page">
                        <span class="dashicons dashicons-controls-skipforward"></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

add_action('init', 'excel_processing_rewrite_rules');

function excel_processing_rewrite_rules() {
    add_rewrite_rule(
        '^beebe-invoices/?$',
        'index.php?excel_comments_all=1',
        'top'
    );
}

add_filter('query_vars', 'excel_processing_query_vars');

function excel_processing_query_vars($vars) {
    $vars[] = 'excel_comments_all';
    return $vars;
}

add_action('template_redirect', 'excel_processing_comments_all_template');

function excel_processing_comments_all_template() {
    if (get_query_var('excel_comments_all')) {
        // Handle logout action
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            // Clear WordPress cookies
            wp_clear_auth_cookie();
            
            // Clear session data
            if (session_id() || session_start()) {
                session_destroy();
            }
            
            // Clear all session variables
            $_SESSION = array();
            
            // Delete session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Redirect to login page
            wp_redirect(site_url('/beebe-invoices'));
            exit;
        }
        
        // Check if user is logged in via WordPress
        if (!is_user_logged_in()) {
            // Check for session-based authentication
            if (session_id() || session_start()) {
                if (isset($_SESSION['excel_editor_logged_in']) && $_SESSION['excel_editor_logged_in'] === true) {
                    // Session-based authentication found
                    $session_user_id = $_SESSION['excel_user_id'];
                    $session_user_login = $_SESSION['excel_user_login'];
                    
                    // Set WordPress user from session
                    wp_set_current_user($session_user_id);
                    
                    // Continue to main page with session authentication
                } else {
                    // Show login form
                    ?>
                    <!DOCTYPE html>
                    <html <?php language_attributes(); ?>>
                    <head>
                        <meta charset="<?php bloginfo('charset'); ?>">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <title>Excel Comments Editor - Login Required</title>
                        <?php wp_head(); ?>
                        <style>
                            body {
                                margin: 0;
                                padding: 0;
                                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                min-height: 100vh;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                            
                            .login-container {
                                background: rgba(255, 255, 255, 0.95);
                                backdrop-filter: blur(10px);
                                border-radius: 20px;
                                padding: 40px;
                                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
                                width: 100%;
                                max-width: 400px;
                                text-align: center;
                                border: 1px solid rgba(255, 255, 255, 0.2);
                            }
                            
                            .login-header {
                                margin-bottom: 30px;
                            }
                            
                            .login-header h1 {
                                color: #667eea;
                                margin: 0 0 10px 0;
                                font-size: 28px;
                                font-weight: 700;
                            }
                            
                            .login-header p {
                                color: #666;
                                margin: 0;
                                font-size: 16px;
                            }
                            
                            .login-form {
                                text-align: left;
                            }
                            
                            .form-group {
                                margin-bottom: 20px;
                            }
                            
                            .form-group label {
                                display: block;
                                margin-bottom: 8px;
                                color: #333;
                                font-weight: 600;
                                font-size: 14px;
                            }
                            
                            .form-group input {
                                width: 100%;
                                padding: 12px 16px;
                                border: 2px solid #e1e5e9;
                                border-radius: 8px;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                box-sizing: border-box;
                            }
                            
                            .form-group input:focus {
                                outline: none;
                                border-color: #667eea;
                                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                            }
                            
                            .login-btn {
                                width: 100%;
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                color: white;
                                border: none;
                                padding: 14px 20px;
                                border-radius: 8px;
                                font-size: 16px;
                                font-weight: 600;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                margin-top: 10px;
                            }
                            
                            .login-btn:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
                            }
                            
                            .login-footer {
                                margin-top: 25px;
                                padding-top: 20px;
                                border-top: 1px solid #e1e5e9;
                                color: #666;
                                font-size: 14px;
                            }
                            
                            .error-message {
                                background: #f8d7da;
                                color: #721c24;
                                padding: 12px;
                                border-radius: 6px;
                                margin-bottom: 20px;
                                border: 1px solid #f5c6cb;
                                font-size: 14px;
                            }
                            
                            .back-link {
                                display: inline-block;
                                margin-top: 20px;
                                color: #667eea;
                                text-decoration: none;
                                font-weight: 500;
                                transition: color 0.3s ease;
                            }
                            
                            .back-link:hover {
                                color: #764ba2;
                            }
                            
                            @media (max-width: 480px) {
                                .login-container {
                                    margin: 20px;
                                    padding: 30px 20px;
                                }
                                
                                .login-header h1 {
                                    font-size: 24px;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="login-container">
                            <div class="login-header">
                                <h1>🔐 Excel Comments Editor</h1>
                                <p>Please login to access the comments editing page</p>
                            </div>
                            
                            <?php
                            // Handle login form submission
                            if (isset($_POST['excel_login'])) {
                                // Verify nonce
                                if (!wp_verify_nonce($_POST['excel_login_nonce'], 'excel_login_nonce')) {
                                    echo '<div class="error-message">Security check failed. Please try again.</div>';
                                } else {
                                    $username = sanitize_text_field($_POST['username']);
                                    $password = $_POST['password'];
                                
                                    if (empty($username) || empty($password)) {
                                        echo '<div class="error-message">Please enter both username and password.</div>';
                                    } else {
                                        // Use WordPress standard login process
                                        $creds = array(
                                            'user_login'    => $username,
                                            'user_password' => $password,
                                            'remember'      => true
                                        );
                                        
                                        $user = wp_signon($creds, is_ssl());
                                        
                                        if (is_wp_error($user)) {
                                            echo '<div class="error-message">Login failed: ' . $user->get_error_message() . '</div>';
                                        } else {
                                            // Check if user has excel_editor role or is admin
                                            $has_excel_editor_role = in_array('excel_editor', $user->roles);
                                            $is_admin = user_can($user->ID, 'manage_options');
                                            
                                            if (!$has_excel_editor_role && !$is_admin) {
                                                echo '<div class="error-message">You do not have permission to access this page. Only users with "Excel Editor" role can access.</div>';
                                            } else {
                                                // Manual authentication setup
                                                wp_set_current_user($user->ID);
                                                wp_set_auth_cookie($user->ID, true, is_ssl());
                                                
                                                // Start session for fallback authentication
                                                if (!session_id()) {
                                                    session_start();
                                                }
                                                $_SESSION['excel_editor_logged_in'] = true;
                                                $_SESSION['excel_user_id'] = $user->ID;
                                                $_SESSION['excel_user_login'] = $user->user_login;
                                                $_SESSION['excel_login_time'] = time();
                                                 
                                                // Redirect immediately
                                                wp_redirect(site_url('/beebe-invoices'));
                                                echo "<script>window.location.href = '" . site_url('/beebe-invoices') . "';</script>";
                                                exit;
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                            
                            <form method="post" class="login-form" action="<?php echo esc_url(site_url('/beebe-invoices')); ?>">
                                <?php wp_nonce_field('excel_login_nonce', 'excel_login_nonce'); ?>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username" required autocomplete="username">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" required autocomplete="current-password">
                                </div>
                                
                                <button type="submit" name="excel_login" class="login-btn">Login to Access Editor</button>
                            </form>
                            
                            <div class="login-footer">
                                <p>Only users with "Excel Editor" role can access this page.</p>
                                <a href="<?php echo esc_url(home_url()); ?>" class="back-link">← Back to Homepage</a>
                            </div>
                        </div>
                        <?php wp_footer(); ?>
                    </body>
                    </html>
                    <?php
                    exit;
                }
            }
        }
        
        // Ensure WordPress authentication is properly set
        $current_user = wp_get_current_user();
        if (!$current_user->exists()) {
            // User doesn't exist, redirect to login
            wp_redirect(site_url('/beebe-invoices'));
            exit;
        }
        
        // Rely only on WordPress authentication - no custom session needed
        $current_user_id = get_current_user_id();
        
        // Ensure WordPress auth cookie is set properly
        if (!wp_validate_auth_cookie()) {
            wp_set_auth_cookie($current_user_id, true, is_ssl());
        }

        // Check if user has excel_editor role or is admin
        $user = wp_get_current_user();
        $has_excel_editor_role = in_array('excel_editor', $user->roles);
        $is_admin = current_user_can('manage_options');
        
        if (!$has_excel_editor_role && !$is_admin) {
            wp_die(
                '<div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
                    <h2 style="color: #d63638;">Access Denied</h2>
                    <p style="font-size: 16px; color: #50575e;">You do not have permission to access this page.</p>
                    <p style="font-size: 14px; color: #646970;">Only users with the "Excel Editor" role can access this page.</p>
                    <p style="margin-top: 30px;">
                        <a href="' . esc_url(home_url()) . '" style="background: #2271b1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Go to Homepage</a>
                    </p>
                </div>',
                'Access Denied',
                ['response' => 403]
            );
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'excel_data';

        // Pagination settings
        $per_page = 20;
        $current_page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Search functionality
        $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $search_field = isset($_GET['search_field']) ? sanitize_text_field($_GET['search_field']) : 'all';
        
        // Build query with search
        $where_clause = '';
        $query_params = [];
        
        if (!empty($search_term)) {
            switch ($search_field) {
                case 'invoice_id':
                    $where_clause = "WHERE invoice_id LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    break;
                case 'patient_name':
                    $where_clause = "WHERE patient_full_name LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    break;
                case 'service_code':
                    $where_clause = "WHERE billable_service_code LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    break;
                case 'origin':
                    $where_clause = "WHERE origin_name LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    break;
                case 'destination':
                    $where_clause = "WHERE destination_name LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    break;
                case 'billing_notes':
                    $where_clause = "WHERE billing_notes LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    break;
                case 'comments':
                    $where_clause = "WHERE beebe_comments LIKE %s OR aec_comments LIKE %s OR epic_comments LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    break;
                default: // all
                    $where_clause = "WHERE invoice_id LIKE %s OR patient_full_name LIKE %s OR billable_service_code LIKE %s OR origin_name LIKE %s OR destination_name LIKE %s OR billing_notes LIKE %s OR beebe_comments LIKE %s OR aec_comments LIKE %s OR epic_comments LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    $query_params[] = '%' . $wpdb->esc_like($search_term) . '%';
                    break;
            }
        }
        
        // Get total records count
        $count_query = "SELECT COUNT(*) FROM $table_name $where_clause";
        if (!empty($query_params)) {
            $count_query = $wpdb->prepare($count_query, $query_params);
        }
        $total_records = $wpdb->get_var($count_query);
        $total_pages = ceil($total_records / $per_page);
        
        // Get records for current page
        $query = "SELECT * FROM $table_name $where_clause ORDER BY id DESC LIMIT %d OFFSET %d";
        $query_params[] = $per_page;
        $query_params[] = $offset;
        $query = $wpdb->prepare($query, $query_params);
        $results = $wpdb->get_results($query, ARRAY_A);

        if (empty($results)) {
            ?>
            <!DOCTYPE html>
            <html <?php language_attributes(); ?>>
            <head>
                <meta charset="<?php bloginfo('charset'); ?>">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Excel Comments Editor</title>
                <?php wp_head(); ?>
            </head>
            <body>
                <div class="excel-comments-edit">
                    <div class="header-section">
                        <h1>Excel Comments Editor</h1>
                        <p>No records found. Please upload an Excel file first.</p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=excel-processing')); ?>" class="button">Go to Admin Panel</a>
                    </div>
                </div>
                <?php wp_footer(); ?>
            </body>
            </html>
            <?php
            exit;
        }

        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Excel Comments Editor</title>
            <?php wp_head(); ?>
        </head>
        <body>
            <div class="excel-comments-edit">
                <div class="header-section">
                    <div class="header-content">
                        <div class="header-left">
                            <h1>📝 Excel Comments Editor</h1>
                            <p class="header-subtitle">Edit comments for Excel records. Click on any comment cell to edit.</p>
                        </div>
                        <div class="header-right">
                            <div class="user-info">
                                <span class="user-name"><?php echo esc_html($current_user->display_name); ?></span>
                                <a href="<?php echo esc_url(site_url('/beebe-invoices?action=logout')); ?>" class="logout-btn">
                                    <span class="dashicons dashicons-exit"></span>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-bar">
                        <div class="stat-item">
                            <span class="stat-label">Total Records:</span>
                            <span class="stat-value"><?php echo number_format($total_records); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Records with Comments:</span>
                            <span class="stat-value"><?php echo number_format(countRecordsWithComments($results)); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Page:</span>
                            <span class="stat-value"><?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">User:</span>
                            <span class="stat-value"><?php echo esc_html($current_user->user_login); ?></span>
                        </div>
                    </div>
                    
                    <!-- Search Section -->
                    <div class="search-section">
                        <form method="get" action="<?php echo esc_url(site_url('/beebe-invoices')); ?>" class="search-form">
                            <div class="search-inputs">
                                <input type="text" name="search" value="<?php echo esc_attr($search_term); ?>" placeholder="Search records..." class="search-input">
                                <select name="search_field" class="search-field">
                                    <option value="all" <?php selected($search_field, 'all'); ?>>All Fields</option>
                                    <option value="invoice_id" <?php selected($search_field, 'invoice_id'); ?>>Invoice ID</option>
                                    <option value="patient_name" <?php selected($search_field, 'patient_name'); ?>>Patient Name</option>
                                    <option value="service_code" <?php selected($search_field, 'service_code'); ?>>Service Code</option>
                                    <option value="origin" <?php selected($search_field, 'origin'); ?>>Origin</option>
                                    <option value="destination" <?php selected($search_field, 'destination'); ?>>Destination</option>
                                    <option value="billing_notes" <?php selected($search_field, 'billing_notes'); ?>>Billing Notes</option>
                                    <option value="comments" <?php selected($search_field, 'comments'); ?>>Comments</option>
                                </select>
                                <button type="submit" class="search-btn">
                                    <span class="dashicons dashicons-search"></span>
                                    Search
                                </button>
                                <?php if (!empty($search_term)): ?>
                                    <a href="<?php echo esc_url(site_url('/beebe-invoices')); ?>" class="clear-search-btn">
                                        <span class="dashicons dashicons-dismiss"></span>
                                        Clear
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="table-wrapper">
                    <table class="wp-list-table widefat fixed striped excel-data-table commentftbl">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Invoice ID</th>
                                <th>Dispatch ID</th>
                                <th>Service Date</th>
                                <th>Patient Name</th>
                                <th>Service Code</th>
                                <th>Origin Name</th>
                                <th>Origin Address</th>
                                <th>Destination Name</th>
                                <th>Destination Address</th>
                                <th>Mileage</th>
                                <th>Price Invoiced</th>
                                <th>Amount Received</th>
                                <th>Amount Due</th>
                                <th>Billing Notes</th>
                                <th>Beebe Comments</th>
                                <th>AEC Comments</th>
                                <th>Epic Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr data-record-id="<?php echo esc_attr($row['id']); ?>">
                                    <td>
                                        <span class="status-indicator <?php echo (hasAnyComments($row)) ? 'has-comments' : 'no-comments'; ?>"></span>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($row['invoice_id']); ?></strong>
                                    </td>
                                    <td><?php echo esc_html($row['dispatch_id']); ?></td>
                                    <td><?php echo formatDate($row['date_time_of_service']); ?></td>
                                    <td><?php echo esc_html($row['patient_full_name']); ?></td>
                                    <td><?php echo esc_html($row['billable_service_code']); ?></td>
                                    <td><?php echo esc_html($row['origin_name']); ?></td>
                                    <td>
                                        <div class="address-cell">
                                            <?php echo esc_html($row['origin_address']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($row['destination_name']); ?></td>
                                    <td>
                                        <div class="address-cell">
                                            <?php echo esc_html($row['destination_address']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($row['mileage'], 1); ?></td>
                                    <td>$<?php echo number_format($row['price_invoiced'], 2); ?></td>
                                    <td>$<?php echo number_format($row['amount_received'], 2); ?></td>
                                    <td>
                                        <span class="amount-due <?php echo $row['current_amount_due'] > 0 ? 'outstanding' : 'paid'; ?>">
                                            $<?php echo number_format($row['current_amount_due'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="billing-notes-cell">
                                            <?php echo esc_html($row['billing_notes']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="comment-cell">
                                            <span class="edit-comment" data-id="<?php echo esc_attr($row['id']); ?>" data-field="beebe_comments">
                                                <span class="dashicons dashicons-edit"></span>
                                                <span class="edit-text">Edit</span>
                                            </span>
                                            <div class="comment-content <?php echo !empty($row['beebe_comments']) ? 'has-content' : ''; ?>">
                                                <?php echo esc_html($row['beebe_comments']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="comment-cell">
                                            <span class="edit-comment" data-id="<?php echo esc_attr($row['id']); ?>" data-field="aec_comments">
                                                <span class="dashicons dashicons-edit"></span>
                                                <span class="edit-text">Edit</span>
                                            </span>
                                            <div class="comment-content <?php echo !empty($row['aec_comments']) ? 'has-content' : ''; ?>">
                                                <?php echo esc_html($row['aec_comments']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="comment-cell">
                                            <span class="edit-comment" data-id="<?php echo esc_attr($row['id']); ?>" data-field="epic_comments">
                                                <span class="dashicons dashicons-edit"></span>
                                                <span class="edit-text">Edit</span>
                                            </span>
                                            <div class="comment-content <?php echo !empty($row['epic_comments']) ? 'has-content' : ''; ?>">
                                                <?php echo esc_html($row['epic_comments']); ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_records); ?> of <?php echo number_format($total_records); ?> records
                    </div>
                    <div class="pagination-controls">
                        <?php if ($current_page > 1): ?>
                            <a href="<?php echo add_query_arg(['page_num' => 1, 'search' => $search_term, 'search_field' => $search_field]); ?>" class="pagination-btn first-page" title="First Page">
                                <span class="dashicons dashicons-controls-skipback"></span>
                            </a>
                            <a href="<?php echo add_query_arg(['page_num' => $current_page - 1, 'search' => $search_term, 'search_field' => $search_field]); ?>" class="pagination-btn prev-page" title="Previous Page">
                                <span class="dashicons dashicons-controls-back"></span>
                            </a>
                        <?php endif; ?>
                        
                        <div class="page-numbers">
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            if ($start_page > 1) {
                                echo '<span class="page-ellipsis">...</span>';
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $active_class = ($i == $current_page) ? 'active' : '';
                                echo '<a href="' . add_query_arg(['page_num' => $i, 'search' => $search_term, 'search_field' => $search_field]) . '" class="pagination-btn page-number ' . $active_class . '">' . $i . '</a>';
                            }
                            
                            if ($end_page < $total_pages) {
                                echo '<span class="page-ellipsis">...</span>';
                            }
                            ?>
                        </div>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?php echo add_query_arg(['page_num' => $current_page + 1, 'search' => $search_term, 'search_field' => $search_field]); ?>" class="pagination-btn next-page" title="Next Page">
                                <span class="dashicons dashicons-controls-forward"></span>
                            </a>
                            <a href="<?php echo add_query_arg(['page_num' => $total_pages, 'search' => $search_term, 'search_field' => $search_field]); ?>" class="pagination-btn last-page" title="Last Page">
                                <span class="dashicons dashicons-controls-skipforward"></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div id="excel-comment-popup" title="Edit Comments"></div>
                
                <div class="page-footer">
                    <div class="footer-info">
                        <p>Total Records: <strong><?php echo number_format($total_records); ?></strong></p>
                        <p>Records with Comments: <strong><?php echo number_format(countRecordsWithComments($results)); ?></strong></p>
                        <p>Page: <strong><?php echo $current_page; ?> of <?php echo $total_pages; ?></strong></p>
                    </div>
                </div>
            </div>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
        exit;
    }
}

// Helper function to check if record has any comments
function hasAnyComments($row) {
    return !empty($row['beebe_comments']) || 
           !empty($row['aec_comments']) || 
           !empty($row['epic_comments']);
}

// Helper function to count records with comments
function countRecordsWithComments($results) {
    $count = 0;
    foreach ($results as $row) {
        if (hasAnyComments($row)) {
            $count++;
        }
    }
    return $count;
}

// Helper function to format date
function formatDate($date_string) {
    if (empty($date_string)) {
        return '-';
    }
    $date = new DateTime($date_string);
    return $date->format('M j, Y g:i A');
}

// Helper function to format date for shortcode (date only)
function formatShortcodeDate($date_string) {
    if (empty($date_string)) {
        return '-';
    }
    $date = new DateTime($date_string);
    return $date->format('M j, Y');
}
?>