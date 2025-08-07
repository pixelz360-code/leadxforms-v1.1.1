<?php

class LeadXForms_Database_Mail {

    public function up($wpdb) {
        return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lxform_mail (
            ID BIGINT(20) NOT NULL AUTO_INCREMENT,
            form_id BIGINT(20) UNSIGNED NOT NULL,
            sender VARCHAR(255) NOT NULL,
            recipient TEXT NOT NULL,
            replay_to VARCHAR(255) NULL,
            cc TEXT NULL,
            bcc TEXT NULL,
            topic VARCHAR(255) NOT NULL,
            body LONGTEXT  NULL,
            use_html INT(10) DEFAULT 0,
            attachment TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID)
        ) {$wpdb->get_charset_collate()};";
    }

    public function down($wpdb) {
        return "DROP TABLE IF EXISTS {$wpdb->prefix}lxform_mail";
    }
}