<?php
if (!defined('ABSPATH')) {
    exit();
}
require_once(MOJOAUTH_ROOT_DIR."sdk/mojoAuthAPI.php");
/**
 * OverWrite mojoAuthWPClient Class with mojoAuthAPI
 */
class mojoAuthWPClient extends mojoAuthAPI
{	
    /**
     * OverWrite Request function of mojoAuth API request
     */
    public function request($endPointPath, $args = array()){
        $output = array();
        $request = wp_remote_request($this->getApiurl() . $endPointPath, $args);
        $output['response'] = wp_remote_retrieve_body($request);
        $output['status_code'] = $request["response"]["code"];
        return $output;
    }
}