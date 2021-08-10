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
            </div>
            <hr>
            <div class="mojoauth_field">
                <?php submit_button(); ?>
            </div>
        </form>
    </div>
</div>