<?php

class LeadXForms_WpAjax_FormSubmit
{
    use LeadXForms_Trait_Validator;

    private $db;
    private $prefix;
    private $loader;
    private $mail_context = [];

    public function __construct($loader)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->prefix = $wpdb->prefix;
        $this->loader = $loader;
    }

    private function resolve_form_id($form_data)
    {
        global $wpdb;

        if (!empty($form_data) && !empty($form_data->id)) {
            return (int) $form_data->id;
        }

        if (!empty($_POST['form_id'])) {
            return (int) $_POST['form_id'];
        }

        if (!empty($_POST['_key'])) {
            $formKey = sanitize_text_field($_POST['_key']);
            $table = $this->prefix . 'lxform_forms';
            $id = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$table} WHERE form_key = %s LIMIT 1", $formKey)
            );
            if ($id) return (int) $id;
        }

        return 0;
    }

    public function log_failed_mail($wp_error)
    {
        if (!function_exists('current_time')) return;

        global $wpdb;
        $data = is_wp_error($wp_error) ? (array) $wp_error->get_error_data() : [];
        $ctx  = $this->mail_context ?: [];

        $recipient = '';
        if (!empty($data['to'])) {
            $recipient = is_array($data['to']) ? implode(',', $data['to']) : $data['to'];
        } elseif (!empty($ctx['recipients'])) {
            $recipient = is_array($ctx['recipients']) ? implode(',', $ctx['recipients']) : $ctx['recipients'];
        }

        $subject = $data['subject'] ?? ($ctx['subject'] ?? '');
        $message = $data['message'] ?? ($ctx['message'] ?? '');

        $form_id = (int) ($ctx['form_id'] ?? 0);
        if ($form_id === 0) {
            $form_id = $this->resolve_form_id($ctx['form_data'] ?? null);
        }

        $user_id = get_current_user_id();

        $wpdb->insert(
            "{$wpdb->prefix}lxform_mail_logs",
            [
                'form_id'         => $form_id,
                'user_id'         => $user_id,
                'recipient_email' => $recipient,
                'subject'         => $subject,
                'message'         => $message,
                'status'          => 'failed',
                'error_message'   => is_wp_error($wp_error) ? $wp_error->get_error_message() : 'Unknown mail failure',
                'sent_at'         => current_time('mysql'),
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    public function init()
    {
        $this->loader->add_action('wp_ajax_lxf_form_submit', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_form_submit', $this, 'request');
    }

    public function request()
    {
        if (!$this->loader->verify_nonce('lxform-nonce')) {
            echo wp_send_json_error([
                'errors' => [],
                'message' => __('Permission Denied!', 'lxform')
            ]);
            wp_die();
        }

        //         if(!$this->loader->is_internet_on()) {
        //             $message = 'Please check your internet connection or try again later';
        //             echo wp_send_json_error([
        //                 'errors' => [],
        //                 'message' => __($message, 'lxform')
        //             ]);
        //             wp_die();
        //         }

        if (isset($_POST['request-checker'])) {
            echo wp_send_json_error([
                'errors' => [],
                'message' => __('Error: Invalid Request', 'lxform')
            ]);
            wp_die();
        }

        $form_key = (isset($_POST['_key'])) ? $_POST['_key'] : '';
        if ($form_key === '') {
            echo wp_send_json_error([
                'errors' => [],
                'message' => __('Error: Invalid Request', 'lxform')
            ]);
            wp_die();
        }

        $form_data = $this->db->get_row("SELECT * FROM {$this->prefix}lxform_forms WHERE form_key = '{$form_key}'");
        if (!$form_data) {
            echo wp_send_json_error([
                'errors' => [],
                'message' => __('Error: Form Not Found', 'lxform')
            ]);
            wp_die();
        }

        $form_template = (new LeadXForms_FormTemplate())->set($form_data->template);
        $messages = !empty($form_data->messages) ? (array) json_decode($form_data->messages) : [];
        $settings = !empty($form_data->settings) ? (array) json_decode($form_data->settings) : [
            "after_redirect" => false,
            "redirect_url" => '',
            "has_limit" => false,
            "submission_limit" => 0,
        ];
        $rules = $form_template->rules();
        $names = $form_template->names();
        $fields = $form_template->fields();

        if (isset($fields['recaptcha'])) {
            $captcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
            if ($captcha_response == '') {
                $message = 'Please verify that you are not a robot';
                echo wp_send_json_error([
                    'errors' => [],
                    'message' => __($message, 'lxform')
                ]);
                wp_die();
            }

            $validateReCaptcha = $this->validateReCaptcha($captcha_response);
            if ($validateReCaptcha === false) {
                $message = 'Error: invalid reCaptcha';
                echo wp_send_json_error([
                    'errors' => [],
                    'message' => __($message, 'lxform')
                ]);
                wp_die();
            }
        }

        $data = [];
        if (count($names) > 0) {
            foreach ($names as $name) {
                if (isset($_POST[$name])) {
                    $data[$name] = $_POST[$name];
                }

                if (isset($_FILES[$name])) {
                    $data[$name] = $_FILES[$name];
                }
            }
        }

        $mails = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_mail WHERE form_id = '{$form_data->ID}'");
        if (!count($mails)) {
            echo wp_send_json_error([
                'errors' => [],
                'message' => __('Error: Mail Data Not Found', 'lxform')
            ]);
            wp_die();
        }

        $this->set_messages($messages);
        $validate = $this->validate($data, $rules, $fields);
        if ($validate) {
            if (isset($settings['has_limit']) && $settings['has_limit'] == true) {
                session_start();
                if (!isset($_SESSION['submission_count'])) {
                    $_SESSION['submission_count'] = 0;
                }

                $_SESSION['submission_count']++;
                if ($_SESSION['submission_count'] > (int) $settings['submission_limit']) {
                    echo wp_send_json_error([
                        'errors' => [],
                        'message' => __('Submission limit exceeded', 'lxform')
                    ]);
                    wp_die();
                }

                if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 3600)) {
                    unset($_SESSION['submission_count']);
                    unset($_SESSION['LAST_ACTIVITY']);
                }
                $_SESSION['LAST_ACTIVITY'] = time();
            }

            $visitor_info = $this->get_visitor_info();
            $formlead = [];
            if (count($fields) > 0) {
                foreach ($fields as $field => $field_names) {
                    if (count($field_names) > 0) {
                        foreach ($field_names as $name) {
                            if (isset($_POST[$name])) {
                                $formlead[$field][$name] = $_POST[$name];
                            }

                            if (isset($_FILES[$name])) {
                                $file = $_FILES[$name];
                                $upload = wp_handle_upload($file, array('test_form' => false));
                                if (isset($upload['file'])) {
                                    $filename = basename($upload['file']);
                                    $filetype = wp_check_filetype($filename);
                                    $fileurl = $upload['url'];
                                    $formlead[$field][$name] = [
                                        'name' => $filename,
                                        'type' => $filetype['type'],
                                        'url' => $fileurl
                                    ];
                                } else {
                                    $message = isset($messages['upload_failed']) ? $messages['upload_failed'] : 'An unforeseen issue occurred while attempting to upload the file.';
                                    echo wp_send_json_error([
                                        'errors' => [],
                                        'message' => __($message, 'lxform')
                                    ]);
                                    wp_die();
                                }
                            }
                        }
                    }
                }
            }

            $license_key = get_option('leadxforms_license_key');
            $valid_license = false;
            if (isset($license_key) || !empty($license_key)) {
                $valid_license = $this->loader->verify_license();
            }
            $isSpam = 0;

            if (count($formlead) > 0) {
                $url = $this->loader->api_url() . '/lead/create';

                if ($valid_license) {

                    $response = wp_remote_post($url, [
                        'sslverify' => false,
                        'method' => 'POST',
                        'headers' => array(
                            'LicenseKey' => $license_key,
                            'websiteurl' => lxf_get_domain()
                        ),
                        'body' => [
                            'wpform_id' => $form_data->ID,
                            'wpform_name' => $form_data->form_name,
                            'form_data' => json_encode([
                                'data' => $formlead,
                                'visitor_info' => $visitor_info
                            ])
                        ],
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

                        if ($body->is_spam) {
                            $isSpam = 1;
                        }

                        if ($response_code !== 200) {
                            echo wp_send_json_error([
                                'errors' => [],
                                'message' => __($body->message, 'lxform')
                            ]);
                            wp_die();
                        }
                    }
                }
            }

            $send = false;
            if (count($mails) && $isSpam != 1) {
                foreach ($mails as $index => $mail) {
                    $mailData = (new LeadXForms_MailDataFilter)->set($mail, $formlead);
                    $mailObj = $mailData->data();

                    $recipients = [];
                    if (!empty($mailObj->recipient)) {
                        foreach ($mailObj->recipient as $recipient) {
                            if (!empty($recipient->name)) {
                                $recipients[] = sprintf('%s <%s>', $recipient->name, $recipient->email);
                            } else {
                                $recipients[] = sprintf('%s', $recipient->email);
                            }
                        }
                    }

                    $headers = [];
                    if ($mailObj->use_html == "1") {
                        $headers[] = 'Content-Type: text/html; charset=UTF-8';
                    }

                    if (!empty($mailObj->sender)) {
                        if (!empty($mailObj->sender[0]->name)) {
                            $headers[] = sprintf('From: %s <%s>', $mailObj->sender[0]->name, $mailObj->sender[0]->email);
                        } else {
                            $headers[] = sprintf('From: %s', $mailObj->sender[0]->email);
                        }
                    }

                    if (!empty($mailObj->cc)) {
                        foreach ($mailObj->cc as $cc) {
                            $headers[] = sprintf('Cc: %s', $cc->email);
                        }
                    }

                    if (!empty($mailObj->bcc)) {
                        foreach ($mailObj->bcc as $bcc) {
                            $headers[] = sprintf('Bcc: %s', $bcc->email);
                        }
                    }

                    if (!empty($mailObj->replay_to)) {
                        $headers[] = sprintf('Reply-To: %s', $mailObj->replay_to);
                    }

                    $subject = $mailObj->topic;
                    $message = $mailObj->body;

                    $attachments = [];
                    if (!empty($mailObj->attachment)) {
                        $upload_dir = wp_upload_dir();
                        foreach ($mailObj->attachment as $attachment) {
                            $url = $attachment->file;
                            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
                            $attachments[] = $file_path;
                        }
                    }

                    $this->mail_context = [
                        'form_id'    => (int) $this->resolve_form_id($form_data ?? null),
                        'form_data'  => $form_data ?? null,
                        'recipients' => $recipients,
                        'subject'    => $subject,
                        'message'    => $message,
                    ];

                    add_action('wp_mail_failed', [$this, 'log_failed_mail'], 10, 1);

                    $send = wp_mail($recipients, $subject, $message, $headers, $attachments);
                    remove_action('wp_mail_failed', [$this, 'log_failed_mail'], 10);

                    if ($send === false || is_wp_error($send)) {
                        $err = is_wp_error($send)
                            ? $send
                            : new WP_Error('lxform_send_failed', 'wp_mail returned false', [
                                'to' => $recipients,
                                'subject' => $subject,
                                'message' => $message
                            ]);
                        $this->log_failed_mail($err);
                    }
                }
            }

            if ($send || $isSpam == 1) {

                if (count($formlead) > 0 && $isSpam != 1) {
                    if (isset($formlead['file'])) {
                        foreach ($formlead['file'] as $key => $file) {
                            $file_path = str_replace(site_url('/'), ABSPATH, $file['url']);
                            if (file_exists($file_path)) {
                                wp_delete_file($file_path);
                            }
                        }
                    }
                }

                $redirect = 'none';
                if (
                    $valid_license &&
                    (isset($settings['after_redirect']) && $settings['after_redirect'] == true) &&
                    (isset($settings['redirect_url']) && !empty($settings['redirect_url']))
                ) {
                    $redirect = $settings['redirect_url'];
                }

                $message = isset($messages['mail_sent']) ? $messages['mail_sent'] : 'Message has been sent successfully';
                echo wp_send_json_success([
                    'redirect' => $redirect,
                    'message' => __($message, 'lxform')
                ], 200);
                wp_die();
            } else {
                $redirect = 'none';
                if (
                    $valid_license &&
                    (isset($settings['after_redirect']) && $settings['after_redirect'] == true) &&
                    (isset($settings['redirect_url']) && !empty($settings['redirect_url']))
                ) {
                    $redirect = $settings['redirect_url'];
                }

                $message = isset($messages['mail_sent']) ? $messages['mail_sent'] : 'Form submitted successfully (mail failed).';
                echo wp_send_json_success([
                    'redirect' => $redirect,
                    'message' => __($message, 'lxform')
                ], 200);
                wp_die();
            }
        } else {
            $message = isset($messages['validation_error']) ? $messages['validation_error'] : 'There was an error in one or more fields. Please double-check and try again.';
            echo wp_send_json_error([
                'errors' => $this->getErrors(),
                'message' => __($message, 'lxform')
            ]);
            wp_die();
        }
    }

    public function get_visitor_info()
    {
        $visitor_details = new LeadXForms_VisitorDetails();
        $ip = $visitor_details->get_ip_address();
        $platform = $visitor_details->get_os();
        $browser = $visitor_details->get_browser();
        $ref_url = $visitor_details->get_ref_url();
        $location = $visitor_details->get_country();

        return [
            'ip' => $ip,
            'platform' => $platform,
            'browser' => $browser,
            'ref_url' => $ref_url,
            'country' => $location['country'],
            'state' => $location['state'],
            'city' => $location['city'],
            'country_code' => $location['country_code'],
            'continent' => $location['continent']
        ];
    }

    public function validateReCaptcha($captcha_response)
    {
        $keys = get_option('leadxforms_reCaptcha_keys');
        $secret_key = ($keys) ? $keys['secret_key'] : '';
        $get_visitor = $this->get_visitor_info();
        $url = 'https://www.google.com/recaptcha/api/siteverify';

        $data = array(
            'secret' => $secret_key,
            'response' => $captcha_response,
            'remoteip' => $get_visitor['ip']
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);

        return ($response->success == false) ? false : true;
    }
}
