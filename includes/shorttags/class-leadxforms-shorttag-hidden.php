<?php

class LeadXForms_ShortCode_Hidden extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'name',
        'id',
        'class',
        'value'
    ];

    protected $rules = [];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-hidden-wrap" data-name="'. $attr['name'] .'">';
            $output .= '<input type="hidden"';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-hidden '. $attr['class'] .'"';
            $output .= ($attr['value']) ? ' value="'. $attr['value'] .'"' : '';
            $output .= '>';
        $output .= '</span>';

        return $output;
    }
}