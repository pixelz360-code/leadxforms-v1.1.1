<?php

class LeadXForms_ShortCode_Tel extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
        'placeholder',
        'value',
        'required',
    ];

    protected $rules = [
        'required'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-tel-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label';
                $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
                $output .= '>'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }

            $output .= '<input type="tel"';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-tel '. $attr['class'] .'"';
            $output .= ($attr['placeholder']) ? ' placeholder="'. $attr['placeholder'] .'"' : '';
            $output .= ($attr['value']) ? ' value="'. $attr['value'] .'"' : '';
            $output .= '>';
        $output .= '</span>';

        return $output;
    }
}