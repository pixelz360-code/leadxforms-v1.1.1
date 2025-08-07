<?php

class LeadXForms_SmtpConnect {
	private $loader;

	public function __construct( $loader ) {
		$this->loader = $loader;
	}

	public function init() {
		$this->loader->add_action( 'phpmailer_init', $this, 'phpmailer_smtp' );
        $this->loader->add_action( 'wp_mail_failed', $this, 'mail_failed' );
	}

	public function phpmailer_smtp( $phpmailer ) {
		$smtp = get_option('leadxforms_smtp_setting');
        if($smtp) {
            $phpmailer->SMTPDebug = 0;
            $phpmailer->isSMTP();
            $phpmailer->Host = $smtp['host'];
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $smtp['username'];
            $phpmailer->Password = $smtp['password'];
            $phpmailer->SMTPSecure = $smtp['encryption'];
            $phpmailer->Port = $smtp['port'];
        }
	}

    public function mail_failed( $error ) {
        ?>
            <script>
                console.log('<?php echo json_encode($error); ?>');
            </script>
        <?php
    }
}
