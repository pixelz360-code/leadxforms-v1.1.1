<?php

class LeadXForms_WpAjax_FormGetAll {

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
        $this->loader->add_action('wp_ajax_lxf_form_getall', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_form_getall', $this, 'request');
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

        $per_page = isset($_POST['per_page']) ? sanitize_text_field($_POST['per_page']) : false;
        
        if($per_page) {
            $paged = isset($_POST['paged']) ? sanitize_text_field($_POST['paged']) : 1;
            $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
            $current_page = max(1, $paged);
            $offset = ($current_page - 1) * $per_page;
            
            $sql = "SELECT * FROM {$this->prefix}lxform_forms";
            if (!empty($search)) {
                $sql .= " WHERE form_name LIKE '%$search%' OR form_key LIKE '%$search%'";
            }
            $sql .= " ORDER BY created_at LIMIT {$per_page} OFFSET {$offset}";
            $forms = $this->db->get_results($sql);
            if(!count($forms)) {
                echo wp_send_json_error(__('Error: Forms Not Found', 'lxform'));
                wp_die();
            }
            
            $sql = "SELECT COUNT(*) FROM {$this->prefix}lxform_forms";
            if (!empty($search)) {
                $sql .= " WHERE form_name LIKE '%$search%' OR form_key LIKE '%$search%'";
            }
            $sql .= " ORDER BY created_at";
            $total_forms = $this->db->get_var($sql);
            if(!$total_forms) {
                echo wp_send_json_error(__('Error: Forms Not Found', 'lxform'));
                wp_die();
            }
    
            $total_pages = ceil($total_forms / $per_page);
            $data = [];
            if(count($forms)>0) {
                foreach ($forms as $form) {
                    $data[] = array(
                        'ID' => $form->ID,
                        'form_name' => $form->form_name,
                        'form_key' => $form->form_key,
                        'author' => get_the_author_meta('display_name', $form->user_id),
                        'date' => $form->created_at
                    );
                }
            }
    
            $items_number = "Showing {$current_page} to ".count($data)." of {$total_forms} items";
    
            echo wp_send_json_success([
                'data' => [
                    'items' => $data,
                    'per_page' => $per_page,
                    'current_page' => $current_page,
                    'total_items' => $total_forms,
                    'total_pages' => $total_pages,
                    'items_number' => $items_number
                ],
                'message' => 'success'
            ], 200);
            wp_die();
        } else {
            $forms = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_forms ORDER BY created_at");
            if(!count($forms)) {
                echo wp_send_json_error(__('Error: Forms Not Found', 'lxform'));
                wp_die();
            }

            $data = [];
            if(count($forms)>0) {
                foreach ($forms as $form) {
                    $data[] = array(
                        'ID' => $form->ID,
                        'form_name' => $form->form_name,
                        'form_key' => $form->form_key,
                        'author' => get_the_author_meta('display_name', $form->user_id),
                        'leads' => !empty($form->leads) ? $form->leads: 0,
                        'date' => $form->created_at
                    );
                }
            }

            echo wp_send_json_success([
                'data' => $data,
                'message' => __('Success', 'lxform')
            ], 200);
            wp_die();
        }
    }
}