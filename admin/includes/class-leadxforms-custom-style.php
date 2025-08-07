<?php

class LeadXforms_CustomStyle {
    private $loader;

	public function __construct( $loader ) {
		$this->loader = $loader;
	}

	public function init() {
		$this->loader->add_action( 'admin_head', $this, 'custom_style' );
	}
    
    public function custom_style() { ?>
        <style>
            .lxf-update-adminlink {
                background-color: #008744;
                color: #ffffff !important;
                font-weight: 600;
                display: block !important;
            }

            .lxf-update-adminlink:is(:hover, :focus) {
                background-color: #008744 !important;
                box-shadow: none !important;
            }
        </style>
    <?php 
    }
}