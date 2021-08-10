<?php
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('mojoAuth_Front')) {

    /**
     * The main class and initialization point of the plugin.
     */
    class mojoAuth_Front
    {

        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('login_enqueue_scripts', array($this,'mojoauth_enqueue_script'), 10);
            add_action('wp_ajax_mojoauth_login', array($this,'mojoauth_login'));
            add_action('wp_ajax_nopriv_mojoauth_login', array($this,'mojoauth_login'));
        }
        /**
         * create and generate login form
         */
        public function mojoauth_enqueue_script()
        {
            ?>
            <style type="text/css">#login{display:none;}#mojoauth-passwordless-form {margin: 6% auto;width: 405px;}</style>
            <?php
            $mojoauth_option = get_option('mojoauth_option');
            $apikey = isset($mojoauth_option["apikey"]) && !empty($mojoauth_option["apikey"])?trim($mojoauth_option["apikey"]):"";
            wp_enqueue_script('mojoauth-js', 'https://cdn.mojoauth.com/js/mojoauth.min.js', false, MOJOAUTH_PLUGIN_VERSION);
            wp_enqueue_script('mojoauthajax-script', MOJOAUTH_ROOT_URL . 'frontend/assets/js/loginpage.js', array('mojoauth-js'), MOJOAUTH_PLUGIN_VERSION);
            wp_localize_script('mojoauthajax-script', 'mojoauthajax', array('ajax_url' => admin_url('admin-ajax.php'), 'apikey' => $apikey, 'redirect' => home_url()));
        }
        /**
         * Login handler
         */
        public function mojoauth_login()
        {
            $token = isset($_POST['mojoauth_token']) && !empty($_POST['mojoauth_token']) ? trim($_POST['mojoauth_token']) : false;
            $email = isset($_POST['mojoauth_email']) && !empty($_POST['mojoauth_email']) ? trim($_POST['mojoauth_email']) : false;
            if (!empty($token) && !empty($email)) {
                //call API
                require_once(MOJOAUTH_ROOT_DIR."mojoAuthWPClient.php");
                $mojoauth_option = get_option('mojoauth_option');
                $apikey = isset($mojoauth_option["apikey"]) && !empty($mojoauth_option["apikey"])?trim($mojoauth_option["apikey"]):"";
                $publicKey = isset($mojoauth_option["public_key"]) && !empty($mojoauth_option["public_key"])?trim($mojoauth_option["public_key"]):"";
                $client = new mojoAuthWPClient($apikey);

                $mojoAutoUser = $client->getUserProfileData($token, $publicKey);
                if ($mojoAutoUser->identifier && $email) {
                    $user = get_user_by('email', $email);
                    
                    if (!$user) {
                        $username = $this->get_username($email, 0);
                        //create user in wp database
                        $result = wp_create_user($username, $email, $email);
                        if (is_wp_error($result)) {
                            $error = $result->get_error_message();
                        //handle error here
                        } else {
                            $user = get_user_by('id', $result);
                        }
                    }
                    //login user
                    wp_clear_auth_cookie();
                    wp_set_auth_cookie($user->ID, true);
                    wp_set_current_user($user->ID);
                    do_action('wp_login', $user->data->user_login, $user);
                }
            }
            wp_die();
        }
        /**
         * get unique username
         */
        private function get_username($email, $count)
        {
            $username = explode('@', $email)[0];
            if ($count>0) {
                $username = $username."-".$count;
            }
            if (username_exists($username)) {
                return $this->get_username($email, $count+1);
            }
            return $username;
        }
    }
    new mojoAuth_Front();
}
