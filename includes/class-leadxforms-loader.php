<?php

class LeadXForms_Loader {
	protected $actions;
	protected $filters;
	protected $api_url = 'http://127.0.0.1:8000/api/v1';
	protected $website_link = 'http://127.0.0.1:8000/';
	
	public function __construct() {
		$this->actions = array();
		$this->filters = array();
	}

	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);
		return $hooks;
	}

	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}

	public function api_url() {
		return $this->api_url;
	}

	public function website_link() {
		return $this->website_link;
	}

	public function has_license() {
		$leadxforms_license_key = get_option('leadxforms_license_key');
		if(isset($leadxforms_license_key) && !empty($leadxforms_license_key)) {
			return 1;
		}

		return 0;
	}

	public function has_reCaptcha_keys() {
		$leadxforms_reCaptcha_keys = get_option('leadxforms_reCaptcha_keys');
		if(isset($leadxforms_reCaptcha_keys) && !empty($leadxforms_reCaptcha_keys)) {
			return 1;
		}

		return 0;
	}

	public function has_license_errors() {
		$license_key = get_option('leadxforms_license_key');
		if(isset($license_key) && !empty($license_key)) {
			$url = $this->api_url() . '/license/verify';
			$response = wp_remote_post($url, [
				'sslverify' => false,
				'method' => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => json_encode([
					'license_key' => $license_key,
					'websiteurl' => lxf_get_domain(),
				]),
			]);

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				return __("Something went wrong: $error_message", "lxform");
			} else {
				$response_code = wp_remote_retrieve_response_code($response);
            	$response_body = wp_remote_retrieve_body($response);
				$body = json_decode($response_body);
				if($response_code !== 200) {
					return __($body->message, 'lxform');
				}
			}
		}

		return false;
    }

	public function verify_license() {
		$license_key = get_option('leadxforms_license_key');
		if(isset($license_key) && !empty($license_key)) {
			$url = $this->api_url() . '/license/verify';
			$response = wp_remote_post($url, [
				'sslverify' => false,
				'method' => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => json_encode([
					'license_key' => $license_key,
					'websiteurl' => lxf_get_domain(),
				])
			]);
	
			if (!is_wp_error($response)) {
				$response_code = wp_remote_retrieve_response_code($response);
				if($response_code == 200) {
					return 1;
				}
			}
		}

        return 0;
    }

	public function is_internet_on() {
        $response = @file_get_contents('https://www.google.com/');
        return ($response !== false);
    }

	public function verify_nonce($nonce) {
		return wp_verify_nonce( $_POST['nonce'], $nonce ) ? true : false;
    }
}
