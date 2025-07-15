<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Excel_Data_Table extends WP_List_Table {
    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'invoice_id' => 'Invoice ID',
            'dispatch_id' => 'Dispatch ID',
            'date_time_of_service' => 'Date/Time of Service',
            'patient_full_name' => 'Patient Name',
            'billable_service_code' => 'Service Code',
            'origin_name' => 'Origin Name',
            'origin_address' => 'Origin Address',
            'destination_name' => 'Destination Name',
            'destination_address' => 'Destination Address',
            'mileage' => 'Mileage',
            'price_invoiced' => 'Price Invoiced',
            'amount_received' => 'Amount Received',
            'current_amount_due' => 'Current Amount Due',
            'billing_notes' => 'Billing Notes',
            'beebe_comments' => 'Beebe Comments',
            'aec_comments' => 'AEC Comments',
            'epic_comments' => 'Epic Comments',
            'delete' => 'Delete',
        ];
    }

    public function get_sortable_columns() {
        return [
            'invoice_id' => ['invoice_id', false],
            'date_time_of_service' => ['date_time_of_service', false],
            'patient_full_name' => ['patient_full_name', false],
        ];
    }

    public function get_bulk_actions() {
        return [
            'delete' => 'Delete',
        ];
    }

    public function column_default($item, $column_name) {
        return esc_html($item[$column_name]);
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="record[]" value="%s" />', $item['id']);
    }

    public function column_billing_notes($item) {
        return esc_html($item['billing_notes']);
    }

    public function column_beebe_comments($item) {
        return $this->render_editable_column($item, 'beebe_comments');
    }

    public function column_aec_comments($item) {
        return $this->render_editable_column($item, 'aec_comments');
    }

    public function column_epic_comments($item) {
        return $this->render_editable_column($item, 'epic_comments');
    }

    public function column_delete($item) {
        return sprintf('<a href="%s" class="delete-record" data-id="%s" style="color: red;">Delete</a>',
            wp_nonce_url(admin_url('admin.php?page=excel-processing&action=delete&record=%s', $item['id']), 'excel_delete_nonce_' . $item['id']),
            $item['id']
        );
    }

    private function render_editable_column($item, $column_name) {
        $value = esc_textarea($item[$column_name]);
        return '<span class="edit-comment" data-id="' . esc_attr($item['id']) . '" data-field="' . esc_attr($column_name) . '" data-value="' . esc_attr($value) . '"><span class="dashicons dashicons-edit"></span></span>';
    }

    public function process_bulk_action() {
        if ($this->current_action() === 'delete' && check_admin_referer('bulk-' . $this->_args['plural'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'excel_data';
            $record_ids = isset($_GET['record']) ? array_map('intval', (array)$_GET['record']) : [];

            if (!empty($record_ids)) {
                $placeholders = implode(',', array_fill(0, count($record_ids), '%d'));
                $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN ($placeholders)", $record_ids));
                wp_redirect(add_query_arg(['deleted' => count($record_ids)], admin_url('admin.php?page=excel-processing')));
                exit;
            }
        }
    }

    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'excel_data';
        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->process_bulk_action();

        $query = "SELECT * FROM $table_name";
        $search = !empty($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        if ($search) {
            $query .= $wpdb->prepare(
                " WHERE invoice_id LIKE %s OR patient_full_name LIKE %s OR billing_notes LIKE %s OR beebe_comments LIKE %s OR aec_comments LIKE %s OR epic_comments LIKE %s",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $orderby = !empty($_GET['orderby']) ? esc_sql($_GET['orderby']) : 'id';
        $order = !empty($_GET['order']) ? esc_sql($_GET['order']) : 'asc';
        if (!empty($orderby) && !empty($order)) {
            $query .= " ORDER BY $orderby $order";
        }

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);

        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $query .= " LIMIT $per_page OFFSET $offset";

        $this->items = $wpdb->get_results($query, ARRAY_A);
    }
}
?>