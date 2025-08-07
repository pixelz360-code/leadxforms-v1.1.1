<?php

class LeadXForms_ShortCode_ReCaptcha extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'id',
        'class',
    ];

    protected $rules = [];

    public function output() {
        $attr = $this->attributes();
        $output = '';

        $keys = get_option('leadxforms_reCaptcha_keys');
        if($keys) {
            $output = '<span class="lxform-recaptcha-wrap '. $attr['class'] .'" id="'. $attr['id'] .'">';
                $output .= '<div class="g-recaptcha" data-sitekey="'.$keys['site_key'].'"></div>';
            $output .= '</span>';
        }

        return $output;
    }
}