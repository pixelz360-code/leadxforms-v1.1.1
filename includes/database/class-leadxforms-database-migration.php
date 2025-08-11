<?php

class LeadXForms_Database_Migration {

    protected $migrations = [
        LeadXForms_Database_Forms::class,
        LeadXForms_Database_Mail::class,
        LeadXForms_Database_Mail_Logs::class
    ];

    public function init_method($method) {
        global $wpdb;
        $wpdb->hide_errors();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php');

        if(count($this->migrations)) {
            foreach($this->migrations as $class) {
                $migration = new $class();
                if(method_exists($migration, $method)) {
                    $sql = $migration->$method($wpdb);
                    dbDelta($sql);
                }
            }
        }
    }
    
    public function migrate() {
        $this->init_method('up');
    }

    public function rollback() {
        $this->init_method('down');
    }
}