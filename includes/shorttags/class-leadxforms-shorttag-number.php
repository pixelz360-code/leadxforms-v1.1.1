<?php

class LeadXForms_ShortCode_Number extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
        'placeholder',
        'value',
        'required',
        'min',
        'max'
    ];

    protected $rules = [
        'required',
        'min',
        'max'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-number-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label';
                $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
                $output .= '>'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }
            
            $output .= '<input type="number"';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-number '. $attr['class'] .'"';
            $output .= ($attr['placeholder']) ? ' placeholder="'. $attr['placeholder'] .'"' : '';
            $output .= ($attr['value']) ? ' value="'. $attr['value'] .'"' : '';
            $output .= ($attr['min']) ? ' min="'. $attr['min'] .'"' : '';
            $output .= ($attr['max']) ? ' max="'. $attr['max'] .'"' : '';
            $output .= '>';
        $output .= '</span>';

        return $output;
    }
}