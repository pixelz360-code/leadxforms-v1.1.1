<?php

defined( 'WP_UNINSTALL_PLUGIN' ) or die();
$migration = new LeadXForms_Database_Migration();
$migration->rollback();