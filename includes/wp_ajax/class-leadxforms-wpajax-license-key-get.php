<?php

class LeadXForms_WpAjax_LicenseKeyGet {

    private $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_get_license_key', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_get_license_key', $this, 'request');
    }

    public function request() {
        if ( !$this->loader->verify_nonce( 'lxform-nonce' ) ) {
            echo wp_send_json_error([
                'errors' => [],
                'message' => __('Permission Denied!', 'lxform')
            ]);
            wp_die();
        }

        // if(!$this->loader->is_internet_on()) {
        //     $message = 'Please check your internet connection or try again later';
        //     echo wp_send_json_error([
        //         'errors' => [],
        //         'message' => __($message, 'lxform')
        //     ]);
        //     wp_die();
        // }

        $leadxforms_license_key = get_option('leadxforms_license_key');
        if($leadxforms_license_key) {
            $data = [
                "license_key" => $leadxforms_license_key
            ];
            
            $message = 'Success!';
        } else {
            $data = [];
            $message = 'Empty!';
        }

        echo wp_send_json_success([
            'data' => $data,
            'message' => __($message, 'lxform')
        ], 200);
        wp_die();
    }
}