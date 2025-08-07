<?php

class LeadXForms_MailDataFilter {
    
    private $mail;
    private $formData;

    public function set($mail, $formData) {
        $this->mail = $mail;
        $this->formData = $formData;
        return $this;
    }

    public function data() {
        if(count($this->formData)>0) {
            foreach($this->formData as $field_name => $data) {
                if(count($data)>0) {
                    foreach($data as $key => $value) {
                        if($field_name !== 'file' && $field_name !== 'checkbox-list') {
                            $this->mail->recipient = str_replace("[{$key}]", $value, $this->mail->recipient);
                            $this->mail->sender = str_replace("[{$key}]", $value, $this->mail->sender);
                            if(!empty($this->mail->replay_to)) {
                                $this->mail->replay_to = str_replace("[{$key}]", $value, $this->mail->replay_to);
                            }
        
                            if(!empty($this->mail->cc)) {
                                $this->mail->cc = str_replace("[{$key}]", $value, $this->mail->cc);
                            }
        
                            if(!empty($this->mail->bcc)) {
                                $this->mail->bcc = str_replace("[{$key}]", $value, $this->mail->bcc);
                            }
        
                            $this->mail->topic = str_replace("[{$key}]", $value, $this->mail->topic);
                            $this->mail->body = str_replace("[{$key}]", $value, $this->mail->body);
                        }

                        if($field_name === 'file') {
                            if(isset($value['url'])) {
                                if(!empty($this->mail->attachment)) {
                                    $this->mail->attachment = str_replace("[{$key}]", $value['url'], $this->mail->attachment);
                                }
                            }
                        }

                        if($field_name === 'checkbox-list') {
                            $val = implode(", ", $value);
                            $this->mail->body = str_replace("[{$key}]", $val, $this->mail->body);
                        }
                    }
                } 
            }
        }

        if(!empty($this->mail->recipient)) {
            $this->mail->recipient = json_decode($this->mail->recipient);
        }

        if(!empty($this->mail->sender)) {
            $this->mail->sender = json_decode($this->mail->sender);
        }

        if(!empty($this->mail->replay_to)) {
            $this->mail->replay_to = $this->mail->replay_to;
        }

        if(!empty($this->mail->cc)) {
            $this->mail->cc = json_decode($this->mail->cc);
        }

        if(!empty($this->mail->bcc)) {
            $this->mail->bcc = json_decode($this->mail->bcc);
        }

        if(!empty($this->mail->attachment)) {
            $this->mail->attachment = addcslashes($this->mail->attachment, '\\');
            $this->mail->attachment = json_decode($this->mail->attachment);
        }

        if(!empty($this->mail->body)) {
            $this->mail->body = $this->mail->body;
        }

        return $this->mail;
    }
}