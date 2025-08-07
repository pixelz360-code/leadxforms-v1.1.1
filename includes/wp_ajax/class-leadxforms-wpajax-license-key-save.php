<?php

class LeadXForms_WpAjax_LicenseKeySave {

    private $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_save_license_key', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_save_license_key', $this, 'request');
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

        $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
        if(isset($_POST['reset'])) {
            $leadxforms_license_key = get_option('leadxforms_license_key');
            if($leadxforms_license_key) {
                delete_option('leadxforms_license_key');
                $message = 'License key has been removed successfully!';
            } else {
                $message = 'The license key is currently unavailable.';
            }

            echo wp_send_json_success([
                'data' => [],
                'message' => __($message, 'lxform')
            ], 200);
            wp_die();
        } else {
            $errors = [];
            if(empty($license_key)) {
                $errors['license_key'] = 'The license key field is required.';
            }

            if(count($errors)>0) {
                $message = 'There was a mistake in the field. Please verify and attempt again.';
                echo wp_send_json_error([
                    'errors' => $errors,
                    'message' => __($message, 'lxform') 
                ]);
                wp_die();
            } else {
                $verify_license = $this->verify_license($license_key);
                if($verify_license->error == 0) {
                    $leadxforms_license_key = get_option('leadxforms_license_key');
                    if($leadxforms_license_key) {
                        update_option('leadxforms_license_key', $license_key);
                    } else {
                        add_option('leadxforms_license_key', $license_key);
                    }

                    echo wp_send_json_success([
                        'data' => $license_key,
                        'message' => __($verify_license->message, 'lxform')
                    ], 200);
                    wp_die();
                }
            }
        }
    }

    public function verify_license($license_key) {
        $url = $this->loader->api_url() . '/license/verify';
        $response = wp_remote_post($url, [
            'sslverify' => false,
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode([
                'license_key' => $license_key,
                'websiteurl' => lxf_get_domain(),
            ]),
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $message = "Something went wrong: $error_message";
            echo wp_send_json_error([
                'errors' => [],
                'message' => __($message, 'lxform') 
            ]);

            wp_die();
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            
            $body = json_decode($response_body);
            if($response_code == 200) {
                return $body;
            } else {
                echo wp_send_json_error([
                    'errors' => [],
                    'message' => __($body->message, 'lxform') 
                ]);
                wp_die();
            }
        }

        return (object) ['error' => 1];
    }
}