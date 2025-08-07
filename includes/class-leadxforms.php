<?php

class LeadXForms {
	protected $loader;
	protected $dependencies = [
		'includes/class-leadxforms-loader.php',
		'includes/class-leadxforms-i18n.php',
		'includes/class-leadxforms-visitordetails.php',
		'includes/class-leadxforms-maildata-filter.php',
		'includes/class-leadxforms-smtp-connect.php',

		'includes/database/class-leadxforms-database-forms.php',
		'includes/database/class-leadxforms-database-mail.php',
		'includes/database/class-leadxforms-database-migration.php',
		'includes/shorttags/class-leadxforms-shorttag-tag.php',
		'includes/shorttags/class-leadxforms-shorttag-checkbox-list.php',
        'includes/shorttags/class-leadxforms-shorttag-appointment.php',
		'includes/shorttags/class-leadxforms-shorttag-checkbox.php',
		'includes/shorttags/class-leadxforms-shorttag-date.php',
		'includes/shorttags/class-leadxforms-shorttag-email.php',
		'includes/shorttags/class-leadxforms-shorttag-file.php',
		'includes/shorttags/class-leadxforms-shorttag-hidden.php',
		'includes/shorttags/class-leadxforms-shorttag-number.php',
		'includes/shorttags/class-leadxforms-shorttag-radio.php',
		'includes/shorttags/class-leadxforms-shorttag-range.php',
		'includes/shorttags/class-leadxforms-shorttag-recaptcha.php',
		'includes/shorttags/class-leadxforms-shorttag-select.php',
		'includes/shorttags/class-leadxforms-shorttag-submit.php',
		'includes/shorttags/class-leadxforms-shorttag-tel.php',
		'includes/shorttags/class-leadxforms-shorttag-text.php',
		'includes/shorttags/class-leadxforms-shorttag-textarea.php',
		'includes/shorttags/class-leadxforms-shorttag-url.php',

		'includes/class-leadxforms-formtemplate.php',

		'includes/wp_ajax/class-leadxforms-wpajax-form-preview.php',
		'includes/wp_ajax/class-leadxforms-wpajax-form-getall.php',
		'includes/wp_ajax/class-leadxforms-wpajax-form-clone.php',
		'includes/wp_ajax/class-leadxforms-wpajax-form-create.php',
		'includes/wp_ajax/class-leadxforms-wpajax-form-getby.php',
		'includes/wp_ajax/class-leadxforms-wpajax-form-update.php',
		'includes/wp_ajax/class-leadxforms-wpajax-form-delete.php',
		'includes/wp_ajax/class-leadxforms-wpajax-form-bulkdelete.php',
		'includes/wp_ajax/class-leadxforms-wpajax-lead-getall.php',
		'includes/wp_ajax/class-leadxforms-wpajax-lead-view.php',
		'includes/wp_ajax/class-leadxforms-wpajax-lead-generate-pdf.php',
		'includes/wp_ajax/class-leadxforms-wpajax-lead-generate-excel.php',
		'includes/wp_ajax/class-leadxforms-wpajax-lead-update-status.php',
		'includes/wp_ajax/class-leadxforms-wpajax-lead-delete.php',
		'includes/wp_ajax/class-leadxforms-wpajax-lead-bulkdelete.php',
		'includes/wp_ajax/class-leadxforms-wpajax-multiple-leads-generate-pdf.php',
		'includes/wp_ajax/class-leadxforms-wpajax-multiple-leads-generate-excel.php',
		'includes/wp_ajax/class-leadxforms-wpajax-pages-getall.php',
		'includes/wp_ajax/class-leadxforms-wpajax-recaptcha-integration.php',
		'includes/wp_ajax/class-leadxforms-wpajax-recaptcha-get-keys.php',
		'includes/wp_ajax/class-leadxforms-wpajax-smtp-save-details.php',
		'includes/wp_ajax/class-leadxforms-wpajax-smtp-get-details.php',
		'includes/wp_ajax/class-leadxforms-wpajax-license-key-save.php',
		'includes/wp_ajax/class-leadxforms-wpajax-license-key-get.php',

		'includes/shortcodes/class-leadxforms-shortcode-form.php',

		'includes/traits/class-leadxforms-trait-validator.php',

		'admin/class-leadxforms-admin.php',
		'admin/includes/class-leadxforms-adminpages.php',
		'admin/includes/class-leadxforms-settings-links.php',
		'admin/includes/class-leadxforms-custom-style.php',

		'public/class-leadxforms-public.php',
		'public/includes/wp_ajax/class-leadxforms-wpajax-form-submit.php',
        'includes/wp_ajax/class-leadxforms-wpajax-form-blockedIP.php',
	];

	protected $services = [
		'LeadXForms_i18n',
		'LeadXForms_SmtpConnect',
		'LeadXForms_WpAjax_FormPreview',
		'LeadXForms_WpAjax_FormGetAll',
		'LeadXForms_WpAjax_FormClone',
		'LeadXForms_WpAjax_FormCreate',
		'LeadXForms_WpAjax_FormGetBy',
		'LeadXForms_WpAjax_FormUpdate',
		'LeadXForms_WpAjax_FormDelete',
		'LeadXForms_WpAjax_FormBulkDelete',
        'LeadXForms_WpAjax_FormBlockedIP',
		'LeadXForms_WpAjax_LeadGetAll',
		'LeadXForms_WpAjax_LeadView',
		'LeadXForms_WpAjax_LeadGeneratePDF',
		'LeadXForms_WpAjax_LeadGenerateExcel',
		'LeadXForms_WpAjax_LeadUpdateStatus',
		'LeadXForms_WpAjax_LeadDelete',
		'LeadXForms_WpAjax_LeadBulkDelete',
		'LeadXForms_WpAjax_MultipleLeadsGeneratePDF',
		'LeadXForms_WpAjax_MultipleLeadsGenerateExcel',
		'LeadXForms_WpAjax_PagesGetAll',
		'LeadXForms_WpAjax_RecaptchaIntegration',
		'LeadXForms_WpAjax_RecaptchaGetKeys',
		'LeadXForms_WpAjax_SmtpSaveDetails',
		'LeadXForms_WpAjax_SmtpGetDetails',
		'LeadXForms_WpAjax_LicenseKeySave',
		'LeadXForms_WpAjax_LicenseKeyGet',

		'LeadXForms_Shortcode_Form',

		'LeadXForms_Database_Migration',

		'LeadXForms_Admin',
		'LeadXforms_AdminPages',
		'LeadXforms_SettingsLinks',
		'LeadXforms_CustomStyle',
		'LeadXForms_Public',
		'LeadXForms_WpAjax_FormSubmit',
        'LeadXForms_WpAjax_FormBlockedIP'
	];

	public function __construct() {
		$this->load_dependencies();
		$this->loader = new LeadXForms_Loader();

		$this->load_services();
	}




    private function load_dependencies() {
		if(count($this->dependencies)) {
            foreach($this->dependencies as $path) {
                require_once(plugin_dir_path( dirname( __FILE__ ) ) . $path);
            }
        }
	}

	private function load_services() {
		$services = $this->services;
        if(count($services)) {
            foreach($services as $class) {
                $service = new $class($this->loader);
                if(method_exists($service, 'init')) {
                    $service->init();
                }
            }
        }
	}

	public function run() {
		$this->loader->run();
	}

	public function get_loader() {
		return $this->loader;
	}
}
