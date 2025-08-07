<?php

class LeadXForms_ShortCode_Checkbox extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
        'value',
        'required'
    ];

    protected $rules = [
        'required'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-checkbox-wrap" data-name="'. $attr['name'] .'">';
            $output .= '<label';
            $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
            $output .= '>';
                $output .= '<input type="checkbox" ';
                $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
                $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
                $output .= ' class="lxform-field lxform-checkbox '. $attr['class'] .'"';
                $output .= ($attr['value']) ? ' value="'. $attr['value'] .'"' : '';
                $output .= '>';
                if($attr['label']) {
                    $output .= '<span>'.$attr['label'];
                    $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                    $output .= '</span>';
                }
            $output .= '</label>';
        $output .= '</span>';

        return $output;
    }
}