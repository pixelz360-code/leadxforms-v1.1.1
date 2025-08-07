<?php

class LeadXforms_SettingsLinks {
    private $loader;

	public function __construct( $loader ) {
		$this->loader = $loader;
	}

	public function init() {
		$this->loader->add_filter( 'plugin_action_links_leadxforms/leadxforms.php', $this, 'settings_links' );
	}
    
    public function settings_links( $links ) {

        $url = esc_url(add_query_arg(
            'page',
            'lxform-settings',
            get_admin_url() . 'admin.php'
        ));

        $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';

        $get_license_link = "<a href='". $this->loader->website_link() ."' style='color: #2197f5; font-weight: 700;'>" . __( 'Upgrade Pro' ) . '</a>';

        array_unshift($links, $settings_link);
        array_unshift($links, $get_license_link);
        return $links;

    }
}