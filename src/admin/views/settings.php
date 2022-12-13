<?php
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}
?>

<div id="mojoauth_admin">
    <div class="mojoauth_logo">
        <img src="<?php echo MOJOAUTH_ROOT_URL . 'admin/assets/images/logo.svg' ?>" alt="MojoAuth" title="MojoAuth">
    </div>
    <br/>
    <?php
    settings_errors();
    ?><br/>
    <div class="mojoauth_config">
        <h2><?php _e('Configuration','mojoauth');?></h2>
        <hr/>
        <form method="post" action="options.php"> 
            <?php
            $mojoauth_option = get_option('mojoauth_option');
            $integrate_method_email = isset($mojoauth_option["integrate_method_email"]) && !empty($mojoauth_option["integrate_method_email"]) ? trim($mojoauth_option["integrate_method_email"]) : "";
            $integrate_method_email_type = isset($mojoauth_option["integrate_method_email_type"]) && !empty($mojoauth_option["integrate_method_email_type"]) ? trim($mojoauth_option["integrate_method_email_type"]) : "";
            $integrate_method_sms = isset($mojoauth_option["integrate_method_sms"]) && !empty($mojoauth_option["integrate_method_sms"]) ? trim($mojoauth_option["integrate_method_sms"]) : "";
            if (empty($integrate_method_email) && empty($integrate_method_sms)) {
                $integrate_method_email = 'email';
                $integrate_method_email_type = 'magiclink';
            }
            settings_fields('mojoauth_option');
            ?>
            <div class="mojoauth_field">
                <label for="mojoauth_apikey">
                    <?php _e('APIkey:', 'mojoauth'); ?>
                </label>
                <input type="text" id="mojoauth_apikey" name="mojoauth_option[apikey]" value="<?php echo isset($mojoauth_option['apikey']) ? esc_attr($mojoauth_option['apikey']) : ""; ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxx">
                <div class="mojoauth_verification button" disabled><?php _e('Verify', 'mojoauth'); ?></div>
                <div class="mojoauth_help_text"><?php _e('<a href="https://mojoauth.com/signin" target="_blank">Log in to MojoAuth</a> and get your API key under the <a href="https://mojoauth.com/dashboard/overview" target="_blank">overview</a> section.', 'mojoauth'); ?></div>
                <div class="mojoauth_verification_message" style="display:none;"></div>
            </div>
            <div class="mojoauth_field mojoauth_active">
                <label for="mojoauth_public_key">
                    <?php _e('Public Certificate:', 'mojoauth'); ?>
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
                          "><?php echo isset($mojoauth_option['public_key']) ? esc_attr($mojoauth_option['public_key']) : ""; ?></textarea>
                <div class="mojoauth_help_text"><?php _e('Get your public certificate by clicking on the Verify button. This certificate will be used to verify the token.', 'mojoauth'); ?></div>
            </div>
            <div class="mojoauth_field mojoauth_active">
                <label for="mojoauth_language">
                    <?php _e('Language:', 'mojoauth'); ?>
                </label>
                <select id="mojoauth_language" name="mojoauth_option[language]">
                </select>
                <div class="mojoauth_help_text"><?php _e('Localize your website according to your country or region. Check the <a href="https://mojoauth.com/docs/configurations/localization/" target="_blank">supported languages</a> page.', 'mojoauth'); ?></div>
            </div>
            <div class="mojoauth_field mojoauth_active">
                <label for="mojoauth_integrate_method">
                    <?php _e('Integrate Method:', 'mojoauth'); ?>
                </label>
                <div class="mojoauth_rightfield mojoauth_active">
                    <input type="checkbox" id="mojoauth_integrate_method_email" name="mojoauth_option[integrate_method_email]" value="email" <?php echo (($integrate_method_email == "email") ? 'checked="checked"' : "") ?>/>
                    <label for="mojoauth_integrate_method_email">
                        <?php _e('Email Authentication', 'mojoauth'); ?>
                    </label>
                    <br/>
                    <div id="mojoauth_integrate_method_email_active" class="mojoauth_subfield">
                        <label for="mojoauth_integrate_method_email_link">
                            <input type="radio" id="mojoauth_integrate_method_email_link" name="mojoauth_option[integrate_method_email_type]" value="magiclink" <?php echo (($integrate_method_email_type == "magiclink") ? 'checked="checked"' : "") ?>/>
                            <?php _e('Email Magic Link', 'mojoauth'); ?>
                        </label>
                        <label for="mojoauth_integrate_method_email_otp">
                            <input type="radio" id="mojoauth_integrate_method_email_otp" name="mojoauth_option[integrate_method_email_type]" value="otp" <?php echo (($integrate_method_email_type == "otp") ? 'checked="checked"' : "") ?>/>
                            <?php _e('Email OTP', 'mojoauth'); ?>
                        </label>
                    </div>
                </div>
                <div class="mojoauth_rightfield mojoauth_active">
                    <label for="mojoauth_integrate_method_sms">
                        <input type="checkbox" id="mojoauth_integrate_method_sms" name="mojoauth_option[integrate_method_sms]" value="sms" <?php echo (($integrate_method_sms == "sms") ? 'checked="checked"' : "") ?>/>
                        <?php _e('SMS Authentication', 'mojoauth'); ?>
                    </label>
                </div>
                </div>
                <div class="mojoauth_field mojoauth_active">
                <label for="mojoauth_login_redirection">
                    <?php _e('Custom Redirection:', 'mojoauth'); ?>
                </label>
                <?php
                $login_redirection = isset($mojoauth_option['login_redirection'])?$mojoauth_option['login_redirection']:"";
                
                ?>
                <select id="mojoauth_login_redirection" name="mojoauth_option[login_redirection]">
                    <option value=""> <?php _e('--- SELECT --- ', 'mojoauth');?></option>
                    <option value="@@samepage@@"
                    <?php 
                    if($login_redirection == "@@samepage@@"){
                        ?> selected="selected"<?php
                    }
                    ?>
                    > <?php _e('Same Page', 'mojoauth');?></option>
                    <?php 
                    $options = '';
                    query_posts('&showposts=-1&order=ASC');
                    while (have_posts()) {
                        the_post();
                        ob_start();
                        the_taxonomies(); 
                        $result = ob_get_contents();
                        ob_end_clean();
                        $options .= '<option value="' . get_permalink().'"';
                        if((get_permalink() != "") && ($login_redirection == get_permalink())){
                            $options .= ' selected="selected"';
                        }
                        $options .= '>' . get_the_title() . '</option>';
                    };                    
                    $options .= '<option value="@@other@@"';
                    if($login_redirection == "@@other@@"){
                            $options .= ' selected="selected"';
                        }
                        $options .= '>'. __('Other', 'mojoauth').'</option>';
                        echo $options;
                        ?>
                </select>
                <div class="mojoauth_help_text"><?php _e('Select you page where you wanted to redirect after successful authentication. By default MojoAuth will redirect you to the root domain.', 'mojoauth'); ?></div>
                </div>
                <div class="mojoauth_field mojoauth_active">
                    <label for="mojoauth_login_redirection">&nbsp;</label>
                    <input type="text" id="mojoauth_login_redirection_other" name="mojoauth_option[login_redirection_other]" value="<?php echo isset($mojoauth_option['login_redirection_other']) ? esc_attr($mojoauth_option['login_redirection_other']) : ""; ?>" placeholder="https://example.com/path">
                </div>
            <hr>
            <div class="mojoauth_field">
                <?php submit_button(); ?>
            </div>
        </form>
    </div>
    <div class="mojoauth_rightsection">
         <div class="mojoauth_shortcode_section">
            <h2><?php _e('Help','mojoauth');?></h2>
            <hr/>
            <p><?php _e('Configure your desired Social provider i.e. Google, Facebook, Apple, etc. from <a href="https://mojoauth.com/dashboard/marketplace/list" target="_blank">Dashboard</a> to use Social Login with your provider</p>','mojoauth');?>
        </div>

        <div class="mojoauth_shortcode_section">
            <h2><?php _e('Shortcode','mojoauth');?></h2>
            <hr/>
            <h4><?php _e('Editor Shortcode','mojoauth');?></h4>
            <input type="text" value="[mojoauth]" id="mojoauthloginformshortcodeeditor" readonly="readonly"	/>
            <h4><?php _e('PHP Shortcode','mojoauth');?></h4>
            <input type="text" value="&lt;?php echo do_shortcode('[mojoauth]'); ?&gt;" id="mojoauthloginformshortcodephp" readonly="readonly"	/>

        </div>
    </div>
</div>