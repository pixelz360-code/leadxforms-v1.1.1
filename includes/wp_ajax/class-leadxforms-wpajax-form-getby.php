<?php

class LeadXForms_WpAjax_FormGetBy {

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
        $this->loader->add_action('wp_ajax_lxf_form_getby', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_form_getby', $this, 'request');
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

        $form_id = isset($_POST['form_id']) ? sanitize_text_field($_POST['form_id']) : '';
        if($form_id === '') {
            echo wp_send_json_error(__('Error: Invalid Request', 'lxform'));
            wp_die();
        }

        $result = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_forms WHERE id = {$form_id}");
        if(!count($result)) {
            echo wp_send_json_error(__('Error: Form Not Found', 'lxform'));
            wp_die();
        }

        $form_data = $result[0];

        $mails = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_mail WHERE form_id = {$form_id}");
        if(!count($mails)) {
            echo wp_send_json_error(__('Error: Mail Not Found', 'lxform'));
            wp_die();
        }

        $mail_data = $mails;

        echo wp_send_json_success([
            'data' => [
                'form' => $form_data,
                'mails' => $mail_data
            ],
            'message' => __('Success', 'lxform')
        ], 200);
        wp_die();
    }
}