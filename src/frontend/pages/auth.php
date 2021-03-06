<?php

// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('mojoAuth_Front')) {

    /**
     * The main class and initialization point of the plugin.
     */
    class mojoAuth_Front {

        /**
         * Constructor
         */
        public function __construct() {
            add_action('login_enqueue_scripts', array($this, 'mojoauth_enqueue_script'), 10);
            add_action('wp_ajax_mojoauth_login', array($this, 'mojoauth_login'));
            add_action('wp_ajax_nopriv_mojoauth_login', array($this, 'mojoauth_login'));
            add_action('init', array($this, 'mojoauth_enqueue_script'));
            add_shortcode('mojoauth', array($this, 'mojoauth_short_code'));
            add_filter( 'pr_page_content', array($this, 'mojoauth_short_code'));
            add_action('woocommerce_init', array($this, 'woocommerce_init'));
        }

        function woocommerce_init() {
            add_filter('woocommerce_checkout_fields', array($this, 'mojoauth_woocommerce_remove_checkout_fields'));
            add_action('woocommerce_edit_account_form_end', array($this, 'mojoauth_myaccount_required_fields'));
            if (get_option('woocommerce_enable_checkout_login_reminder') == "yes") {
                add_action('template_redirect', array($this, 'mojoauth_woocommerce_login_form_redirect'));
            }
        }

        /**
         * create and generate ShortCode login form
         */
        public function mojoauth_short_code($atts) {
            return $this->mojoauth_login_form();
        }

        public function mojoauth_myaccount_required_fields() {
            ?>
            <script>document.getElementById('account_email').setAttribute("readonly", true);</script>
            <?php

        }

        public function mojoauth_woocommerce_remove_checkout_fields($fields) {
            $readonly = ['readonly' => 'readonly'];
            $fields['billing']['billing_email']['custom_attributes'] = $readonly;
            return $fields;
        }

        public function mojoauth_woocommerce_login_form_redirect() {
            if (!is_user_logged_in()) {
                $myaccount_page_id = get_option('woocommerce_myaccount_page_id');
                $checkout_page_id = get_option('woocommerce_checkout_page_id');
                if (in_array(get_the_ID(), array($myaccount_page_id, $checkout_page_id))) {
                    wp_redirect(wp_login_url(get_permalink()));
                    exit;
                }
            }
        }

        public function mojoauth_login_form() {
            if (!is_user_logged_in()) {
                add_action('wp_footer', array($this, 'mojoauth_enqueue_script'), 10);
                return '<div id="login"></div>';
            }
        }

        /**
         * create and generate login form
         */
        public function mojoauth_enqueue_script() {
            $mojoauth_option = get_option('mojoauth_option');
            $apikey = isset($mojoauth_option["apikey"]) && !empty($mojoauth_option["apikey"]) ? trim($mojoauth_option["apikey"]) : "";
            $language = isset($mojoauth_option["language"]) && !empty($mojoauth_option["language"]) ? trim($mojoauth_option["language"]) : "en";
            $integrate_method_email = isset($mojoauth_option["integrate_method_email"]) && !empty($mojoauth_option["integrate_method_email"]) ? trim($mojoauth_option["integrate_method_email"]) : "";
            $integrate_method_email_type = isset($mojoauth_option["integrate_method_email_type"]) && !empty($mojoauth_option["integrate_method_email_type"]) ? trim($mojoauth_option["integrate_method_email_type"]) : "";
            $integrate_method_sms = isset($mojoauth_option["integrate_method_sms"]) && !empty($mojoauth_option["integrate_method_sms"]) ? trim($mojoauth_option["integrate_method_sms"]) : "";
            if (empty($integrate_method_email) && empty($integrate_method_sms)) {
                $integrate_method_email = 'email';
                $integrate_method_email_type = 'magiclink';
            }
            $mojoauthAjax = array('ajax_url' => admin_url('admin-ajax.php'),
                'apikey' => $apikey,
                'language' => $language,
                'integrate_method' => array(
                    "email" => ($integrate_method_email == "email") ? $integrate_method_email_type : "",
                    "sms" => $integrate_method_sms
                ),
                'redirect' => home_url());
            if (!is_user_logged_in()) {
                $state_id = mojoAuthPlugin::data_validation('state_id', $_GET);
                if (!empty($state_id)) {
                    //call API
                    $mojoauth_option = get_option('mojoauth_option');
                    $apikey = isset($mojoauth_option["apikey"]) ? trim($mojoauth_option["apikey"]) : "";
                    if (!empty($apikey)) {
                        require_once(MOJOAUTH_ROOT_DIR . "mojoAuthWPClient.php");
                        $client = new mojoAuthWPClient($apikey);
                        $mojoAutoUserResponse = $client->checkLoginStatus($state_id);
                        $mojoAutoUser = isset($mojoAutoUserResponse['response']) ? json_decode($mojoAutoUserResponse['response']) : false;
                        if (isset($mojoAutoUser->authenticated) && ($mojoAutoUser->authenticated == true) && isset($mojoAutoUser->user->identifier) && !empty($mojoAutoUser->user->identifier)) {
                            $mojoauthAjax['action'] = 'mojoauth_login';
                            $mojoauthAjax['mojoauth_token'] = $mojoAutoUser->oauth->access_token;
                            $mojoauthAjax['mojoauth_identifier'] = $mojoAutoUser->user->identifier;
                            add_action('wp_footer', array($this, 'mojoauth_stateid_js_handler'), 11);
                        }
                    }
                }
            }
            wp_enqueue_script('mojoauth-js', 'https://cdn.mojoauth.com/js/mojoauth.min.js', false, MOJOAUTH_PLUGIN_VERSION);
            wp_enqueue_script('mojoauthajax-script', MOJOAUTH_ROOT_URL . 'frontend/assets/js/loginpage.js', array('mojoauth-js','jquery'), MOJOAUTH_PLUGIN_VERSION);
            wp_localize_script('mojoauthajax-script', 'mojoauthajax', $mojoauthAjax);
        }

        /**
         * StateID handler from js
         */
        function mojoauth_stateid_js_handler() {
            ?>
            <script>
                setTimeout(function () {
                    mjAjaxRequest(mojoauthajax, {
                        "action": mojoauthajax.action,
                        "mojoauth_token": mojoauthajax.mojoauth_token,
                        "mojoauth_identifier": mojoauthajax.mojoauth_identifier
                    });
                }, 2000);
            </script>
            <?php

        }

        /**
         * Login Fields handler
         */
        function mojoauth_fields_handler($identifier, $email = "") {
            if (!empty($identifier)) {
                if (!empty($email) && is_email($email)) {
                    $user = get_user_by('email', $email);
                    if (!$user) {
                        $this->allow_login($email, $identifier);
                    } else {
                        echo json_encode(array("status" => "error", "message" => "This email is already exist. please try another email."));
                    }
                } else {
                    if (!is_email($identifier)) {
                        //check user exist in db                        
                        $users = get_users(array(
                            'meta_key' => 'mj_user_identifier',
                            'meta_value' => $identifier
                        ));
                        if (isset($users[0]) && isset($users[0]->data) && isset($users[0]->data->user_email)) {
                            $this->allow_login($users[0]->data->user_email, $identifier);
                        } else {
                            echo json_encode(array("status" => "popup", "message" => "Display ask email popup"));
                        }
                    } else {
                        $this->allow_login($identifier);
                    }
                }
            } else {
                echo json_encode(array("status" => "error", "message" => "identifier not found"));
            }
        }

        /**
         * Login handler
         */
        public function mojoauth_login() {
            $token = mojoAuthPlugin::data_validation('mojoauth_token', $_POST);
            $identifier = mojoAuthPlugin::data_validation('mojoauth_identifier', $_POST);
            $email = mojoAuthPlugin::email_validation('mojoauth_email', $_POST);
            if (!empty($token) && !empty($identifier)) {
                //call API
                require_once(MOJOAUTH_ROOT_DIR . "mojoAuthWPClient.php");
                $mojoauth_option = get_option('mojoauth_option');
                $apikey = isset($mojoauth_option["apikey"]) && !empty($mojoauth_option["apikey"]) ? trim($mojoauth_option["apikey"]) : "";
                $publicKey = isset($mojoauth_option["public_key"]) && !empty($mojoauth_option["public_key"]) ? trim($mojoauth_option["public_key"]) : "";
                $client = new mojoAuthWPClient($apikey);
                try {
                    $mojoAutoUser = $client->getUserProfileData($token, $publicKey);
                    if (isset($mojoAutoUser->identifier) && !empty($mojoAutoUser->identifier)) {
                        $this->mojoauth_fields_handler($mojoAutoUser->identifier, $email);
                    } else {
                        echo json_encode(array("status" => "error", "message" => "An error occurred."));
                    }
                } catch (Exception $e) {
                    echo json_encode(array("status" => "error", "message" => "An error occurred."));
                }
            }
            wp_die();
        }

        /**
         * Allow Login by email
         */
        private function allow_login($email, $identifier = "") {
            $user = get_user_by('email', $email); //000
            if (!$user) {
                $username = $this->get_username($email, 0);
                //create user in wp database
                $result = wp_create_user($username, $email, $email);
                if (is_wp_error($result)) {
                    $error = $result->get_error_message();
                    //handle error here
                } else {
                    if (!empty($identifier)) {
                        add_user_meta($result, 'mj_user_identifier', $identifier);
                    }
                    $user = get_user_by('id', $result);
                }
            }
            //login user
            wp_clear_auth_cookie();
            wp_set_auth_cookie($user->ID, true);
            wp_set_current_user($user->ID);
            do_action('wp_login', $user->user_login, $user);
            echo json_encode(array("status" => "login", "action" => "refresh"));
        }

        /**
         * get unique username
         */
        private function get_username($email, $count) {
            $username = explode('@', $email)[0];
            if ($count > 0) {
                $username = $username . "-" . $count;
            }
            if (username_exists($username)) {
                return $this->get_username($email, $count + 1);
            }
            return $username;
        }

    }

    new mojoAuth_Front();
}
