<?php

class LeadXForms_WpAjax_FormDelete {

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
        $this->loader->add_action('wp_ajax_lxf_form_delete', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_form_delete', $this, 'request');
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

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        if($id === '') {
            echo wp_send_json_error(__('Error: Invalid Request', 'lxform'));
            wp_die();
        }

        $form = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_forms WHERE id = {$id}");
        if(!count($form)) {
            echo wp_send_json_error(__('Error: Not Found', 'lxform'));
            wp_die();
        }

        $leads = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_leads WHERE form_id = $id");
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

            $this->db->query( "DELETE FROM {$this->prefix}lxform_leads WHERE form_id = $id" );
        }

        $this->db->delete($this->prefix.'lxform_mail', ['form_id' => $id]);
        $this->db->delete($this->prefix.'lxform_forms', ['id' => $id]);

        echo wp_send_json_success([
            'id' => $id,
            'message' => __(ucfirst($form->form_name). ' form has been deleted successfully!', 'lxform')
        ], 200);
        wp_die();
    }
}