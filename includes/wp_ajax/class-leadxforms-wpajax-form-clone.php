<?php

class LeadXForms_WpAjax_FormClone {

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
        $this->loader->add_action('wp_ajax_lxf_form_clone', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_form_clone', $this, 'request');
    }

    public function generateRandomKey() {
        $number = mt_rand(100, 999) . date('hisYdm');
        if ($this->randomKeyExists($number)) {
            return $this->generateRandomKey();
        }
        return $number;
    }

    public function randomKeyExists($number) {
        $table_name = $this->prefix . 'lxform_forms';
        $query = $this->db->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE form_key = %d", $number );
        return $this->db->get_var( $query );
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
            echo wp_send_json_error(__('Error: Form Not Found', 'lxform'));
            wp_die();
        }

        $mails = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_mail WHERE form_id = {$id}");
        if(!count($mails)) {
            echo wp_send_json_error(__('Error: Mail Not Found', 'lxform'));
            wp_die();
        }

        $form_key = $this->generateRandomKey();
        $user_id = get_current_user_id();

        $this->db->insert($this->prefix . 'lxform_forms', [
            'user_id' => $user_id,
            'form_name' => $form[0]->form_name .' - Clone #'. rand(1000, 9999),
            'form_key' => $form_key,
            'template' => $form[0]->template,
            'custom_css' => $form[0]->custom_css,
        ]);
        $form_id = $this->db->insert_id;

        if(count($mails)) {
            foreach($mails as $mail) {
                $this->db->insert($this->prefix . 'lxform_mail', [
                    'form_id' => $form_id,
                    'sender' => $mail->sender,
                    'recipient' => $mail->recipient,
                    'replay_to' => $mail->replyTo,
                    'topic' => $mail->topic,
                    'cc' => $mail->cc,
                    'bcc' => $mail->bcc,
                    'body' => $mail->body,
                    'use_html' => $mail->use_html,
                    'attachment' => $mail->attachment
                ]);
            }
        }

        echo wp_send_json_success([
            'id' => $id,
            'message' => __(ucfirst($form->form_name). ' form has been cloned successfully!', 'lxform')
        ], 200);
        wp_die();
    }
}