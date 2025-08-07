<?php

class LeadXForms_Database_Forms {

    public function up($wpdb) {
        return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lxform_forms (
            ID BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            form_name VARCHAR(255) NOT NULL,
            form_key VARCHAR(255) NOT NULL,
            template LONGTEXT NULL,
            custom_css LONGTEXT NULL,
            settings LONGTEXT NULL,
            messages LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
        ) {$wpdb->get_charset_collate()};";
    }

    public function down($wpdb) {
        return "DROP TABLE IF EXISTS {$wpdb->prefix}lxform_forms";
    }
}