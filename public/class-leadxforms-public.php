<?php

class LeadXForms_Public {
	private $loader;

	public function __construct( $loader ) {
		$this->loader = $loader;
	}

	public function init() {
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );
	}

	public function enqueue_styles() {
		wp_enqueue_style( lxf_id(), plugin_dir_url( __FILE__ ) . 'css/'.lxf_id().'-public.css', array(), lxf_version(), 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( lxf_id(), plugin_dir_url( __FILE__ ) . 'js/'.lxf_id().'-public.js', array( 'jquery' ), lxf_version(), true );
		wp_localize_script( lxf_id(), lxf_id() . '_data', [
			'has_license' => $this->loader->has_license(),
		]);
	}
}
