<?php

class LeadXForms_ShortCode_Submit extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'text',
        'id',
        'class',
    ];

    protected $rules = [];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-submit">';
            $output .= '<button type="submit"';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-submit-btn '. $attr['class'] .'"';
            $output .= '>';
            $output .= ($attr['text']) ? $attr['text'] : '';
            $output .= '</button>';
        $output .= '</span>';

        return $output;
    }
}