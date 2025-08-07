<?php

class LeadXForms_WpAjax_PagesGetAll {

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
        $this->loader->add_action('wp_ajax_lxf_pages_getall', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_pages_getall', $this, 'request');
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
        
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'order' => 'ASC',
            'order_by' => 'title'
        ]);

        $data = [];
        if($pages) {
            foreach($pages as $page) {
                $data[] = [
                    'ID' => $page->ID,
                    'title' => $page->post_title,
                    'url' => get_the_permalink($page->ID),
                ];
            }
        }

        echo wp_send_json_success([
            'data' => $data,
            'message' => __('Success', 'lxform')
        ], 200);
        wp_die();
    }
}