<?php

class LeadXForms_ShortCode_Select extends LeadXForms_ShortCode_Tag {

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

        $output = '<span class="lxform-field-warp lxform-select-wrap" data-name="'. $attr['name'] .'">';
            if($attr['label']) {
                $output .= '<label';
                $output .= ($attr['id']) ? ' for="'. $attr['id'] .'"' : '';
                $output .= '>'. $attr['label'];
                $output .= $attr['required'] ? ' <span class="text-red">*</span>' : '';
                $output .= '</label>';
            }

            $output .= '<select';
            $output .= ($attr['name']) ? ' name="'. $attr['name'] .'"' : '';
            $output .= ($attr['id']) ? ' id="'. $attr['id'] .'"' : '';
            $output .= ' class="lxform-field lxform-select '. $attr['class'] .'"';
            $output .= '>';
                if($attr['options']) {
                    $options = explode(',', $attr['options']);
                    if(count($options)) {
                        foreach($options as $option) {
                            $opt = explode('|', $option);
                            $selected = ($attr['selected'] == $opt[0]) ? 'selected' : '';
                            if(count($opt) == 1) {
                                $output .= '<option value="">'.$opt[0].'</option>';
                            }

                            if(count($opt) > 1) {
                                $output .= '<option value="'.$opt[0].'" '.$selected.'>'.$opt[1].'</option>';
                            }
                        }
                    }
                }
            $output .= '</select>';
        $output .= '</span>';

        return $output;
    }
}