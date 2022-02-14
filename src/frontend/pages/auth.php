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
			add_action('init',array($this,'mojoauth_stateid_handler'));
			add_shortcode( 'mojoauth', array($this,'mojoauth_short_code'));
			add_action('woocommerce_init',array($this,'woocommerce_init'));
        }
		function woocommerce_init(){
			add_filter( 'woocommerce_checkout_fields', array($this,'mojoauth_woocommerce_remove_checkout_fields'));
			add_action('woocommerce_edit_account_form_end', array($this,'mojoauth_myaccount_required_fields'));
			if(get_option('woocommerce_enable_checkout_login_reminder') == "yes"){
				add_action('template_redirect',array($this,'mojoauth_woocommerce_login_form_redirect'));
			}
		}
		/**
         * create and generate ShortCode login form
         */
		public function mojoauth_short_code( $atts ){
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

		public function mojoauth_woocommerce_login_form_redirect(){
			if(!is_user_logged_in()){
				$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
				$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );
				if(in_array(get_the_ID(),array($myaccount_page_id,$checkout_page_id))){
					wp_redirect(wp_login_url(get_permalink()));
					exit;
				}
			}
		}
		public function mojoauth_login_form(){
			if(!is_user_logged_in()){
				add_action('wp_footer', array($this,'mojoauth_enqueue_script'),10);
				return '<div id="login"></div>';
			}
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
			$language = isset($mojoauth_option["language"]) && !empty($mojoauth_option["language"])?trim($mojoauth_option["language"]):"en";
			$integrate_method = isset($mojoauth_option["integrate_method"]) && !empty($mojoauth_option["integrate_method"])?trim($mojoauth_option["integrate_method"]):"link";
            wp_enqueue_script('mojoauth-js', 'https://cdn.mojoauth.com/js/mojoauth.min.js', false, MOJOAUTH_PLUGIN_VERSION);
            wp_enqueue_script('mojoauthajax-script', MOJOAUTH_ROOT_URL . 'frontend/assets/js/loginpage.js', array('mojoauth-js'), MOJOAUTH_PLUGIN_VERSION);
            wp_localize_script('mojoauthajax-script', 'mojoauthajax', array('ajax_url' => admin_url('admin-ajax.php'), 
			'apikey' => $apikey, 
			'language' => $language,
			'integrate_method' => $integrate_method,
			'redirect' => home_url()));
        }
		
		/**
		 * Handler for state ID
		 */
		public function mojoauth_stateid_handler(){
			$state_id = mojoAuthPlugin::data_validation('state_id', $_GET);
            if (!empty($state_id)) {
                //call API
                $mojoauth_option = get_option('mojoauth_option');
                $apikey = isset($mojoauth_option["apikey"])?trim($mojoauth_option["apikey"]):"";
				if (!empty($apikey)) {
					require_once(MOJOAUTH_ROOT_DIR."mojoAuthWPClient.php");
					$client = new mojoAuthWPClient($apikey);

					$mojoAutoUserResponse = $client->checkLoginStatus($state_id);
					$mojoAutoUser = isset($mojoAutoUserResponse['response'])?json_decode($mojoAutoUserResponse['response']):false;
					if (isset($mojoAutoUser->user) && isset($mojoAutoUser->user->identifier) && !empty($mojoAutoUser->user->identifier)) {
						$this->allow_login($mojoAutoUser->user->identifier);
					}
				}
            }
		}
        /**
         * Login handler
         */
        public function mojoauth_login()
        {
            $token = mojoAuthPlugin::data_validation('mojoauth_token', $_POST);
            $email = mojoAuthPlugin::email_validation('mojoauth_email', $_POST);
            if (!empty($token) && !empty($email)) {
                //call API
                require_once(MOJOAUTH_ROOT_DIR."mojoAuthWPClient.php");
                $mojoauth_option = get_option('mojoauth_option');
                $apikey = isset($mojoauth_option["apikey"]) && !empty($mojoauth_option["apikey"])?trim($mojoauth_option["apikey"]):"";
                $publicKey = isset($mojoauth_option["public_key"]) && !empty($mojoauth_option["public_key"])?trim($mojoauth_option["public_key"]):"";
                $client = new mojoAuthWPClient($apikey);

                $mojoAutoUser = $client->getUserProfileData($token, $publicKey);
                if ($mojoAutoUser->identifier && $email) {
                    $this->allow_login($email);
                }
            }
            wp_die();
        }
		/**
		 * Allow Login by email
		 */
		private function allow_login($email){
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
			do_action('wp_login', $user->user_login, $user);
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
