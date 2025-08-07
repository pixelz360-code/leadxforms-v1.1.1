<?php

class LeadXForms_ShortCode_Range extends LeadXForms_ShortCode_Tag {

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
        'required'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-range-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label';
                $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
                $output .= '>'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }
            
            $output .= '<input type="range"';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-range '. $attr['class'] .'"';
            $output .= ' min="'. $attr['min'].'"';
            $output .= ' max="'. $attr['max'].'"';
            $output .= ' value="'.($attr['value'] ? $attr['value'] : 0).'"'; 
            $output .= '>';
        $output .= '</span>';

        return $output;
    }
}