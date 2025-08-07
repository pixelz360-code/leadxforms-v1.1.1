<?php

class LeadXForms_ShortCode_Date extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
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

        $output = '<span class="lxform-field-warp lxform-date-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label';
                $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
                $output .= '>'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }
            
            $output .= '<input type="date"';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-date '. $attr['class'] .'"';
            $output .= ($attr['value']) ? ' value="'. $attr['value'] .'"' : '';
            $output .= ($attr['min']) ? ' min="'. $attr['min'] .'"' : '';
            $output .= ($attr['max']) ? ' max="'. $attr['max'] .'"' : '';
            $output .= '>';
        $output .= '</span>';

        return $output;
    }
}