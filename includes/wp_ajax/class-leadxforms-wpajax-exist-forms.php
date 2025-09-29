<?php

class LeadXForms_WpAjax_Exist_Forms
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
        $this->loader->add_action('wp_ajax_lxf_exist_forms', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_exist_forms', $this, 'request');
    }

    public function request()
    {
        $license_key = $this->get_license_key_from_headers();

        if (!$license_key) {
            wp_send_json_error(['message' => 'License key is required'], 401);
            wp_die();
        }

        $stored_license_key = get_option('leadxforms_license_key');

        if (!$stored_license_key || $stored_license_key !== $license_key) {
            wp_send_json_error(['message' => 'Invalid license key'], 401);
            wp_die();
        }

        $forms = $this->get_all_forms_from_db();

        if ($forms === false) {
            wp_send_json_error(['message' => 'Failed to fetch forms from database'], 500);
            wp_die();
        }

        wp_send_json_success([
            'data' => $forms,
            'message' => 'Forms retrieved successfully'
        ], 200);
        wp_die();
    }

    private function get_license_key_from_headers()
    {
        $headers = getallheaders();

        if (isset($headers['LicenseKey'])) {
            return sanitize_text_field($headers['LicenseKey']);
        }

        if (isset($headers['licensekey'])) {
            return sanitize_text_field($headers['licensekey']);
        }

        if (isset($_GET['license_key'])) {
            return sanitize_text_field($_GET['license_key']);
        }

        if (isset($_SERVER['HTTP_LICENSEKEY'])) {
            return sanitize_text_field($_SERVER['HTTP_LICENSEKEY']);
        }

        if (isset($_SERVER['HTTP_LICENSE_KEY'])) {
            return sanitize_text_field($_SERVER['HTTP_LICENSE_KEY']);
        }

        return null;
    }

    private function get_website_url_from_headers()
    {
        $headers = getallheaders();

        if (isset($headers['websiteurl'])) {
            return sanitize_text_field($headers['websiteurl']);
        }

        return lxf_get_domain();
    }

    private function get_all_forms_from_db()
    {
        $forms = $this->db->get_results("
            SELECT 
                ID,
                user_id,
                form_name,
                form_key,
                template,
                custom_css,
                settings,
                messages,
                created_at,
                updated_at
            FROM {$this->prefix}lxform_forms 
            ORDER BY created_at DESC
        ", ARRAY_A);

        if ($this->db->last_error) {
            error_log('MySQL Error: ' . $this->db->last_error);
            return false;
        }

        if (empty($forms)) {
            return [];
        }

        foreach ($forms as &$form) {
            if (!empty($form['template'])) {
                $form['template'] = json_decode($form['template'], true) ?: $form['template'];
            }
            if (!empty($form['settings'])) {
                $form['settings'] = json_decode($form['settings'], true) ?: $form['settings'];
            }
            if (!empty($form['messages'])) {
                $form['messages'] = json_decode($form['messages'], true) ?: $form['messages'];
            }
        }

        return $forms;
    }
}
