<?php

class LeadXForms_ShortCode_Tag {
    protected $short_tag = null;
    protected $rules = [];
    protected $attributes = [];

    public function set($short_tag) {
        $this->short_tag = $short_tag;
        $this->set_attributes();
        $this->set_rules();
        return $this;
    }

    public function rules() {
        return $this->rules;
    }

    public function attributes() {
        return $this->attributes;
    }

    protected function search_attr($search) {
        preg_match('/\b('.$search.')\b(?:="([^"]*)")?/', $this->short_tag, $vls);
        return (count($vls) == 3) ? $vls[2] : (count($vls) == 2 ? $vls[1] : false);
    }

    protected function set_attributes() {
        $array = [];
        foreach($this->attributes as $attr) {
            $array[$attr] = $this->search_attr($attr);
        }
        $this->attributes = $array;
    }

    protected function set_rules() {
        $array = [];
        foreach($this->rules as $rule) {
            $array[$rule] = $this->search_attr($rule);
        }
        $this->rules = $array;
    }
}