<?php

class LeadXForms_WpAjax_FormPreview {

    private $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_form_preview', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_form_preview', $this, 'request');
    }

    public function request() {
        if ( !$this->loader->verify_nonce( 'lxform-nonce' ) ) {
            echo wp_send_json_error(__('Permission Denied!', 'lxform'));
            wp_die();
        }

        $form_data = isset( $_POST['form_data'] ) ? wp_unslash( $_POST['form_data'] ) : '';
        $template = (new LeadXForms_FormTemplate)->set($form_data)->output();
        
        ob_start();
        echo $template;
        $output = ob_get_contents();
        ob_end_clean();

        echo wp_send_json_success([
            'data' => $output,
            'message' => __('Success', 'lxform')
        ], 200);
        wp_die();
    }
}