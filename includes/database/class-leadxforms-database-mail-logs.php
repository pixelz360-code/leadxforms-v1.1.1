<?php
class LeadXForms_Database_Mail_Logs
{
    public function up($wpdb)
    {
        return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}lxform_mail_logs (
        ID BIGINT(20) NOT NULL AUTO_INCREMENT,
        form_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        recipient_email VARCHAR(255) NOT NULL,        
        subject VARCHAR(255) DEFAULT NULL,
        message LONGTEXT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'sent',
        error_message TEXT DEFAULT NULL,
        sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (ID)
    ) {$wpdb->get_charset_collate()};";
    }


    public function down($wpdb)
    {
        return "DROP TABLE IF EXISTS {$wpdb->prefix}lxform_mail_logs";
    }
}
