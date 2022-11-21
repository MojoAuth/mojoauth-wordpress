<?php

/**
 * Plugin Name: MojoAuth Passwordless Authentication
 * Plugin URI: https://github.com/MojoAuth/mojoauth-wordpress
 * Description: MojoAuth provides a secure and delightful experience to your customer with passwordless. Here, you'll find comprehensive guides and documentation to help you to start working with MojoAuth APIs.
 * Version: 2.6
 * Author: MojoAuth Team
 * Author URI: https://mojoauth.com
 * License: GPL2+
 */

if (!defined('ABSPATH')) {
    exit();
}
// If this file is called directly, abort.
define('MOJOAUTH_ROOT_DIR', plugin_dir_path(__FILE__));
define('MOJOAUTH_ROOT_URL', plugin_dir_url(__FILE__));
define('MOJOAUTH_PLUGIN_VERSION', '2.6');
define('MOJOAUTH_ROOT_SETTING_LINK', plugin_basename(__FILE__));

if (!class_exists('mojoAuthPlugin')) {
ob_start();
    /**
     * The main class and initialization point of the plugin.
     */
    class mojoAuthPlugin
    {

        /**
         * Constructor
         */
        public function __construct()
        {
            $this->define_constants();
        }

        /**
         * Define constants needed across the plug-in.
         */
        public function define_constants()
        {
            require_once(MOJOAUTH_ROOT_DIR."admin/index.php");
            require_once(MOJOAUTH_ROOT_DIR."frontend/pages/auth.php");
        }
       

        /**
         * Reset Sharing Settings.
         */
        public static function reset_share_options()
        {
            update_option('mojoauth_option', '');
        }
        /**
         * Post Data validation
         */
        public static function data_validation($key, $post){
            return isset($post[$key]) && !empty($post[$key]) ? sanitize_text_field(esc_html(trim($post[$key]))) : false;
        }
        /**
         * Post Email Data validation
         */
        public static function email_validation($key, $post){
            return isset($post[$key]) && !empty($post[$key]) ? sanitize_email(esc_html(trim($post[$key]))) : false;
        }
        
    }

    new mojoAuthPlugin();
}
