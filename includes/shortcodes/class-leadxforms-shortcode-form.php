<?php

class LeadXForms_Shortcode_Form {

    private $db;
    private $prefix;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->prefix = $wpdb->prefix;
    }
	
    public function init() {
        add_shortcode(lxf_id(), [$this, 'shortcode']);
    }

    public function shortcode($atts) {
        global $wp;
        $atts = shortcode_atts(['key' => null], $atts);

        if(!empty($atts)) {
            $result = $this->db->get_results("SELECT * FROM {$this->prefix}lxform_forms WHERE form_key = '{$atts['key']}'");
            if(!count($result)) {
                return '<div class="lxf-form-error">Error: Form Not Found</div>';
            }

            $form_data = $result[0];
            $template = (new LeadXForms_FormTemplate)->set($form_data->template);
            $rules = $template->rules();
            $names = $template->names();
            $fields = $template->fields();
            $form_template = $template->output();

            wp_enqueue_style( lxf_id(), plugin_dir_url( __FILE__ ) . '../../public/css/leadxforms-public.css', [], null, 'all' );
            if(isset($fields['recaptcha'])) {
                wp_enqueue_script( lxf_id().'-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true );    
                wp_script_add_data(lxf_id().'-recaptcha', 'async', true);
                wp_script_add_data(lxf_id().'-recaptcha', 'defer', true);
            }
            wp_enqueue_script( lxf_id(), plugin_dir_url( __FILE__ ) . '../../public/js/leadxforms-public.js', [], null, true );

            wp_localize_script( lxf_id(), 'lxformData', array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            ));

            if($form_data->custom_css) {
                wp_add_inline_style( lxf_id(), $form_data->custom_css );
            }

            ob_start(); ?>
            <div class="lxform-form-wrap">
                <form action="<?php echo home_url( $wp->request ); ?>" method="post" class="lxform-form" aria-label="<?php echo $form_data->form_name; ?>" novalidate>
                    <?php wp_nonce_field( 'lxform-nonce', 'nonce' ); ?>
                    <label for="request-checker" aria-hidden="true" class="d-none"><input type="radio" name="request-checker" id="request-checker" style="display:none" value="1"></label>
                    <div class="lxform-form-innerwrap">
                        <input type="hidden" name="_key" value="<?php echo $form_data->form_key; ?>">
                        <?php echo $form_template; ?>
                    </div>
                </form>
            </div>
            <?php $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }
    }
}