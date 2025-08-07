<?php

class LeadXForms_ShortCode_Text extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
        'placeholder',
        'value',
        'required',
        'minlength',
        'maxlength'
    ];

    protected $rules = [
        'required',
        'minlength',
        'maxlength'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-text-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label';
                $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
                $output .= '>'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }
            
            $output .= '<input type="text"';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-text '. $attr['class'] .'"';
            $output .= ($attr['placeholder']) ? ' placeholder="'. $attr['placeholder'] .'"' : '';
            $output .= ($attr['value']) ? ' value="'. $attr['value'] .'"' : '';
            $output .= ($attr['minlength']) ? ' minlength="'. $attr['minlength'] .'"' : '';
            $output .= ($attr['maxlength']) ? ' maxlength="'. $attr['maxlength'] .'"' : '';
            $output .= '>';
        $output .= '</span>';

        return $output;
    }
}