<?php
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}
?>
<div id="mojoauth_admin">
    <div class="mojoauth_logo">
        <img src="<?php echo MOJOAUTH_ROOT_URL . 'admin/assets/images/logo.svg'?>" alt="MojoAuth" title="MojoAuth">
    </div>
    <br/>
    <?php
    settings_errors();
    ?><br/>
    <div class="mojoauth_config">
		<h2>Configuration</h2>
	<hr/>
        <form method="post" action="options.php"> 
            <?php
            $mojoauth_option = get_option('mojoauth_option');
            settings_fields('mojoauth_option');?>
            <div class="mojoauth_field">
                <label for="mojoauth_apikey">
                <?php _e('APIkey:','mojoauth');?>
                </label>
                <input type="text" id="mojoauth_apikey" name="mojoauth_option[apikey]" value="<?php echo isset($mojoauth_option['apikey'])?esc_attr($mojoauth_option['apikey']):"";?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxx">
				<div class="mojoauth_verification button" disabled><?php _e('Get Public Certificate','mojoauth');?></div>
				<div class="mojoauth_help_text"><?php _e('<a href="https://mojoauth.com/signin" target="_blank">Log in to MojoAuth</a> and get your API key under the <a href="https://mojoauth.com/dashboard/overview" target="_blank">overview</a> section.','mojoauth');?></div>
                <div class="mojoauth_verification_message" style="display:none;"></div>
            </div>
            <div class="mojoauth_field">
                <label for="mojoauth_public_key">
                    <?php _e('Public Certificate:','mojoauth');?>
                </label>
                <textarea id="mojoauth_public_key" name="mojoauth_option[public_key]" rows="8" placeholder="-----BEGIN PUBLIC KEY-----
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
xxxxxxxx
-----END PUBLIC KEY-----
"><?php echo isset($mojoauth_option['public_key'])?esc_attr($mojoauth_option['public_key']):"";?></textarea>
<div class="mojoauth_help_text"><?php _e('Get your public certificate by clicking on the Get Public Certificate button. This certificate will be used to verify the token.','mojoauth');?></div>
            </div>
			<div class="mojoauth_field">
                <label for="mojoauth_language">
                <?php _e('Language:','mojoauth');?>
                </label>
				<select id="mojoauth_language" name="mojoauth_option[language]">
					<?php
					$mojoAuthLanguages = array('en'=>'English',
					'it'=>'Italian',
					'de'=>'German',
					'fr'=>'French',
					'es'=>'Spanish',
					'pt'=>'Portuguese',
					'ru'=>'Russian');
					$selectedLanguage = isset($mojoauth_option['language']) && !empty($mojoauth_option['language'])?trim($mojoauth_option['language']):'en';
						foreach($mojoAuthLanguages as $lan=>$language){
							?>
							<option value="<?php _e($lan,'mojoauth');?>"
							<?php
							if($selectedLanguage==$lan){
								?> selected="selected"<?php
							}
							?>
							><?php _e($language,'mojoauth');?></option>
							<?php
						}					
					?>
					
				</select>
				<div class="mojoauth_help_text"><?php _e('Localize your website according to your country or region. Check the <a href="https://mojoauth.com/docs/configurations/localization/" target="_blank">supported languages</a> page.','mojoauth');?></div>
            </div>
			<div class="mojoauth_field">
                <label for="mojoauth_integrate_method">
                <?php _e('Integrate Method:','mojoauth');?>
                </label>
				<select id="mojoauth_integrate_method" name="mojoauth_option[integrate_method]">
					<?php
					$mojoAuthIntegrateMethod = array('link'=>'Magic Link',
					'otp'=>'Email OTP');
					$selectedIntegrateMethod = isset($mojoauth_option['integrate_method']) && !empty($mojoauth_option['integrate_method'])?trim($mojoauth_option['integrate_method']):'link';
						foreach($mojoAuthIntegrateMethod as $key=>$value){
							?>
							<option value="<?php _e($key,'mojoauth');?>"
							<?php
							if($selectedIntegrateMethod==$key){
								?> selected="selected"<?php
							}
							?>
							><?php _e($value,'mojoauth');?></option>
							<?php
						}					
					?>
					
				</select>
				<div class="mojoauth_help_text"><?php _e('Select the authentication method with which users will authenticate. refer <a href="https://mojoauth.com/docs/integrations/wordpress/" target="_blank">here</a>.','mojoauth');?></div>
            </div>
            <hr>
            <div class="mojoauth_field">
                <?php submit_button(); ?>
            </div>
        </form>
    </div>
	<div class="mojoauth_shortcode_section">
	<h2>Shortcode</h2>
	<hr/>
	<h4>Editor Shortcode</h4>
	<input type="text" value="[mojoauth]" id="mojoauthloginformshortcodeeditor" readonly="readonly"	/>
	<h4>PHP Shortcode</h4>
	<input type="text" value="&lt;?php echo do_shortcode('[mojoauth]'); ?&gt;" id="mojoauthloginformshortcodephp" readonly="readonly"	/>
	</div>
</div>