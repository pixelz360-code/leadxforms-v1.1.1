<?php

class LeadXForms_WpAjax_RecaptchaIntegration {

    private $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_recaptcha_integration', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_recaptcha_integration', $this, 'request');
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

        $site_key = isset($_POST['site_key']) ? sanitize_text_field($_POST['site_key']) : '';
        $secret_key = isset($_POST['secret_key']) ? sanitize_text_field($_POST['secret_key']) : '';

        if(isset($_POST['reset'])) {
            $leadxforms_reCaptcha_keys = get_option('leadxforms_reCaptcha_keys');
            if($leadxforms_reCaptcha_keys) {
                delete_option('leadxforms_reCaptcha_keys');
                $message = 'reCAPTCHA keys has been reset successfully!';
            } else {
                $message = 'The reCAPTCHA keys are currently empty!';
            }

            echo wp_send_json_success([
                'data' => [],
                'message' => __($message, 'lxform')
            ], 200);
            wp_die();
        } else {
            $errors = [];
            if(empty($site_key)) {
                $errors['site_key'] = 'The field is required.';
            }

            if(empty($secret_key)) {
                $errors['secret_key'] = 'The field is required';
            }

            if(count($errors)>0) {
                $message = 'There was an error in one or more fields. Please double-check and try again.';
                echo wp_send_json_error([
                    'errors' => $errors,
                    'message' => __($message, 'lxform') 
                ]);
                wp_die();
            } else {
                if ($this->validateReCaptcha($site_key, $secret_key)) {
                    $recaptchaArr = [
                        'site_key' => $site_key, 
                        'secret_key' => $secret_key
                    ];
                    
                    $leadxforms_reCaptcha_keys = get_option('leadxforms_reCaptcha_keys');
                    if($leadxforms_reCaptcha_keys) {
                        update_option('leadxforms_reCaptcha_keys', $recaptchaArr);
                    } else {
                        add_option('leadxforms_reCaptcha_keys', $recaptchaArr);
                    }

                    $message = 'reCAPTCHA keys has been saved successfully!';
                    echo wp_send_json_success([
                        'data' => $recaptchaArr,
                        'message' => __($message, 'lxform')
                    ], 200);
                    wp_die();
                } else {
                    $message = 'reCAPTCHA keys are invalid';
                    echo wp_send_json_error([
                        'errors' => [],
                        'message' => __($message, 'lxform') 
                    ]);
                    wp_die();
                }
            }
        }
    }

    public function validateReCaptcha($recaptcha_site_key, $recaptcha_secret_key) {
        $recaptcha_endpoint = "https://www.google.com/recaptcha/api/siteverify";
        $visitor_details = new LeadXForms_VisitorDetails();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $recaptcha_endpoint."?secret={$recaptcha_secret_key}&response=check&remoteip={$visitor_details->get_ip_address()}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $api_response = curl_exec($ch);
        curl_close($ch);

        $api_response = json_decode($api_response, true);
        if (isset($api_response['success']) && $api_response['success'] == true) {
            return true;
        } else {
            return false;
        }
    }
}