<?php

class LeadXForms_ShortCode_CheckboxList extends LeadXForms_ShortCode_Tag {

    protected $attributes = [
        'label',
        'name',
        'id',
        'class',
        'options',
        'required',
    ];

    protected $rules = [
        'required'
    ];

    public function output() {
        $attr = $this->attributes();

        $output = '<span class="lxform-field-warp lxform-checkbox-list-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label class="lxform-checkbox-label">'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }

            $output .= '<span';
            $output .= ' class="lxform-listgroup lxform-checkbox-listgroup '. $attr['class'] .'"';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= '>';
            if($attr['options']) {
                $checkboxes = explode(',', $attr['options']);
                if(count($checkboxes)) {
                    foreach($checkboxes as $index => $checkbox) {
                        $val = explode('|', $checkbox);
                        $output .= '<span class="lxform-listitem lxform-checkbox-listitem">';
                            $output .= '<label>';
                                $output .= '<input type="checkbox" name="'.$attr['name'].'['.$index.']" value="'.$val[0].'">';
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