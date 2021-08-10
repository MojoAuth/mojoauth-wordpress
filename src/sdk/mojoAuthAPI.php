<?php
require_once(__DIR__."/lib/vendor/autoload.php");
require_once(__DIR__."/chilkat_9_5_0.php");
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;
use Firebase\JWT\JWT;
class mojoAuthAPI
{
    public $apiurl;
    public $apikey;

    public function __construct($apikey)
    {
        $apiurl = "https://api.mojoauth.com/";
        $this->setApiurl($apiurl);
        $this->setApikey($apikey);
    }
    /**
     * Get API URL
     */
    public function getApiurl()
    {
        return $this->apiurl;
    }
    /**
     * Set API URL
     */
    public function setApiurl($url)
    {
        return $this->apiurl = $url;
    }
    /**
     * Get API key
     */
    public function getApikey()
    {
        return $this->apikey;
    }
    /**
     * Set API key
     */
    public function setApikey($apikey)
    {
        return $this->apikey = $apikey;
    }
    /**
     * Send Link on Email
     */
    public function sendLinkOnEmail($email)
    {
        return $this->request("users/magiclink", array(
            "method"     => "POST",
            "headers"=>array("X-API-Key"=>$this->getApikey(),
            'Content-Type' => 'application/json; charset=utf-8'),
            "body"=>json_encode(array("email"=>$email))
      ));
    }
    /**
     * Check Login Status
     */
    public function checkLoginStatus($state_id)
    {
        return $this->request("users/status?state_id=".$state_id, array(
            "method"     => "GET",
            "headers"=>array("X-API-Key"=>$this->getApikey())
      ));
    }
    /**
     * Send Email OTP
     */
    public function sendEmailOTP($email)
    {
        return $this->request("users/emailotp", array(
            "method"     => "POST",
            "headers"=>array("X-API-Key"=>$this->getApikey(),
            'Content-Type' => 'application/json; charset=utf-8'),
            "body"=>json_encode(array("email"=>$email))
      ));
    }
    /**
     * Verify Email OTP
     */
    public function verifyEmailOTP($state_id, $otp)
    {
        return $this->request("users/emailotp/verify", array(
            "method"     => "POST",
            "headers"=>array("X-API-Key"=>$this->getApikey(),
            'Content-Type' => 'application/json; charset=utf-8'),
            "body"=>json_encode(array("state_id"=>$state_id,"otp"=>$otp))
      ));
    }
    /**
     * Get JWKS
     */
    public function JWKS()
    {
        return $this->request("token/jwks", array(
            "method"     => "GET",
            "headers"=>array("X-API-Key"=>$this->getApikey())
      ));
    }
    /**
     * Get Public Key / Certificate from MojoAuth Server
     */
    public function getPublicKey()
    {
        $jwks = $this->JWKS();
        $jwksResponse = isset($jwks["response"])?json_decode($jwks["response"]):false;
        if(!$jwksResponse->description){
            if(!extension_loaded('chilkat_9_5_0')){
                return array("status_code"=>500,"message"=>'chilkat_9_5_0 is not enabled, enable chilkat_9_5_0 to get response from https://www.chilkatsoft.com/installPhpExtension.asp or use Public Certificate manually.');
                exit;
            }
            $pubKey = new CkPublicKey();
            $success = $pubKey->LoadFromString(json_encode($jwksResponse->keys[0]));
            if ($success != true) {
                return array("status_code"=>500,"message"=>$pubKey->lastErrorText());
                exit;
            }
            return array("status_code"=>$jwks["status_code"],"data"=>$pubKey->getPem(false));
        }
        return array("status_code"=>($jwksResponse->code),"message"=>($jwksResponse->description));
    }
    /**
     * Decode UserProfile From AccessToken
     */
    public function getUserProfileData($access_token, $publicKey)
    {
        return JWT::decode($access_token, $publicKey, array('RS256'));
    }
    /**
     * http request from cURL and FSOCKOPEN
     */
    public function request($endPointPath, $args = array())
    {
        if (in_array('curl', get_loaded_extensions())) {
            $response = $this->curlRequest($endPointPath, $args);
        } elseif (ini_get('allow_url_fopen')) {
            $response = $this->fsockopenRequest($endPointPath, $args);
        } else {
            $response = array("status_code"=>500,"message"=>'cURL or FSOCKOPEN is not enabled, enable cURL or FSOCKOPEN to get response from mojoAuth API.');
        }
        return $response;
    }
    /**
     * http request from FSOCKOPEN
     */
    private function fsockopenApiMethod($endPointPath, $options)
    {
        $method = isset($options['method']) ? strtoupper($options['method']) : 'GET';
        $data = isset($options['body']) ? $options['body'] : array();
        
        $optionsArray = array('http' =>
            array(
                'method' => strtoupper($method),
                'timeout' => 50,
                'ignore_errors' => true
            ),
            "ssl" => array(
                "verify_peer" => false
            )
        );
        if (!empty($data) || $data === true) {
            $optionsArray['http']['content'] = $data;
        }

        foreach ($options['headers'] as $k => $val) {
            $optionsArray['http']['header'] .= "\r\n" . $k.":".$val;
        }

        $context = stream_context_create($optionsArray);
        $jsonResponse['response'] = file_get_contents($this->getApiurl().$endPointPath, false, $context);
        $parseHeaders = Functions::parseHeaders($http_response_header);
        if (isset($parseHeaders['Content-Encoding']) && $parseHeaders['Content-Encoding'] == 'gzip') {
            $jsonResponse['response'] = gzdecode($jsonResponse['response']);
        }
        $jsonResponse['status_code'] = $parseHeaders['reponse_code'];
        
        return $jsonResponse;
    }
    /**
     * http request from cURL
     */
    private function curlRequest($endPointPath, $options)
    {
        $method = isset($options['method']) ? strtoupper($options['method']) : 'GET';
        $data = isset($options['body']) ? $options['body'] : array();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiurl().$endPointPath);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        $headerArray = array();
        foreach ($options['headers'] as $k => $val) {
            $headerArray[] = $k.":".$val;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        if (in_array($method, array('POST'))) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = array();
        $output['response'] = curl_exec($ch);
        $output['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $output['response'] = curl_error($ch);
        }
        curl_close($ch);

        return $output;
    }
}
