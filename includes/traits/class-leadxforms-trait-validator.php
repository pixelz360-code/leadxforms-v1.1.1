<?php

trait LeadXForms_Trait_Validator {

    private $errors = [];
    private $fields = [];
    private $messages = [
        'required_field' => 'The field is required',
        'min_char_limit' => 'The field should have a minimum characters of %s',
        'max_char_limit' => 'The field should have a maximum characters of %s',
        'invalid_email' => 'The field should be a valid email address',
        'invalid_tel' => 'The field should be a valid phone number',
        'invalid_url' => 'The field should be a valid url',
        'invalid_number' => 'The field not a valid integer number',
        'min_num_limit' => 'The field should have a minimum length of %s',
        'max_num_limit' => 'The field should have a maximum length of %s',
        'min_date_limit' => 'The field should have a minimum date of %s',
        'max_date_limit' => 'The field should have a maximum date of %s',
        'invalid_file_type' => 'Only the following extensions are allowed: %s',
        'file_too_large' => 'The maximum upload size should be %s bytes',
        'block_url' => 'No URLs allowed.'
    ];

    public function set_messages($messages) {
        if(count($messages)) {
            $this->messages = $messages;   
        }
    }

    public function validate(Array $data, Array $rules, Array $fields) {
        $this->fields = $fields;
        $valid = true;
		if(count($rules)) {
            foreach ($rules as $item => $ruleset) {
				$ruleset = explode('|', $ruleset);
				foreach ($ruleset as $rule) {
                    if($rule !== '') {
                        $pos = strpos($rule, ':');
                        $parameter = '';
                        if($pos !== false) {
                            $parameter = substr($rule, $pos + 1);
                            $rule = substr($rule, 0, $pos);
                        }
    
                        $methodName = 'validate' . ucfirst($rule);
                        $value = isset($data[$item]) ? $data[$item] : null;
                        if(method_exists($this, $methodName)) {
                        	$this->$methodName($item, $value, $parameter) or $valid = false;
                        }
                    }
				}
			}
		}

        return $valid;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getField($item) {
        $field = '';
        if(count($this->fields)>0) {
            foreach($this->fields as $key => $value) {
                if(in_array($item, $value)) {
                    $field = $key;
                    break;
                }
            }
        }

        return $field;
    }

    public function validateRequired($item, $value, $parameter) 
    {
        $field = $this->getField($item);
        if($field !== 'file') {
            if ($value === '' || $value === NULL) {
                $error = $this->messages['required_field'];
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        } else {
            if(!isset($value["tmp_name"]) || (isset($value["tmp_name"]) && ! file_exists($value["tmp_name"]))) {
                $error = $this->messages['required_field'];
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }

    	return true;
    }

    public function validateEmail($item, $value, $parameter) 
    {
    	if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $error = $this->messages['invalid_email'];
            $this->errors[$item][] = __($error, 'lxform');
    		return false;
    	}

    	return true;
    }

    public function validateUrl($item, $value, $parameter) 
    {
    	if (! filter_var($value, FILTER_SANITIZE_URL)) {
            $error = $this->messages['invalid_url'];
            $this->errors[$item][] = __($error, 'lxform');
    		return false;
    	}

    	return true;
    }

    public function validateNumeric($item, $value, $parameter) 
    {
    	if (!empty($value) && ! filter_var($value, FILTER_VALIDATE_INT)) {
            $error = $this->messages['invalid_number'];
            $this->errors[$item][] = __($error, 'lxform');
    		return false;
    	}

    	return true;
    }

    public function validateTel($item, $value, $parameter) 
    {
    	if (! filter_var($value, FILTER_SANITIZE_NUMBER_INT)) {
            $error = $this->messages['invalid_tel'];
            $this->errors[$item][] = __($error, 'lxform');
    		return false;
    	}

    	return true;
    }

    public function validateMin($item, $value, $parameter) 
    {
        $field = $this->getField($item);
        if($field == 'text') {
            if(strlen($value) < $parameter) {
                $error = sprintf($this->messages['min_char_limit'], $parameter);
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }

        if($field == 'number' || $field == 'range') {
            if((Int) $value < $parameter) {
                $error = sprintf($this->messages['min_num_limit'], $parameter);
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }

        if($field == 'date') {
            if($value < $parameter) {
                $error = sprintf($this->messages['min_date_limit'], $parameter);
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }
        
        return true;
    }

    public function validateMax($item, $value, $parameter) 
    {
        $field = $this->getField($item);
        if($field == 'text') {
            if(strlen($value) > $parameter) {
                $error = sprintf($this->messages['max_char_limit'], $parameter);
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }

        if($field == 'number' || $field == 'range') {
            if((Int) $value > $parameter) {
                $error = sprintf($this->messages['max_num_limit'], $parameter);
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }

        if($field == 'date') {
            if($value > $parameter) {
                $error = sprintf($this->messages['max_date_limit'], $parameter);
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }
        
        return true;
    }

    public function validateSize($item, $value, $parameter) {
        $field = $this->getField($item);
        if($field == 'file') {
            $parameter = ($parameter > 10000000) ? 10000000 : $parameter;
            if(isset($value["size"]) && $value["size"] > $parameter) {
                $error = sprintf($this->messages['file_too_large'], $parameter);
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }

        return true;
    }

    public function validateMimes($item, $value, $parameter) {
        $field = $this->getField($item);
        if($field == 'file') {
            if(isset($value['name'])) {
                $ext = pathinfo($value["name"], PATHINFO_EXTENSION);
                $arr = explode(",", $parameter);
                if(count($arr)>0 && !in_array($ext, $arr)) {
                    $error = sprintf($this->messages['invalid_file_type'], implode(", ", $arr));
                    $this->errors[$item][] = __($error, 'lxform');
                    return false;
                }
            }
        }

        return true;
    }

    public function validateBlock_url($item, $value, $parameter) {
        $field = $this->getField($item);
        if($field == 'textarea') {
            if( strpos($value, 'http') !== false || strpos($value, 'https') !== false || strpos($value, 'www.') !== false ) {
                $error = $this->messages['block_url'];
                $this->errors[$item][] = __($error, 'lxform');
                return false;
            }
        }

        return true;
    }
}