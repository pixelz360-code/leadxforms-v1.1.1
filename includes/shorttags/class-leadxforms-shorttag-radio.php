<?php

class LeadXForms_ShortCode_Radio extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
        'options',
        'selected',
        'required',
    ];

    protected $rules = [
        'required'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-radio-list-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label class="lxform-radio-label">'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }

            $output .= '<span';
            $output .= ' class="lxform-listgroup lxform-radio-listgroup '. $attr['class'] .'"';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= '>';
            if($attr['options']) {
                $radios = explode(',', $attr['options']);
                if(count($radios)) {
                    foreach($radios as $index => $radio) {
                        $val = explode('|', $radio);
                        $checked = ($attr['selected'] == $val[0]) ? 'checked' : '';
                        $output .= '<span class="lxform-listitem lxform-radio-listitem">';
                            $output .= '<label>';
                                $output .= '<input type="radio" name="'.$attr['name'].'" value="'.$val[0].'" '.$checked.'>';
                                if(count($val) == 1) {
                                    $output .= '<span>'.$val[0].'</span>';
                                }

                                if(count($val) == 2) {
                                    $output .= '<span>'.$val[1].'</span>';
                                }    
                            $output .= '</label>';
                        $output .= '</span>';
                    }
                }
            }
        $output .= '</span>';
        
        return $output;
    }
}