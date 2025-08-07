<?php

class LeadXForms_ShortCode_Textarea extends LeadXForms_ShortCode_Tag {
    
    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
        'placeholder',
        'value',
        'required',
        'block_url'
    ];

    protected $rules = [
        'required',
        'block_url'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-textarea-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label';
                $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
                $output .= '>'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }
            
            $output .= '<textarea';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-textarea '. $attr['class'] .'"';
            $output .= ($attr['placeholder']) ? ' placeholder="'. $attr['placeholder'] .'"' : '';
            $output .= ' cols="40" rows="10">';
            $output .= ($attr['value']) ? $attr['value'] : '';
            $output .= '</textarea>';
        $output .= '</span>';

        return $output;
    }
}