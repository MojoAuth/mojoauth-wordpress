<?php

// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('mojoAuth_Admin')) {

    /**
     * The main class and initialization point of the plugin.
     */
    class mojoAuth_Admin {

        /**
         * Constructor
         */
        public function __construct() {
            if (is_admin()) {
                add_action('admin_init', array($this, 'register_mojoauth_plugin_settings'));
            }
            add_action('admin_menu', array($this, 'create_mojoauth_menu'));
            add_filter('plugin_action_links', array($this, 'mojoauth_setting_links'), 10, 2);
            add_action('mojoauth_reset_admin_action', array($this, 'reset_settings_action'), 10, 2);
            add_action('admin_enqueue_scripts', array($this, 'add_stylesheet_to_admin'));
            add_action('wp_ajax_mojoauth_verification', array($this, 'mojoauth_apikey_verification'));
            add_action('wp_ajax_nopriv_mojoauth_verification', array($this, 'mojoauth_apikey_verification'));
            add_action('wp_ajax_mojoauth_get_language', array($this, 'mojoauth_get_language'));
            add_action('wp_ajax_nopriv_mojoauth_get_language', array($this, 'mojoauth_get_language'));
            add_action('personal_options_update', array($this, 'disable_users_email_change_BACKEND'), 5);
            add_action('show_user_profile', array($this, 'disable_users_email_change_HTML'));
        }

        /**
         * Save Plugin option on option table
         */
        public function register_mojoauth_plugin_settings() {
            register_setting('mojoauth_option', 'mojoauth_option', array($this, 'mojoauth_settings_validation'));
        }

        /**
         * MojoAuth Validation
         */
        public function mojoauth_settings_validation($input) {
            $message = null;
            $type = null;
            if (!$input) {
                $input = array();
            }
            if (null != $input) {
                if (!isset($input['apikey']) || empty($input['apikey'])) {
                    $message = __('MojoAuth Required APIkey.');
                    $type = 'error';
                } elseif (!isset($input['public_key']) || empty($input['public_key'])) {
                    $message = __('MojoAuth Required Public Key.');
                    $type = 'error';
                } elseif (!isset($input['login_redirection']) || empty($input['login_redirection'])) {
                    $message = __('MojoAuth Required Redirection.');
                    $type = 'error';
                } elseif (($input['login_redirection'] == "@@other@@") && (!isset($input['login_redirection_other']) || empty($input['login_redirection_other']))) {
                    $message = __('MojoAuth Required Redirection Other.');
                    $type = 'error';
                } elseif (get_option('mojoauth_option')) {
                    $message = __('Option updated!');
                    $type = 'updated';
                } else {
                    $message = __('Option added!');
                    $type = 'updated';
                }
            } else {
                $message = __('There was a problem.');
                $type = 'error';
            }

            add_settings_error('mojoauth_option_notice', 'mojoauth_option', $message, $type);
            return $input;
        }

        /**
         *
         * @param type $option
         * @param type $settings
         */
        public static function reset_settings_action($option, $settings) {
            if (current_user_can('manage_options')) {
                update_option($option, $settings);
            }
        }

        /**
         * Create menu.
         */
        public function create_mojoauth_menu() {
            add_menu_page('mojoAuth', 'MojoAuth', 'manage_options', 'mojoAuth', array('mojoAuth_Admin', 'options_page'), MOJOAUTH_ROOT_URL . 'admin/assets/images/icon.png');
        }

        /**
         * Add a settings link to the Plugins page,
         * so people can go straight from the plugin page to the settings page.
         */
        public function mojoauth_setting_links($links, $file) {
            static $thisPlugin = '';
            if (empty($thisPlugin)) {
                $thisPlugin = MOJOAUTH_ROOT_SETTING_LINK;
            }
            if ($file == $thisPlugin) {
                $settingsLink = '<a href="admin.php?page=mojoAuth">' . __('Settings', 'mojoAuth') . '</a>';

                array_unshift($links, $settingsLink);
            }
            return $links;
        }

        /**
         * Added Style and Script file on plguin Admin Page
         */
        public function add_stylesheet_to_admin() {
            wp_enqueue_style('mojoauth-admin-style', MOJOAUTH_ROOT_URL . 'admin/assets/css/style.css', false, MOJOAUTH_PLUGIN_VERSION);
            wp_enqueue_script('mojoauth-admin-ajax-script', MOJOAUTH_ROOT_URL . 'admin/assets/js/verification.js', array('jquery'), MOJOAUTH_PLUGIN_VERSION);
            wp_localize_script('mojoauth-admin-ajax-script', 'mojoauthadminajax', array('ajax_url' => admin_url('admin-ajax.php')));
        }

        /*
         * Callback for add_menu_page,
         * This is the first function which is called while plugin admin page is requested
         */

        public static function options_page() {
            require_once(MOJOAUTH_ROOT_DIR . "admin/views/settings.php");
        }

        /**
         * Request for get Public key from MojoAuth Server
         */
        public function mojoauth_apikey_verification() {
            $apikey = mojoAuthPlugin::data_validation('mojoauth_apikey', $_POST);
            require_once(MOJOAUTH_ROOT_DIR . "mojoAuthWPClient.php");
            $client = new mojoAuthWPClient($apikey);
            wp_die(json_encode($client->getPublicKey()));
        }

        /**
         * Request for get list of languages from MojoAuth Server
         */
        public function mojoauth_get_language() {
            $apikey = mojoAuthPlugin::data_validation('mojoauth_apikey', $_POST);
            require_once(MOJOAUTH_ROOT_DIR . "mojoAuthWPClient.php");
            $mojoauth_option = get_option('mojoauth_option');
            $client = new mojoAuthWPClient($apikey);
            $result = $client->getApiInfo();
            $result['lang'] = isset($mojoauth_option['language']) && !empty($mojoauth_option['language'])?trim($mojoauth_option['language']):'en';
            wp_die(json_encode($result));
        }

        /**
         * Set email field to be disable on update request
         */
        public function disable_users_email_change_BACKEND($user_id) {
            if (!current_user_can('manage_options')) {
                $user = get_user_by('id', $user_id);
                $_POST['email'] = $user->user_email;
            }
        }

        /**
         * Set email field to be disable on profile page
         */
        public function disable_users_email_change_HTML($user) {
            if (!current_user_can('manage_options')) {
                echo '<script>document.getElementById("email").setAttribute("disabled","disabled");</script>';
            }
        }

    }

    new mojoAuth_Admin();
}
