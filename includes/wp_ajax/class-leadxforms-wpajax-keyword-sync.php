<?php
class LeadXForms_WpAjax_KeywordSync
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
        $this->loader->add_action('wp_ajax_lxf_keyword_sync', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_keyword_sync', $this, 'request');
    }

    private function get_headers()
    {
        if (function_exists('getallheaders')) return getallheaders();
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    public function request()
    {
        $table     = $this->prefix . 'lxform_forms';

        // payload
        $raw     = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) $payload = $_POST;

        // inputs
        $form_id  = isset($payload['form_id'])  ? intval($payload['form_id']) : 0;
        $form_key = isset($payload['form_key']) ? sanitize_text_field($payload['form_key']) : '';

        $keywords = isset($payload['keywords']) && is_array($payload['keywords'])
            ? array_values(array_unique(array_map('strval', $payload['keywords'])))
            : [];

        // --- FLEXIBLE LOOKUP: id → key ---
        $form = null;
        if ($form_id > 0) {
            $form = $this->db->get_row($this->db->prepare(
                "SELECT id, settings FROM {$table} WHERE id = %d LIMIT 1",
                $form_id
            ));
        }
        if (!$form && $form_key !== '') {
            $form = $this->db->get_row($this->db->prepare(
                "SELECT id, settings FROM {$table} WHERE form_key = %s LIMIT 1",
                $form_key
            ));
        }
        if (!$form) {
            wp_send_json_error(['message' => 'Form not found', 'id' => $form_id, 'key' => $form_key], 404);
        }

        // current settings
        $settings = [];
        if (!empty($form->settings)) {
            $decoded = json_decode($form->settings, true);
            if (is_array($decoded)) $settings = $decoded;
        }
        $settings['keyword_block'] = $keywords;

        // update
        $updated = $this->db->update(
            $table,
            ['settings' => json_encode($settings)],
            ['id' => $form->id],
            ['%s'],
            ['%d']
        );
        if ($updated === false) {
            wp_send_json_error(['message' => 'DB update failed'], 500);
        }

        wp_send_json_success([
            'form_id'       => (int) $form->id,
            'keyword_block' => $keywords,
            'updated'       => (bool) $updated,
        ], 200);
    }
}
