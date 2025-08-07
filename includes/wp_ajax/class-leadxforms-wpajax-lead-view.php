<?php

class LeadXForms_WpAjax_LeadView {

    private $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_lead_viewed', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_lead_viewed', $this, 'request');
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

        $license_key = get_option('leadxforms_license_key');
        $url = $this->loader->api_url() . '/lead/view/'.$id;
        $response = wp_remote_post($url, [
            'sslverify' => false,
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'licensekey' => $license_key,
                'websiteurl' => lxf_get_domain(),
            )
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $message = "Something went wrong: $error_message";
            echo wp_send_json_error(__($message, 'lxform'));
            wp_die();
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $body = json_decode($response_body);
            if($response_code == 200) {
                echo wp_send_json_success([
                    'id' => $id,
                    'message' => __($body->message, 'lxform')
                ], 200);
                wp_die();
            } else {
                echo wp_send_json_error(__($body->message, 'lxform'));
                wp_die();
            }
        }
    }
}