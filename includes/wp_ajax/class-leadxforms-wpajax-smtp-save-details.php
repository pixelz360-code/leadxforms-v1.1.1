<?php

class LeadXForms_WpAjax_SmtpSaveDetails {

    private $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_smtp_save_details', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_smtp_save_details', $this, 'request');
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

        $host = isset($_POST['host']) ? sanitize_text_field($_POST['host']) : '';
        $port = isset($_POST['port']) ? sanitize_text_field($_POST['port']) : '';
        $encryption = isset($_POST['encryption']) ? sanitize_text_field($_POST['encryption']) : '';
        $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';

        if(isset($_POST['reset'])) {
            $leadxforms_smtp_setting = get_option('leadxforms_smtp_setting');
            if($leadxforms_smtp_setting) {
                delete_option('leadxforms_smtp_setting');
                $message = 'SMTP setting has been reset successfully!';
            } else {
                $message = 'The SMTP credentials are currently empty.';
            }

            echo wp_send_json_success([
                'data' => [],
                'message' => __($message, 'lxform')
            ], 200);
            wp_die();
        } else {
            $errors = [];
            if(empty($host)) {
                $errors['host'] = 'The field is required.';
            }

            if(empty($port)) {
                $errors['port'] = 'The field is required';
            }

            if(empty($encryption)) {
                $errors['encryption'] = 'The field is required';
            }

            if(empty($username)) {
                $errors['username'] = 'The field is required';
            }

            if(empty($password)) {
                $errors['password'] = 'The field is required';
            }

            if(count($errors)>0) {
                $message = 'There was an error in one or more fields. Please double-check and try again.';
                echo wp_send_json_error([
                    'errors' => $errors,
                    'message' => __($message, 'lxform') 
                ]);
                wp_die();
            } else {
                if ($this->validateSmtp($host, $port, $encryption, $username, $password)) {
                    $smtpArr = [
                        'host' => $host, 
                        'port' => $port, 
                        'encryption' => $encryption, 
                        'username' => $username, 
                        'password' => $password
                    ];
                    
                    $leadxforms_smtp_setting = get_option('leadxforms_smtp_setting');
                    if($leadxforms_smtp_setting) {
                        update_option('leadxforms_smtp_setting', $smtpArr);
                    } else {
                        add_option('leadxforms_smtp_setting', $smtpArr);
                    }

                    $message = 'SMTP credentials has been saved successfully!';
                    echo wp_send_json_success([
                        'data' => $smtpArr,
                        'message' => __($message, 'lxform')
                    ], 200);
                    wp_die();
                } else {
                    $message = 'SMTP credentials are invalid';
                    echo wp_send_json_error([
                        'errors' => [],
                        'message' => __($message, 'lxform') 
                    ]);
                    wp_die();
                }
            }
        }
    }

    public function validateSmtp($host, $port, $encryption, $username, $password) {
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';

        $phpmailer = new PHPMailer\PHPMailer\PHPMailer();
        $phpmailer->isSMTP();
        $phpmailer->Host = $host;
        $phpmailer->Port = $port;
        $phpmailer->SMTPSecure = $encryption;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $username;
        $phpmailer->Password = $password;
        $phpmailer->Timeout = 5;
    
        try {
            return ($phpmailer->smtpConnect()) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
}