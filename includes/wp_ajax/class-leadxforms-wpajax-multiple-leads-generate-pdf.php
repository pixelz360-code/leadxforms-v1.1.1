<?php

class LeadXForms_WpAjax_MultipleLeadsGeneratePDF {

    private $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_multiple_leads_generate_pdf', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_multiple_leads_generate_pdf', $this, 'request');
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

        $ids = isset($_POST['ids']) ? json_decode($_POST['ids']) : '';

        $license_key = get_option('leadxforms_license_key');
        $url = $this->loader->api_url() . '/lead/generate_pdf';
        $response = wp_remote_post($url, [
            'sslverify' => false,
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'licensekey' => $license_key,
                'websiteurl' => lxf_get_domain(),
            ),
            'body' => json_encode([
                'ids' => $ids
            ])
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
                    'url' => $this->loader->website_link() . $body->data,
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