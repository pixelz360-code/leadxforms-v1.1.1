<?php

class LeadXForms_WpAjax_FormBulkDelete {

    private $db;
    private $prefix;
    private $loader;

    public function __construct($loader) {
        global $wpdb;
        $this->db = $wpdb;
        $this->prefix = $wpdb->prefix;
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_form_bulkdelete', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_form_bulkdelete', $this, 'request');
    }

    public function request() {
        if ( !$this->loader->verify_nonce( 'lxform-nonce' ) ) {
            echo wp_send_json_error(__('Permission Denied!', 'lxform'));
            wp_die();
        }

        // if(!$this->loader->is_internet_on()) {
        //     $message = 'Please check your internet connection or try again later';
        //     echo wp_send_json_error(__($message, 'lxform'));
        //     wp_die();
        // }

        $ids = isset($_POST['ids']) ? json_decode(wp_unslash($_POST['ids'])) : '';

        if($ids === '' || !count($ids)) {
            echo wp_send_json_error(__('Error: Invalid Request', 'lxform'));
            wp_die();
        }

        $str_ids = implode(', ', array_map('absint', $ids));

        $leads = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_leads WHERE form_id IN ($str_ids)");
        if(count($leads)) {
            foreach($leads as $lead) {
                $data = json_decode($lead->form_data);
                if(!empty($data)) {
                    foreach($data->data as $field => $item) {
                        foreach($item as $key => $value) {
                            if(isset($value->url)) {
                                $path = WP_CONTENT_DIR . explode('wp-content', $value->url)[1];
                                if (file_exists($path)) {
                                    unlink($path);
                                }
                            }
                        }
                    }
                }
            }

            $this->db->query( "DELETE FROM {$this->prefix}lxform_leads WHERE form_id IN ($str_ids)" );
        }

        $this->db->query( "DELETE FROM {$this->prefix}lxform_mail WHERE form_id IN ($str_ids)" );
        $this->db->query( "DELETE FROM {$this->prefix}lxform_forms WHERE id IN ($str_ids)" );

        echo wp_send_json_success([
            'ids' => $ids,
            'message' => __('Selected Items has been deleted successfully!', 'lxform')
        ], 200);
        wp_die();
    }
}