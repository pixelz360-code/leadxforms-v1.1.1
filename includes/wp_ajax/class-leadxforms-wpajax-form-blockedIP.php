<?php

class LeadXForms_WpAjax_FormBlockedIP
{

    private $db;
    private $prefix;
    private $loader;


    public function __construct($loader)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->prefix = $wpdb->prefix;
        $this->loader = $loader;
    }


    public function formInit()
    {
        if (isset($_POST['email']) && $_POST['fd_d']) {
            $this->request();
        }
        $ip = get_ip_address();
        $apiUrl = apiUrl();
        $blockedUser = 0;
        $domain = get_full_url();
        $api_url = "$apiUrl/track-ip/0/$ip?domain=$domain";
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            return 'API request failed: ' . $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['data'])) {
            $blockedUser = 1;
        }

        ob_start();
        if ($blockedUser == 1) {

            ?>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: left;
                    background-color: #f9f9f9;
                    padding: 100px;
                }

                .container {
                    max-width: 600px;
                    margin: auto;
                    background: white;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }

                .logo {
                    margin: 0 auto 10px;
                    width: 250px;
                }

                h1 {
                    font-size: 22px;
                    color: #2c3e50;
                    margin-bottom: 10px;
                }

                h2 {
                    font-size: 18px;
                    color: #d9534f;
                }

                p {
                    font-size: 14px;
                    color: #555;
                    margin-bottom: 15px;
                }

                .error {
                    color: red;
                    font-weight: bold;
                }

                input {
                    padding: 10px;
                    width: calc(100% - 22px);
                    margin: 10px 0;
                    border: 1px solid #ccc;
                    border-radius: 3px;
                    margin-bottom: 15px;
                }

                button {
                    background-color: #0073aa;
                    color: white;
                    padding: 10px 15px;
                    border: none;
                    cursor: pointer;
                    border-radius: 3px;
                }

                button:hover {
                    background-color: #005f8d;
                }

                .footer a {
                    text-decoration: none;
                    color: #0073aa;
                }

                .footer a:hover {
                    text-decoration: underline;
                }
            </style>
            <div class="container">
                <a href="https://leadxforms.com" target="_blank">
                    <img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/images/logo.png'; ?>"
                         alt="LeadxForm Logo" class="logo">
                </a>
                <h1>Your access to this site has been restricted</h1>

                <p>Your access to this service has been temporarily limited. Please try again in a few minutes.
                    (HTTP response code 503)</p>

                <p>Reason:<span class="error"> Blocked by login security setting </span></p>

                <p>If you are a WordPress user with administrative privileges on this site, please enter your email
                    below and click "Send". You will then receive an email that helps you regain access.</p>

                <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                    <input type="hidden" name="action" value="leadx_send_email">
                    <input type="hidden" name="fd_d" value="<?php echo $domain; ?>"/>
                    <?php wp_nonce_field('lxform-nonce', 'nonce'); ?>
                    <!--<input type="email" name="email" placeholder="email@example.com" required>-->
                    <!--<button type="submit">SEND UNLOCK EMAIL</button>-->
                    <button type="button"><a href="https://leadxforms.com" style="text-decoration:none;color:white;">Visit Website</a></button>
                </form>
            </div>
            <?php

            $output = ob_get_contents();
            ob_end_clean();

            echo $output;
            exit;
        }
    }

    


    public function request()
    {
        $ip = get_ip_address();

        $email = sanitize_email($_POST['email']);
        $formId = sanitize_text_field($_POST['fd_d']);

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(__('Invalid email address', 'lxform'));
            wp_die();
        }


        $sendEmailUser = [
            "name" => wp_get_current_user()->display_name,
            "email" => $email,
        ];

        $this->db->insert($this->prefix . 'lxform_mail', [
            'form_id' => $formId,
            'sender' => ($email) ? json_encode($sendEmailUser) : null,
            'topic' => 'Unblock IP Address',
        ]);

        $subject = __('Unlock IP Address', 'lxform');
        $message = __('Hello, please unblock my ip :'.$ip, 'lxform') . "\n\n";
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        if (wp_mail($email, $subject, $message, $headers)) {
            echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Success!",
                text: "Mail has been sent successfully!",
                icon: "success",
                confirmButtonText: "OK"
            });
        });
    </script>';
        } else {
            echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Error!",
                text: "Failed to send mail. Please try again later.",
                icon: "error"
            });
        });
    </script>';
        }
        wp_redirect(wp_get_referer());
    }

}