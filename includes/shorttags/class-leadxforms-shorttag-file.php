<?php

class LeadXForms_ShortCode_File extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
        'required',
        'type',
        'size'
    ];

    protected $rules = [
        'required',
        'type',
        'size'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-file-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label';
                $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
                $output .= '>'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }
            
            $output .= '<input type="file"';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-file '. $attr['class'] .'"';
            $output .= '>';
        $output .= '</span>';

        return $output;
    }
}