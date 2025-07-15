<?php
/*
Plugin Name: Excel Processing Plugin
Description: A WordPress plugin to upload, process, and display Excel data with editable comments.
Version: 1.4
Author: One Roof Design,Noida
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EXCEL_PROCESSING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXCEL_PROCESSING_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include Composer autoloader
require_once EXCEL_PROCESSING_PLUGIN_DIR . 'vendor/autoload.php';

// Include other PHP files
require_once EXCEL_PROCESSING_PLUGIN_DIR . 'includes/class-excel-data-table.php';
require_once EXCEL_PROCESSING_PLUGIN_DIR . 'includes/admin-functions.php';
require_once EXCEL_PROCESSING_PLUGIN_DIR . 'includes/frontend-functions.php';

// No session handling needed - rely on WordPress authentication

// Initialize session for AJAX requests
add_action('init', 'excel_processing_init_session_for_ajax');
function excel_processing_init_session_for_ajax() {
    // Start session if not already started
    if (!session_id()) {
        session_start();
    }
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'excel_processing_activate');
register_deactivation_hook(__FILE__, 'excel_processing_deactivate');

function excel_processing_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'excel_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        invoice_id varchar(50) DEFAULT '' NOT NULL,
        dispatch_id varchar(50) DEFAULT '' NOT NULL,
        date_time_of_service datetime DEFAULT NULL,
        patient_full_name varchar(100) DEFAULT '' NOT NULL,
        billable_service_code varchar(50) DEFAULT '' NOT NULL,
        origin_name varchar(100) DEFAULT '' NOT NULL,
        origin_address text DEFAULT '' NOT NULL,
        destination_name varchar(100) DEFAULT '' NOT NULL,
        destination_address text DEFAULT '' NOT NULL,
        mileage float DEFAULT 0 NOT NULL,
        price_invoiced float DEFAULT 0 NOT NULL,
        amount_received float DEFAULT 0 NOT NULL,
        current_amount_due float DEFAULT 0 NOT NULL,
        billing_notes text DEFAULT '' NOT NULL,
        beebe_comments text DEFAULT '' NOT NULL,
        aec_comments text DEFAULT '' NOT NULL,
        epic_comments text DEFAULT '' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Create custom user role
    add_role('excel_editor', __('Excel Editor'), [
        'read' => true,
        'edit_excel_comments' => true,
    ]);

    // Register rewrite rules and flush
    excel_processing_rewrite_rules();
    flush_rewrite_rules();
}

function excel_processing_deactivate() {
    // Flush rewrite rules on deactivation to clean up
    flush_rewrite_rules();
}

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'excel_processing_enqueue_admin_assets');
function excel_processing_enqueue_admin_assets($hook) {
    if ($hook !== 'toplevel_page_excel-processing' && $hook !== 'excel-processing_page_excel-processing-guide') {
        return;
    }
    wp_enqueue_style('excel-processing-admin', EXCEL_PROCESSING_PLUGIN_URL . 'assets/css/excel-processing-admin.css', [], '1.4');
    wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', [], '1.12.1');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('excel-processing-admin', EXCEL_PROCESSING_PLUGIN_URL . 'assets/js/excel-processing-admin.js', ['jquery'], '1.4', true);
    wp_enqueue_script('excel-processing-popup', EXCEL_PROCESSING_PLUGIN_URL . 'assets/js/excel-processing-popup.js', ['jquery', 'jquery-ui-dialog'], '1.4', true);
    wp_localize_script('excel-processing-popup', 'excelProcessing', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('excel_comments_nonce'),
    ]);
}

add_action('wp_enqueue_scripts', 'excel_processing_enqueue_frontend_assets');
function excel_processing_enqueue_frontend_assets() {
    global $wp_query;
    if (isset($wp_query->query_vars['excel_comments_all'])) {
        wp_enqueue_style('excel-processing-frontend', EXCEL_PROCESSING_PLUGIN_URL . 'assets/css/excel-processing-frontend.css', [], '1.4');
        wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', [], '1.12.1');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('excel-processing-frontend', EXCEL_PROCESSING_PLUGIN_URL . 'assets/js/excel-processing-frontend.js', ['jquery', 'jquery-ui-dialog'], '1.4', true);
        wp_enqueue_script('excel-processing-popup', EXCEL_PROCESSING_PLUGIN_URL . 'assets/js/excel-processing-popup.js', ['jquery', 'jquery-ui-dialog'], '1.4', true);
        wp_localize_script('excel-processing-popup', 'excelProcessing', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('excel_comments_nonce'),
        ]);
    }
}

// Handle delete action via AJAX
add_action('wp_ajax_excel_processing_delete', 'excel_processing_delete_records');
function excel_processing_delete_records() {
    check_ajax_referer('excel_processing_delete_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'excel_data';
    $record_ids = isset($_POST['record_ids']) ? array_map('intval', (array)$_POST['record_ids']) : [];

    if (!empty($record_ids)) {
        $placeholders = implode(',', array_fill(0, count($record_ids), '%d'));
        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN ($placeholders)", $record_ids));
        wp_send_json_success('Records deleted successfully');
    } else {
        wp_send_json_error('No records selected');
    }
}
?>