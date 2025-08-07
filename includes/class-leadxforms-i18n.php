<?php

class LeadXForms_i18n {
	private $loader;

	public function __construct( $loader ) {
		$this->loader = $loader;
	}

	public function init() {
		$this->loader->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'lxform',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
