<?php

class LeadXForms_WpAjax_LeadGetAll {

    private $loader;

    public function __construct($loader) {
        $this->loader = $loader;
    }

    public function init() {
        $this->loader->add_action('wp_ajax_lxf_lead_getall', $this, 'request');
        $this->loader->add_action('wp_ajax_nopriv_lxf_lead_getall', $this, 'request');
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
        $form_id = isset($_POST['form']) ? sanitize_text_field($_POST['form']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $view = isset($_POST['view']) ? sanitize_text_field($_POST['view']) : '';
        $license_key = get_option('leadxforms_license_key');
        
        $url = $this->loader->api_url() . '/lead';
        if($per_page) {
            $paged = isset($_POST['paged']) ? sanitize_text_field($_POST['paged']) : 1;
            $url .= '?perpage=' . $per_page . '&page='. $paged . '&wpform_id=' . $form_id;
            if($status != '') {
                $url .= '&status='.$status;
            }

            if($view != '') {
                $url .= '&is_viewed='. ($view == 'view' ? 1 : 0);
            }
            
            $response = wp_remote_post($url, [
                'sslverify' => false,
                'method' => 'GET',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'licensekey' => $license_key,
                    'websiteurl' => lxf_get_domain(),
                )
            ]);
    
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $message = "Something went wrong: $error_message";
                echo wp_send_json_error(__($message, 'lxform'));
                wp_die();
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
                $body = json_decode($response_body);
                if($response_code == 200) {
                    $data = [];
                    if(count($body->data)>0) {
                        foreach ($body->data as $row) {
                            $data[] = array(
                                'ID' => $row->id,
                                'uuid' => $row->uuid,
                                'form_id' => $row->wpform_id,
                                'form_data' => (!empty($row->form_data)) ? json_decode($row->form_data) : '',
                                'is_viewed' => $row->is_viewed ? 1 : 0,
                                'status' => $row->status,
                                'created_at' => $row->created_at,
                                'updated_at' => $row->updated_at,
                            );
                        }
                    }

                    $paginate = $body->paginate;
                    $items_number = "Showing {$paginate->from} to {$paginate->to} of {$paginate->count} entries";
                    echo wp_send_json_success([
                        'data' => [
                            'items' => $data,
                            'per_page' => $per_page,
                            'current_page' => $paginate->current_page,
                            'total_items' => $paginate->count,
                            'total_pages' => ceil($paginate->total / $per_page),
                            'items_number' => $items_number
                        ],
                        'message' => 'success'
                    ], 200);
                    wp_die();
                } else {
                    echo wp_send_json_error(__($body->message, 'lxform'));
                    wp_die();
                }
            }
        } else {
            $response = wp_remote_post($url, [
                'sslverify' => false,
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'licensekey' => $license_key,
                    'websiteurl' => lxf_get_domain(),
                )
            ]);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $message = "Something went wrong: $error_message";
                echo wp_send_json_error(__($message, 'lxform'));
                wp_die();
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
                $body = json_decode($response_body);
                if($response_code == 200) {
                    echo wp_send_json_success([
                        'data' => $body->data,
                        'message' => __($body->message, 'lxform')
                    ], 200);
                    wp_die();
                } else {
                    echo wp_send_json_error(__($body->message, 'lxform'));
                    wp_die();
                }
            }
        }
    }
}