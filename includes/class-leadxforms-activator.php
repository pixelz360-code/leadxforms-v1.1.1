<?php

class LeadXForms_Activator {
	public static function activate() {
		flush_rewrite_rules();
		$migration = new LeadXForms_Database_Migration();
		$migration->migrate();
	}
}
