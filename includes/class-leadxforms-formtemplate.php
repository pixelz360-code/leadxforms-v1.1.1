<?php

class LeadXForms_FormTemplate {

    protected $template = '';
    protected $short_tags = [];
    protected $tags = [];
    protected $rules = [];
    protected $names = [];
    protected $fields = [];

    protected $modules = [
        "hidden" => 'LeadXForms_ShortCode_Hidden',
        "text" => 'LeadXForms_ShortCode_Text',
        "email" => 'LeadXForms_ShortCode_Email',
        "url" => 'LeadXForms_ShortCode_Url',
        "tel" => 'LeadXForms_ShortCode_Tel',
        "number" => 'LeadXForms_ShortCode_Number',
        "range" => 'LeadXForms_ShortCode_Range',
        "textarea" => 'LeadXForms_ShortCode_Textarea',
        "date" => 'LeadXForms_ShortCode_Date',
        "appointment" => 'LeadXForms_ShortCode_Appointment',
        "select" => 'LeadXForms_ShortCode_Select',
        "checkbox" => 'LeadXForms_ShortCode_Checkbox',
        "checkbox-list" => 'LeadXForms_ShortCode_CheckboxList',
        "radio" => 'LeadXForms_ShortCode_Radio',
        "file" => 'LeadXForms_ShortCode_File',
        "recaptcha" => 'LeadXForms_ShortCode_ReCaptcha',
        "submit" => 'LeadXForms_ShortCode_Submit'
    ];

    public function set($template) {
        $this->template = $template;
        $this->tags = array_keys($this->modules);
        $this->short_tags = $this->get_short_tags();
        $this->replacement();
        return $this;
    }

    public function get_short_tags() {
        $tags = implode('|', $this->tags);
        $array = [];
        preg_match_all('/\[('.$tags.')\s(.*?)\]/', $this->template, $array, PREG_SET_ORDER);
        return $array;
    }

    public function rules() {
        $rules = [];
        if($this->rules) {
            foreach($this->rules as $name => $rule) {
                $str = '';
                if(isset($rule['required']) && !empty($rule['required'])) {
                    $str .= 'required|';
                }

                if(isset($rule['email']) && !empty($rule['email'])) {
                    $str .= 'email|';
                }

                if(isset($rule['url']) && !empty($rule['url'])) {
                    $str .= 'url|';
                }

                if(isset($rule['tel']) && !empty($rule['tel'])) {
                    $str .= 'tel|';
                }

                if(isset($rule['numeric']) && !empty($rule['numeric'])) {
                    $str .= 'numeric|';
                }
                
                if(isset($rule['block_url']) && !empty($rule['block_url'])) {
                    $str .= 'block_url|';
                }

                if(isset($rule['minlength']) && ($rule['minlength'] !== '' && $rule['minlength'] !== false)) {
                    $str .= 'min:'.$rule['min'].'|';
                }

                if(isset($rule['maxlength']) && ($rule['maxlength'] !== '' && $rule['maxlength'] !== false)) {
                    $str .= 'max:'.$rule['max'].'|';
                }

                if(isset($rule['min']) && ($rule['min'] !== '' && $rule['min'] !== false)) {
                    $str .= 'min:'.$rule['min'].'|';
                }

                if(isset($rule['max']) && ($rule['max'] !== '' && $rule['max'] !== false)) {
                    $str .= 'max:'.$rule['max'].'|';
                }

                if(isset($rule['size'])) {
                    if($rule['size'] !== '' && $rule['size'] !== false) {
                        if($rule['size'] > 10000000) {
                            $str .= 'size:10000000|';
                        } else {
                            $str .= 'size:'.$rule['size'].'|';
                        }
                    } else {
                        $str .= 'size:10000000|';
                    }
                }

                if(isset($rule['type']) && ($rule['type'] !== '' && $rule['type'] !== false)) {
                    $str .= 'mimes:'.$rule['type'].'|';
                }

                $rules[$name] = rtrim($str, "|"); 
            }

        }
        
        return $rules;
    }

    public function names() {
        return $this->names;
    }

    public function fields() {
        return $this->fields;
    }

    public function replacement() {
        $short_tags = $this->short_tags;
        $output = '';
        if(count($short_tags)) {
            foreach($short_tags as $short_tag) {
                if(isset($short_tag[1]) && in_array($short_tag[1], $this->tags)) {
                    $module = $this->modules[$short_tag[1]];
                    if(class_exists($module)) {
                        $class = (new $module)->set($short_tag[0]);
                        $attr = $class->attributes();
                        if($short_tag[1] == 'recaptcha') {
                            $attr['name'] = 'recaptcha';
                        }
                        
                        if(isset($attr['name'])) {
                            $this->names[] = $attr['name'];
                            $this->fields[$short_tag[1]][] = $attr['name'];

                            $this->rules[$attr['name']] = $class->rules();
                            if($short_tag[1] == 'email') {
                                $this->rules[$attr['name']]['email'] = 'email';
                            }

                            if($short_tag[1] == 'url') {
                                $this->rules[$attr['name']]['url'] = 'url';
                            }

                            if($short_tag[1] == 'tel') {
                                $this->rules[$attr['name']]['tel'] = 'tel';
                            }

                            if($short_tag[1] == 'number' || $short_tag[1] == 'range') {
                                $this->rules[$attr['name']]['numeric'] = 'numeric';
                            }
                        }

                        $output = $class->output();
                        $this->template = str_replace($short_tag[0], $output, $this->template);
                    }
                }
            }
        }

        return $output;
    }

    public function output() {
        return $this->template;
    }
}