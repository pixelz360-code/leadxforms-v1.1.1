<?php

class LeadXForms_Admin {
	private $loader;

	public function __construct( $loader ) {
		$this->loader = $loader;
	}



	public function init() {
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
	}


	public function enqueue_styles( $hook ) {
		if(
			$hook === 'toplevel_page_lxform' ||
            $hook === 'forms_page_lxform-create' ||
            $hook === 'forms_page_lxform-leads' ||
            $hook === 'forms_page_lxform-settings'
        ) {
			wp_enqueue_style(lxf_id().'-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css', array(), null, 'all' );
            wp_enqueue_style(lxf_id().'-lineawesome', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css', array(), null, 'all' );
            wp_enqueue_style(lxf_id(), plugin_dir_url( __FILE__ ) . 'css/'.lxf_id().'-admin.css', array(), null, 'all' );
		}
	}

	public function enqueue_scripts( $hook ) {
		wp_enqueue_script(lxf_id().'-ace', 'https://cdn.jsdelivr.net/npm/ace-builds@1.31.1/src-min-noconflict/ace.min.js', array(), null, true);
		wp_enqueue_script(lxf_id(), plugin_dir_url( __FILE__ ) . 'js/'.lxf_id().'-admin.js', array( 'jquery' ), null, true );
		wp_localize_script(lxf_id(), 'lxformData', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'lxform-nonce' ),
			'plugin_name' => lxf_name(),
			'plugin_dir' => plugin_dir_url( __FILE__ ),
			'plugin_version' => lxf_version(),
			'admin_url' => admin_url(),
			'admin_email' => get_bloginfo('admin_email'),
			'site_title' => get_bloginfo('name'),
			'site_url' => get_bloginfo('url'),
			'isReCaptchaIntegrated' => $this->loader->has_reCaptcha_keys(),
			'has_license' => $this->loader->has_license(),
			'license_expired' => $this->loader->verify_license(),
			'has_license_errors' => $this->loader->has_license_errors()
		));
	}
}
