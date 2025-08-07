<?php

class LeadXforms_AdminPages {
    private $loader;

	public function __construct( $loader ) {
		$this->loader = $loader;
	}

	public function init() {
		$this->loader->add_action( 'admin_menu', $this, 'register_admin_menu' );
		$this->loader->add_filter( 'admin_body_class', $this, 'body_class' );
	}
    
    public function register_admin_menu() {
        add_menu_page(
            __( lxf_name(), 'lxform' ),
            'Forms',
            'manage_options',
            'lxform',
            [$this, 'forms_callback'],
            // 'dashicons-email',
            plugin_dir_url( dirname(__FILE__) ) . 'images/icon.png',
            26
        );

        add_submenu_page(
            'lxform',
            lxf_name(),
            'All Forms',
            'manage_options',
            'lxform',
            [$this, 'forms_callback'],
        );

        add_submenu_page(
            'lxform',
            'Create - '. lxf_name(),
            'Add New',
            'manage_options',
            'lxform-create',
            [$this, 'forms_callback'],
        );

        add_submenu_page(
            null,
            'Edit Form - '. lxf_name(),
            'Edit Form',
            'manage_options',
            'lxform&action=lxform-edit',
            [$this, 'forms_callback'],
        );

        if($this->loader->has_license() && $this->loader->verify_license()) {
            add_submenu_page(
                'lxform',
                'Leads - '. lxf_name(),
                'Leads',
                'manage_options',
                'lxform-leads',
                [$this, 'forms_callback'],
            );
        }

        add_submenu_page(
            'lxform',
            'Settings - '. lxf_name(),
            'Settings',
            'manage_options',
            'lxform-settings',
            [$this, 'forms_callback'],
        );

        add_submenu_page(
            'lxform',
            'Upgrade Pro - '. lxf_name(),
            'Upgrade Pro',
            'manage_options',
            $this->loader->website_link(),
            '',
        );
    }

    public function forms_callback() {
        return lxf_view('app');
    }

    public function body_class( $classes ) {
        $screen = get_current_screen();

        if ( get_plugin_page_hook( 'lxform', '' ) === $screen->id ) {
            $classes .= ' lxf-body lxf-listing';
        } elseif ( get_plugin_page_hook( 'lxform-create', 'lxform' ) === $screen->id ) {
            $classes .= ' lxf-body lxf-create';
        } elseif ( get_plugin_page_hook( 'lxform-leads', 'lxform' ) === $screen->id ) {
            $classes .= ' lxf-body lxf-leads';
        } elseif ( get_plugin_page_hook( 'lxform-settings', 'lxform' ) === $screen->id ) {
            $classes .= ' lxf-body lxf-settings';
        }
    
        return $classes;
    }
}