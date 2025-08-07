<?php

class LeadXForms_WpAjax_FormCreate
{

    private $db;
    private $prefix;
    private $loader;

    public function __construct($loader)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->prefix = $wpdb->prefix;
        $this->loader = $loader;
    }

    public function init()
    {
        $this->loader->add_action('wp_ajax_lxf_form_create', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_form_create', $this, 'request');
    }

    public function generateRandomKey()
    {
        $number = mt_rand(100, 999) . date('hisYdm');
        if ($this->randomKeyExists($number)) {
            return $this->generateRandomKey();
        }
        return $number;
    }

    public function randomKeyExists($number)
    {
        $table_name = $this->prefix . 'lxform_forms';
        $query = $this->db->prepare("SELECT COUNT(*) FROM {$table_name} WHERE form_key = %d", $number);
        return $this->db->get_var($query);
    }

    public function request()
    {
        if (!$this->loader->verify_nonce('lxform-nonce')) {
            echo wp_send_json_error(__('Permission Denied!', 'lxform'));
            wp_die();
        }

        // if(!$this->loader->is_internet_on()) {
        //     $message = 'Please check your internet connection or try again later';
        //     echo wp_send_json_error(__($message, 'lxform'));
        //     wp_die();
        // }



        $form_name = isset($_POST['form_name']) ? sanitize_text_field($_POST['form_name']) : '';
        $template = isset($_POST['template']) ? wp_unslash($_POST['template']) : '';
        $custom_css = isset($_POST['css']) ? wp_unslash($_POST['css']) : '';
        $mails = isset($_POST['mails']) ? json_decode(wp_unslash($_POST['mails'])) : '';
        $mail2 = isset($_POST['mail2']) ? $_POST['mail2'] : 0;
        $messages = isset($_POST['messages']) ? json_decode(wp_unslash($_POST['messages'])) : '';
        $settings = isset($_POST['settings']) ? json_decode(wp_unslash($_POST['settings'])) : '';

        $form_key = $this->generateRandomKey();
        $user_id = get_current_user_id();

        $this->db->insert($this->prefix . 'lxform_forms', [
            'user_id' => $user_id,
            'form_name' => $form_name,
            'form_key' => $form_key,
            'template' => $template,
            'custom_css' => $custom_css,
            'settings' => (!empty($settings)) ? json_encode($settings) : null,
            'messages' => (!empty($messages)) ? json_encode($messages) : null,
        ]);

        $form_id = $this->db->insert_id;

        if (isset($_POST['settings'])) {
            $settings = json_decode(stripslashes($_POST['settings']), true);

            if (
                isset($settings['keyword_block']) &&
                is_array($settings['keyword_block']) &&
                count($settings['keyword_block']) > 0
            ) {
                $url = $this->loader->api_url() . '/keyword/updateOrCreate';
                $license_key = get_option('leadxforms_license_key');

                $data = [
                    "form_id" => $form_id,
                    "form_name" => $form_name,
                    "setting" => $_POST['settings'],
                    "user_id" => $user_id
                ];

                $headers = [
                    'LicenseKey: ' . $license_key,
                    'websiteurl: ' . lxf_get_domain(),
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: application/json'
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Curl error: ' . curl_error($ch);
                }
                curl_close($ch);
            }
        }

        if (isset($_POST['settings'])) {
            $laravel_url = $this->loader->api_url() . '/customer-forms/store';
            $license_key = get_option('leadxforms_license_key');

            $payload = [
                'form_id'     => $form_id,
                'form_name'   => sanitize_text_field($_POST['form_name']),
                'form_key'    => $form_key,
                'template'    => isset($_POST['template']) ? wp_unslash($_POST['template']) : '',
                'custom_css'  => isset($_POST['css']) ? wp_unslash($_POST['css']) : '',
                'settings'    => isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '',
                'website_url' => site_url(),
            ];

            $args = [
                'body' => $payload,
                'headers' => [
                    'LicenseKey'   => $license_key,
                    'websiteurl'   => lxf_get_domain(),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'timeout' => 15,
            ];

            $response = wp_remote_post($laravel_url, $args);
        }

        if (count($mails)) {
            foreach ($mails as $index => $mail) {
                if ($index > 1) break;
                if ($mail2 == 0 && $index == 1) continue;

                $this->db->insert($this->prefix . 'lxform_mail', [
                    'form_id' => $form_id,
                    'sender' => (count($mail->mail->sender) > 0) ? json_encode($mail->mail->sender) : null,
                    'recipient' => (count($mail->mail->recipient) > 0) ? json_encode($mail->mail->recipient) : null,
                    'replay_to' => ($mail->mail->replyTo !== '') ? $mail->mail->replyTo : null,
                    'topic' => ($mail->mail->topic !== '') ? $mail->mail->topic : null,
                    'cc' => (count($mail->mail->cc) > 0) ? json_encode($mail->mail->cc) : null,
                    'bcc' => (count($mail->mail->bcc) > 0) ? json_encode($mail->mail->bcc) : null,
                    'body' => ($mail->mail->body !== '') ? $mail->mail->body : null,
                    'use_html' => $mail->mail->use_html,
                    'attachment' => (count($mail->mail->attachment) > 0) ? json_encode($mail->mail->attachment) : null
                ]);
            }
        }

        echo wp_send_json_success([
            'id' => $form_id,
            'message' => __(ucfirst($form_name) . ' form has been created successfully!', 'lxform')
        ], 200);
        wp_die();
    }
}
