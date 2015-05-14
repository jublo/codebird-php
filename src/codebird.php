<?php

namespace Codebird;

/**
 * A Twitter library in PHP.
 *
 * @package   codebird
 * @version   2.7.0
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2010-2015 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/GPL-3.0 GNU General Public License 3.0
 * @link      https://github.com/jublonet/codebird-php
 */

/**
 * Define constants
 */
$constants = explode(' ', 'OBJECT ARRAY JSON');
foreach ($constants as $i => $id) {
    $id = 'CODEBIRD_RETURNFORMAT_' . $id;
    defined($id) or define($id, $i);
}
$constants = array(
    'CURLE_SSL_CERTPROBLEM' => 58,
    'CURLE_SSL_CACERT' => 60,
    'CURLE_SSL_CACERT_BADFILE' => 77,
    'CURLE_SSL_CRL_BADFILE' => 82,
    'CURLE_SSL_ISSUER_ERROR' => 83,
    // workaround for http://php.net/manual/en/function.curl-setopt.php#107314
    '_CURLOPT_TIMEOUT_MS' => 155,
    '_CURLOPT_CONNECTTIMEOUT_MS' => 156
);
foreach ($constants as $id => $i) {
    defined($id) or define($id, $i);
}
unset($constants);
unset($i);
unset($id);

/**
 * A Twitter library in PHP.
 *
 * @package codebird
 * @subpackage codebird-php
 */
class Codebird
{
    /**
     * The current singleton instance
     */
    private static $_instance = null;

    /**
     * The OAuth consumer key of your registered app
     */
    protected static $_oauth_consumer_key = null;

    /**
     * The corresponding consumer secret
     */
    protected static $_oauth_consumer_secret = null;

    /**
     * The app-only bearer token. Used to authorize app-only requests
     */
    protected static $_oauth_bearer_token = null;

    /**
     * The API endpoint to use
     */
    protected static $_endpoint = 'https://api.twitter.com/1.1/';

    /**
     * The media API endpoint to use
     */
    protected static $_endpoint_media = 'https://upload.twitter.com/1.1/';

    /**
     * The API endpoint base to use
     */
    protected static $_endpoint_oauth = 'https://api.twitter.com/';

    /**
     * The Request or access token. Used to sign requests
     */
    protected $_oauth_token = null;

    /**
     * The corresponding request or access token secret
     */
    protected $_oauth_token_secret = null;

    /**
     * The format of data to return from API calls
     */
    protected $_return_format = CODEBIRD_RETURNFORMAT_OBJECT;

    /**
     * The file formats that Twitter accepts as image uploads
     */
    protected $_supported_media_files = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);

    /**
     * The current Codebird version
     */
    protected $_version = '2.7.0';

    /**
     * Auto-detect cURL absence
     */
    protected $_use_curl = true;

    /**
     * Request timeout
     */
    protected $_timeout = 10000;

    /**
     * Connection timeout
     */
    protected $_connectionTimeout = 3000;

    /**
     * Proxy
     */
    protected $_proxy = array();

    /**
     *
     * Class constructor
     *
     */
    public function __construct()
    {
        // Pre-define $_use_curl depending on cURL availability
        $this->setUseCurl(function_exists('curl_init'));
    }

    /**
     * Returns singleton class instance
     * Always use this method unless you're working with multiple authenticated users at once
     *
     * @return Codebird The instance
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Sets the OAuth consumer key and secret (App key)
     *
     * @param string $key    OAuth consumer key
     * @param string $secret OAuth consumer secret
     *
     * @return void
     */
    public static function setConsumerKey($key, $secret)
    {
        self::$_oauth_consumer_key    = $key;
        self::$_oauth_consumer_secret = $secret;
    }

    /**
     * Sets the OAuth2 app-only auth bearer token
     *
     * @param string $token OAuth2 bearer token
     *
     * @return void
     */
    public static function setBearerToken($token)
    {
        self::$_oauth_bearer_token = $token;
    }

    /**
     * Gets the current Codebird version
     *
     * @return string The version number
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Sets the OAuth request or access token and secret (User key)
     *
     * @param string $token  OAuth request or access token
     * @param string $secret OAuth request or access token secret
     *
     * @return void
     */
    public function setToken($token, $secret)
    {
        $this->_oauth_token        = $token;
        $this->_oauth_token_secret = $secret;
    }

    /**
     * Forgets the OAuth request or access token and secret (User key)
     *
     * @return bool
     */
    public function logout()
    {
        $this->_oauth_token =
        $this->_oauth_token_secret = null;

        return true;
    }

    /**
     * Sets if codebird should use cURL
     *
     * @param bool $use_curl Request uses cURL or not
     *
     * @return void
     */
    public function setUseCurl($use_curl)
    {
        if ($use_curl && ! function_exists('curl_init')) {
            throw new \Exception('To use cURL, the PHP curl extension must be available.');
        }

        $this->_use_curl = (bool) $use_curl;
    }

    /**
     * Sets request timeout in milliseconds
     *
     * @param int $timeout Request timeout in milliseconds
     *
     * @return void
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = (int) $timeout;
    }

    /**
     * Sets connection timeout in milliseconds
     *
     * @param int $timeout Connection timeout in milliseconds
     *
     * @return void
     */
    public function setConnectionTimeout($timeout)
    {
        $this->_connectionTimeout = (int) $timeout;
    }

    /**
     * Sets the format for API replies
     *
     * @param int $return_format One of these:
     *                           CODEBIRD_RETURNFORMAT_OBJECT (default)
     *                           CODEBIRD_RETURNFORMAT_ARRAY
     *
     * @return void
     */
    public function setReturnFormat($return_format)
    {
        $this->_return_format = $return_format;
    }

    /**
     * Sets the proxy
     *
     * @param string $host Proxy host
     * @param int    $port Proxy port
     *
     * @return void
     */
    public function setProxy($host, $port)
    {
        $this->_proxy['host'] = $host;
        $this->_proxy['port'] = $port;
    }

    /**
     * Sets the proxy authentication
     *
     * @param string $authentication Proxy authentication
     *
     * @return void
     */
    public function setProxyAuthentication($authentication)
    {
        $this->_proxy['authentication'] = $authentication;
    }

    /**
     * Get allowed API methods, sorted by GET or POST
     * Watch out for multiple-method "account/settings"!
     *
     * @return array $apimethods
     */
    public function getApiMethods()
    {
        static $httpmethods = array(
            'GET' => array(
                'account/settings',
                'account/verify_credentials',
                'application/rate_limit_status',
                'blocks/ids',
                'blocks/list',
                'direct_messages',
                'direct_messages/sent',
                'direct_messages/show',
                'favorites/list',
                'followers/ids',
                'followers/list',
                'friends/ids',
                'friends/list',
                'friendships/incoming',
                'friendships/lookup',
                'friendships/lookup',
                'friendships/no_retweets/ids',
                'friendships/outgoing',
                'friendships/show',
                'geo/id/:place_id',
                'geo/reverse_geocode',
                'geo/search',
                'geo/similar_places',
                'help/configuration',
                'help/languages',
                'help/privacy',
                'help/tos',
                'lists/list',
                'lists/members',
                'lists/members/show',
                'lists/memberships',
                'lists/ownerships',
                'lists/show',
                'lists/statuses',
                'lists/subscribers',
                'lists/subscribers/show',
                'lists/subscriptions',
                'mutes/users/ids',
                'mutes/users/list',
                'oauth/authenticate',
                'oauth/authorize',
                'saved_searches/list',
                'saved_searches/show/:id',
                'search/tweets',
                'statuses/home_timeline',
                'statuses/mentions_timeline',
                'statuses/oembed',
                'statuses/retweeters/ids',
                'statuses/retweets/:id',
                'statuses/retweets_of_me',
                'statuses/show/:id',
                'statuses/user_timeline',
                'trends/available',
                'trends/closest',
                'trends/place',
                'users/contributees',
                'users/contributors',
                'users/profile_banner',
                'users/search',
                'users/show',
                'users/suggestions',
                'users/suggestions/:slug',
                'users/suggestions/:slug/members'
            ),
            'POST' => array(
                'account/remove_profile_banner',
                'account/settings__post',
                'account/update_delivery_device',
                'account/update_profile',
                'account/update_profile_background_image',
                'account/update_profile_banner',
                'account/update_profile_colors',
                'account/update_profile_image',
                'blocks/create',
                'blocks/destroy',
                'direct_messages/destroy',
                'direct_messages/new',
                'favorites/create',
                'favorites/destroy',
                'friendships/create',
                'friendships/destroy',
                'friendships/update',
                'lists/create',
                'lists/destroy',
                'lists/members/create',
                'lists/members/create_all',
                'lists/members/destroy',
                'lists/members/destroy_all',
                'lists/subscribers/create',
                'lists/subscribers/destroy',
                'lists/update',
                'media/upload',
                'mutes/users/create',
                'mutes/users/destroy',
                'oauth/access_token',
                'oauth/request_token',
                'oauth2/invalidate_token',
                'oauth2/token',
                'saved_searches/create',
                'saved_searches/destroy/:id',
                'statuses/destroy/:id',
                'statuses/lookup',
                'statuses/retweet/:id',
                'statuses/update',
                'statuses/update_with_media', // deprecated, use media/upload
                'users/lookup',
                'users/report_spam'
            )
        );
        return $httpmethods;
    }

    /**
     * Main API handler working on any requests you issue
     *
     * @param string $fn    The member function you called
     * @param array $params The parameters you sent along
     *
     * @return mixed The API reply encoded in the set return_format
     */

    public function __call($fn, $params)
    {
        // parse parameters
        $apiparams = $this->_parseApiParams($params);

        // stringify null and boolean parameters
        $apiparams = $this->_stringifyNullBoolParams($apiparams);

        $app_only_auth = false;
        if (count($params) > 1) {
            // convert app_only_auth param to bool
            $app_only_auth = !! $params[1];
        }

        // reset token when requesting a new token
        // (causes 401 for signature error on subsequent requests)
        if ($fn === 'oauth_requestToken') {
            $this->setToken(null, null);
        }

        // map function name to API method
        list($method, $method_template) = $this->_mapFnToApiMethod($fn, $apiparams);

        $httpmethod = $this->_detectMethod($method_template, $apiparams);
        $multipart  = $this->_detectMultipart($method_template);

        return $this->_callApi(
            $httpmethod,
            $method,
            $apiparams,
            $multipart,
            $app_only_auth
        );
    }


    /**
     * __call() helpers
     */

    /**
     * Parse given params, detect query-style params
     *
     * @param array|string $params Parameters to parse
     *
     * @return array $apiparams
     */
    protected function _parseApiParams($params)
    {
        $apiparams = array();
        if (count($params) === 0) {
            return $apiparams;
        }

        if (is_array($params[0])) {
            // given parameters are array
            $apiparams = $params[0];
            if (! is_array($apiparams)) {
                $apiparams = array();
            }
            return $apiparams;
        }

        // user gave us query-style params
        parse_str($params[0], $apiparams);
        if (! is_array($apiparams)) {
            $apiparams = array();
        }

        if (! get_magic_quotes_gpc()) {
            return $apiparams;
        }

        // remove auto-added slashes recursively if on magic quotes steroids
        foreach($apiparams as $key => $value) {
            if (is_array($value)) {
                $apiparams[$key] = array_map('stripslashes', $value);
            } else {
                $apiparams[$key] = stripslashes($value);
            }
        }

        return $apiparams;
    }

    /**
     * Replace null and boolean parameters with their string representations
     *
     * @param array $apiparams Parameter array to replace in
     *
     * @return array $apiparams
     */
    protected function _stringifyNullBoolParams($apiparams)
    {
        foreach ($apiparams as $key => $value) {
            if (! is_scalar($value)) {
                // no need to try replacing arrays
                continue;
            }
            if (is_null($value)) {
                $apiparams[$key] = 'null';
            } elseif (is_bool($value)) {
                $apiparams[$key] = $value ? 'true' : 'false';
            }
        }

        return $apiparams;
    }

    /**
     * Maps called PHP magic method name to Twitter API method
     *
     * @param string $fn              Function called
     * @param array  $apiparams byref API parameters
     *
     * @return array (string method, string method_template)
     */
    protected function _mapFnToApiMethod($fn, &$apiparams)
    {
        // replace _ by /
        $method = $this->_mapFnInsertSlashes($fn);

        // undo replacement for URL parameters
        $method = $this->_mapFnRestoreParamUnderscores($method);

        // replace AA by URL parameters
        $method_template = $method;
        $match           = array();
        if (preg_match('/[A-Z_]{2,}/', $method, $match)) {
            foreach ($match as $param) {
                $param_l = strtolower($param);
                $method_template = str_replace($param, ':' . $param_l, $method_template);
                if (! isset($apiparams[$param_l])) {
                    for ($i = 0; $i < 26; $i++) {
                        $method_template = str_replace(chr(65 + $i), '_' . chr(97 + $i), $method_template);
                    }
                    throw new \Exception(
                        'To call the templated method "' . $method_template
                        . '", specify the parameter value for "' . $param_l . '".'
                    );
                }
                $method  = str_replace($param, $apiparams[$param_l], $method);
                unset($apiparams[$param_l]);
            }
        }

        // replace A-Z by _a-z
        for ($i = 0; $i < 26; $i++) {
            $method  = str_replace(chr(65 + $i), '_' . chr(97 + $i), $method);
            $method_template = str_replace(chr(65 + $i), '_' . chr(97 + $i), $method_template);
        }

        return array($method, $method_template);
    }

    /**
     * API method mapping: Replaces _ with / character
     *
     * @param string $fn Function called
     *
     * @return string API method to call
     */
    protected function _mapFnInsertSlashes($fn)
    {
        $path   = explode('_', $fn);
        $method = implode('/', $path);

        return $method;
    }

    /**
     * API method mapping: Restore _ character in named parameters
     *
     * @param string $method API method to call
     *
     * @return string API method with restored underscores
     */
    protected function _mapFnRestoreParamUnderscores($method)
    {
        $url_parameters_with_underscore = array('screen_name', 'place_id');
        foreach ($url_parameters_with_underscore as $param) {
            $param = strtoupper($param);
            $replacement_was = str_replace('_', '/', $param);
            $method = str_replace($replacement_was, $param, $method);
        }

        return $method;
    }


    /**
     * Uncommon API methods
     */

    /**
     * Gets the OAuth authenticate URL for the current request token
     *
     * @param optional bool   $force_login Whether to force the user to enter their login data
     * @param optional string $screen_name Screen name to repopulate the user name with
     * @param optional string $type        'authenticate' or 'authorize', to avoid duplicate code
     *
     * @return string The OAuth authenticate/authorize URL
     */
    public function oauth_authenticate($force_login = NULL, $screen_name = NULL, $type = 'authenticate')
    {
        if (! in_array($type, array('authenticate', 'authorize'))) {
            throw new \Exception('To get the ' . $type . ' URL, use the correct third parameter, or omit it.');
        }
        if ($this->_oauth_token === null) {
            throw new \Exception('To get the ' . $type . ' URL, the OAuth token must be set.');
        }
        $url = self::$_endpoint_oauth . 'oauth/' . $type . '?oauth_token=' . $this->_url($this->_oauth_token);
        if ($force_login) {
            $url .= "&force_login=1";
        }
        if ($screen_name) {
            $url .= "&screen_name=" . $screen_name;
        }
        return $url;
    }

    /**
     * Gets the OAuth authorize URL for the current request token
     * @param optional bool   $force_login Whether to force the user to enter their login data
     * @param optional string $screen_name Screen name to repopulate the user name with
     *
     * @return string The OAuth authorize URL
     */
    public function oauth_authorize($force_login = NULL, $screen_name = NULL)
    {
        return $this->oauth_authenticate($force_login, $screen_name, 'authorize');
    }

    /**
     * Gets the OAuth bearer token
     *
     * @return string The OAuth bearer token
     */

    public function oauth2_token()
    {
        if ($this->_use_curl) {
            return $this->_oauth2TokenCurl();
        }
        return $this->_oauth2TokenNoCurl();
    }

    /**
     * Gets a cURL handle
     * @param string $url the URL for the curl initialization
     * @return cURL handle
     */
    protected function getCurlInitialization($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
        curl_setopt(
            $ch, CURLOPT_USERAGENT,
            'codebird-php ' . $this->getVersion() . ' by Jublo Solutions <support@jublo.net>'
        );

        if ($this->hasProxy()) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, $this->getProxyHost());
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->getProxyPort());

            if ($this->hasProxyAuthentication()) {
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->getProxyAuthentication());
            }
        }

        return $ch;
    }

    /**
     * Gets a non cURL initialization
     * @param string $url            the URL for the curl initialization
     * @param array  $contextOptions the options for the stream context
     * @param string $hostname       the hostname to verify the SSL FQDN for
     * @return the read data
     */
    protected function getNoCurlInitialization($url, $contextOptions, $hostname = '')
    {
        $httpOptions = array();
        
        $httpOptions['header'] = array(
            'User-Agent: codebird-php ' . $this->getVersion() . ' by Jublo Solutions <support@jublo.net>'
        );

        $httpOptions['ssl'] = array(
            'verify_peer'  => true,
            'cafile'       => __DIR__ . '/cacert.pem',
            'verify_depth' => 5,
            'peer_name'    => $hostname
        );

        if ($this->hasProxy()) {
            $httpOptions['request_fulluri'] = true;
            $httpOptions['proxy'] = $this->getProxyHost() . ':' . $this->getProxyPort();

            if ($this->hasProxyAuthentication()) {
                $httpOptions['header'][] =
                    'Proxy-Authorization: Basic ' . base64_encode($this->getProxyAuthentication());
            }
        }

        // merge the http options with the context options
        $options = array_merge_recursive(
            $contextOptions,
            array('http' => $httpOptions)
        );

        // silent the file_get_contents function
        $content = @file_get_contents($url, false, stream_context_create($options));

        $headers = array();
        // API is responding
        if (isset($http_response_header)) {
            $headers = $http_response_header;
        }

        return array(
            $content,
            $headers
        );
    }

    protected function hasProxy()
    {
        if ($this->getProxyHost() === null) {
            return false;
        }

        if ($this->getProxyPort() === null) {
            return false;
        }

        return true;
    }

    protected function hasProxyAuthentication()
    {
        if ($this->getProxyAuthentication() === null) {
            return false;
        }

        return true;
    }

    /**
     * Gets the proxy host
     *
     * @return string The proxy host
     */
    protected function getProxyHost()
    {
        return $this->getProxyData('host');
    }

    /**
     * Gets the proxy port
     *
     * @return string The proxy port
     */
    protected function getProxyPort()
    {
        return $this->getProxyData('port');
    }

    /**
     * Gets the proxy authentication
     *
     * @return string The proxy authentication
     */
    protected function getProxyAuthentication()
    {
        return $this->getProxyData('authentication');
    }

    private function getProxyData($name)
    {
        if (empty($this->_proxy[$name])) {
            return null;
        }

        return $this->_proxy[$name];
    }

    /**
     * Gets the OAuth bearer token, using cURL
     *
     * @return string The OAuth bearer token
     */

    protected function _oauth2TokenCurl()
    {
        if (self::$_oauth_consumer_key === null) {
            throw new \Exception('To obtain a bearer token, the consumer key must be set.');
        }
        $post_fields = array(
            'grant_type' => 'client_credentials'
        );
        $url = self::$_endpoint_oauth . 'oauth2/token';
        $ch = $this->getCurlInitialization($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        curl_setopt($ch, CURLOPT_USERPWD, self::$_oauth_consumer_key . ':' . self::$_oauth_consumer_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Expect:'
        ));
        $result = curl_exec($ch);

        // catch request errors
        if ($result === false) {
            throw new \Exception('Request error for bearer token: ' . curl_error($ch));
        }

        // certificate validation results
        $validation_result = curl_errno($ch);
        $this->_validateSslCertificate($validation_result);

        $httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $reply = $this->_parseBearerReply($result, $httpstatus);
        return $reply;
    }

    /**
     * Gets the OAuth bearer token, without cURL
     *
     * @return string The OAuth bearer token
     */

    protected function _oauth2TokenNoCurl()
    {
        if (self::$_oauth_consumer_key == null) {
            throw new \Exception('To obtain a bearer token, the consumer key must be set.');
        }

        $url      = self::$_endpoint_oauth . 'oauth2/token';
        $hostname = parse_url($url, PHP_URL_HOST);

        $contextOptions = array(
            'http' => array(
                'method'           => 'POST',
                'protocol_version' => '1.1',
                'header'           => "Accept: */*\r\n"
                    . 'Authorization: Basic '
                    . base64_encode(
                        self::$_oauth_consumer_key
                        . ':'
                        . self::$_oauth_consumer_secret
                    ),
                'timeout'          => $this->_timeout / 1000,
                'content'          => 'grant_type=client_credentials',
                'ignore_errors'    => true
            )
        );
        list($reply, $headers) = $this->getNoCurlInitialization($url, $contextOptions, $hostname);
        $result  = '';
        foreach ($headers as $header) {
            $result .= $header . "\r\n";
        }
        $result .= "\r\n" . $reply;

        // find HTTP status
        $httpstatus = '500';
        $match      = array();
        if (!empty($headers[0]) && preg_match('/HTTP\/\d\.\d (\d{3})/', $headers[0], $match)) {
            $httpstatus = $match[1];
        }

        $reply = $this->_parseBearerReply($result, $httpstatus);
        return $reply;
    }


    /**
     * General helpers to avoid duplicate code
     */

    /**
     * Parse oauth2_token reply and set bearer token, if found
     *
     * @param string $result     Raw HTTP response
     * @param int    $httpstatus HTTP status code
     *
     * @return array|object reply
     */
    protected function _parseBearerReply($result, $httpstatus)
    {
        list($headers, $reply) = $this->_parseApiHeaders($result);
        $reply                 = $this->_parseApiReply($reply);
        $rate                  = $this->_getRateLimitInfo($headers);
        switch ($this->_return_format) {
            case CODEBIRD_RETURNFORMAT_ARRAY:
                $reply['httpstatus'] = $httpstatus;
                $reply['rate']       = $rate;
                if ($httpstatus === 200) {
                    self::setBearerToken($reply['access_token']);
                }
                break;
            case CODEBIRD_RETURNFORMAT_JSON:
                if ($httpstatus === 200) {
                    $parsed = json_decode($reply);
                    self::setBearerToken($parsed->access_token);
                }
                break;
            case CODEBIRD_RETURNFORMAT_OBJECT:
                $reply->httpstatus = $httpstatus;
                $reply->rate       = $rate;
                if ($httpstatus === 200) {
                    self::setBearerToken($reply->access_token);
                }
                break;
        }
        return $reply;
    }

    /**
     * Extract rate-limiting data from response headers
     *
     * @param array $headers The CURL response headers
     *
     * @return null|array The rate-limiting information
     */
    protected function _getRateLimitInfo($headers)
    {
        if (! isset($headers['x-rate-limit-limit'])) {
            return null;
        }
        return array(
            'limit'     => $headers['x-rate-limit-limit'],
            'remaining' => $headers['x-rate-limit-remaining'],
            'reset'     => $headers['x-rate-limit-reset']
        );
    }

    /**
     * Check if there were any SSL certificate errors
     *
     * @param int $validation_result The curl error number
     *
     * @return void
     */
    protected function _validateSslCertificate($validation_result)
    {
        if (in_array(
                $validation_result,
                array(
                    CURLE_SSL_CERTPROBLEM,
                    CURLE_SSL_CACERT,
                    CURLE_SSL_CACERT_BADFILE,
                    CURLE_SSL_CRL_BADFILE,
                    CURLE_SSL_ISSUER_ERROR
                )
            )
        ) {
            throw new \Exception(
                'Error ' . $validation_result
                . ' while validating the Twitter API certificate.'
            );
        }
    }

    /**
     * Signing helpers
     */

    /**
     * URL-encodes the given data
     *
     * @param mixed $data
     *
     * @return mixed The encoded data
     */
    protected function _url($data)
    {
        if (is_array($data)) {
            return array_map(array(
                $this,
                '_url'
            ), $data);
        } elseif (is_scalar($data)) {
            return str_replace(array(
                '+',
                '!',
                '*',
                "'",
                '(',
                ')'
            ), array(
                ' ',
                '%21',
                '%2A',
                '%27',
                '%28',
                '%29'
            ), rawurlencode($data));
        } else {
            return '';
        }
    }

    /**
     * Gets the base64-encoded SHA1 hash for the given data
     *
     * @param string $data The data to calculate the hash from
     *
     * @return string The hash
     */
    protected function _sha1($data)
    {
        if (self::$_oauth_consumer_secret === null) {
            throw new \Exception('To generate a hash, the consumer secret must be set.');
        }
        if (!function_exists('hash_hmac')) {
            throw new \Exception('To generate a hash, the PHP hash extension must be available.');
        }
        return base64_encode(hash_hmac(
            'sha1',
            $data,
            self::$_oauth_consumer_secret
            . '&'
            . ($this->_oauth_token_secret != null
                ? $this->_oauth_token_secret
                : ''
            ),
            true
        ));
    }

    /**
     * Generates a (hopefully) unique random string
     *
     * @param int optional $length The length of the string to generate
     *
     * @return string The random string
     */
    protected function _nonce($length = 8)
    {
        if ($length < 1) {
            throw new \Exception('Invalid nonce length.');
        }
        return substr(md5(microtime(true)), 0, $length);
    }

    /**
     * Generates an OAuth signature
     *
     * @param string          $httpmethod   Usually either 'GET' or 'POST' or 'DELETE'
     * @param string          $method       The API method to call
     * @param array  optional $params       The API call parameters, associative
     * @param bool   optional append_to_get Whether to append the OAuth params to GET
     *
     * @return string Authorization HTTP header
     */
    protected function _sign($httpmethod, $method, $params = array(), $append_to_get = false)
    {
        if (self::$_oauth_consumer_key === null) {
            throw new \Exception('To generate a signature, the consumer key must be set.');
        }
        $sign_params      = array(
            'consumer_key'     => self::$_oauth_consumer_key,
            'version'          => '1.0',
            'timestamp'        => time(),
            'nonce'            => $this->_nonce(),
            'signature_method' => 'HMAC-SHA1'
        );
        $sign_base_params = array();
        foreach ($sign_params as $key => $value) {
            $sign_base_params['oauth_' . $key] = $this->_url($value);
        }
        if ($this->_oauth_token != null) {
            $sign_base_params['oauth_token'] = $this->_url($this->_oauth_token);
        }
        $oauth_params = $sign_base_params;
        foreach ($params as $key => $value) {
            $sign_base_params[$key] = $this->_url($value);
        }
        ksort($sign_base_params);
        $sign_base_string = '';
        foreach ($sign_base_params as $key => $value) {
            $sign_base_string .= $key . '=' . $value . '&';
        }
        $sign_base_string = substr($sign_base_string, 0, -1);
        $signature        = $this->_sha1($httpmethod . '&' . $this->_url($method) . '&' . $this->_url($sign_base_string));

        $params = $append_to_get ? $sign_base_params : $oauth_params;
        $params['oauth_signature'] = $signature;
        $keys = $params;
        ksort($keys);
        if ($append_to_get) {
            $authorization = '';
            foreach ($keys as $key => $value) {
                $authorization .= $key . '="' . $this->_url($value) . '", ';
            }
            return substr($authorization, 0, -1);
        }
        $authorization = 'OAuth ';
        foreach ($keys as $key => $value) {
            $authorization .= $key . "=\"" . $this->_url($value) . "\", ";
        }
        return substr($authorization, 0, -2);
    }

    /**
     * Detects HTTP method to use for API call
     *
     * @param string $method The API method to call
     * @param array  $params The parameters to send along
     *
     * @return string The HTTP method that should be used
     */
    protected function _detectMethod($method, $params)
    {
        // multi-HTTP method endpoints
        switch ($method) {
            case 'account/settings':
            case 'account/login_verification_enrollment':
            case 'account/login_verification_request':
                $method = count($params) > 0 ? $method . '__post' : $method;
                break;
        }

        $apimethods = $this->getApiMethods();
        foreach ($apimethods as $httpmethod => $methods) {
            if (in_array($method, $methods)) {
                return $httpmethod;
            }
        }
        throw new \Exception('Can\'t find HTTP method to use for "' . $method . '".');
    }

    /**
     * Detects if API call should use multipart/form-data
     *
     * @param string $method The API method to call
     *
     * @return bool Whether the method should be sent as multipart
     */
    protected function _detectMultipart($method)
    {
        $multiparts = array(
            // Tweets
            'statuses/update_with_media',
            'media/upload',

            // Users
            'account/update_profile_background_image',
            'account/update_profile_image',
            'account/update_profile_banner'
        );
        return in_array($method, $multiparts);
    }

    /**
     * Detect filenames in upload parameters,
     * build multipart request from upload params
     *
     * @param string $method  The API method to call
     * @param array  $params  The parameters to send along
     *
     * @return null|string
     */
    protected function _buildMultipart($method, $params)
    {
        // well, files will only work in multipart methods
        if (! $this->_detectMultipart($method)) {
            return;
        }

        // only check specific parameters
        $possible_files = array(
            // Tweets
            'statuses/update_with_media' => 'media[]',
            'media/upload' => 'media',
            // Accounts
            'account/update_profile_background_image' => 'image',
            'account/update_profile_image' => 'image',
            'account/update_profile_banner' => 'banner'
        );
        // method might have files?
        if (! in_array($method, array_keys($possible_files))) {
            return;
        }

        $possible_files = explode(' ', $possible_files[$method]);

        $multipart_border = '--------------------' . $this->_nonce();
        $multipart_request = '';

        foreach ($params as $key => $value) {
            // is it an array?
            if (is_array($value)) {
                throw new \Exception('Using URL-encoded parameters is not supported for uploading media.');
            }
            $multipart_request .=
                '--' . $multipart_border . "\r\n"
                . 'Content-Disposition: form-data; name="' . $key . '"';

            // check for filenames
            if (in_array($key, $possible_files)) {
                if (// is it a file, a readable one?
                    @file_exists($value)
                    && @is_readable($value)

                    // is it a valid image?
                    && $data = @getimagesize($value)
                ) {
                    // is it a supported image format?
                    if (in_array($data[2], $this->_supported_media_files)) {
                        // try to read the file
                        ob_start();
                        readfile($value);
                        $data = ob_get_contents();
                        ob_end_clean();
                        if (strlen($data) === 0) {
                            continue;
                        }
                        $value = $data;
                    }
                } elseif (// is it a remote file?
                    filter_var($value, FILTER_VALIDATE_URL)
                    && preg_match('/^https?:\/\//', $value)
                ) {
                    // try to fetch the file
                    if ($this->_use_curl) {
                        $ch = $this->getCurlInitialization($value);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        // no SSL validation for downloading media
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        // use hardcoded download timeouts for now
                        curl_setopt($ch, _CURLOPT_TIMEOUT_MS, 5000);
                        curl_setopt($ch, _CURLOPT_CONNECTTIMEOUT_MS, 2000);
                        $result = curl_exec($ch);
                        if ($result !== false) {
                            $value = $result;
                        }
                    } else {
                        $contextOptions = array(
                            'http' => array(
                                'method'           => 'GET',
                                'protocol_version' => '1.1',
                                'timeout'          => 5000
                            ),
                            'ssl' => array(
                                'verify_peer'  => false
                            )
                        );
                        list($result) = $this->getNoCurlInitialization($value, $contextOptions);
                        if ($result !== false) {
                            $value = $result;
                        }
                    }
                }
            }

            $multipart_request .=
                "\r\n\r\n" . $value . "\r\n";
        }
        $multipart_request .= '--' . $multipart_border . '--';

        return $multipart_request;
    }

    /**
     * Detects if API call should use media endpoint
     *
     * @param string $method The API method to call
     *
     * @return bool Whether the method is defined in media API
     */
    protected function _detectMedia($method) {
        $medias = array(
            'media/upload'
        );
        return in_array($method, $medias);
    }

    /**
     * Builds the complete API endpoint url
     *
     * @param string $method The API method to call
     *
     * @return string The URL to send the request to
     */
    protected function _getEndpoint($method)
    {
        if (substr($method, 0, 5) === 'oauth') {
            $url = self::$_endpoint_oauth . $method;
        } elseif ($this->_detectMedia($method)) {
            $url = self::$_endpoint_media . $method . '.json';
        } else {
            $url = self::$_endpoint . $method . '.json';
        }
        return $url;
    }

    /**
     * Calls the API
     *
     * @param string          $httpmethod      The HTTP method to use for making the request
     * @param string          $method          The API method to call
     * @param array  optional $params          The parameters to send along
     * @param bool   optional $multipart       Whether to use multipart/form-data
     * @param bool   optional $app_only_auth   Whether to use app-only bearer authentication
     *
     * @return mixed The API reply, encoded in the set return_format
     */

    protected function _callApi($httpmethod, $method, $params = array(), $multipart = false, $app_only_auth = false)
    {
        if (! $app_only_auth
            && $this->_oauth_token === null
            && substr($method, 0, 5) !== 'oauth'
        ) {
                throw new \Exception('To call this API, the OAuth access token must be set.');
        }
        if ($this->_use_curl) {
            return $this->_callApiCurl($httpmethod, $method, $params, $multipart, $app_only_auth);
        }
        return $this->_callApiNoCurl($httpmethod, $method, $params, $multipart, $app_only_auth);
    }

    /**
     * Calls the API using cURL
     *
     * @param string          $httpmethod    The HTTP method to use for making the request
     * @param string          $method        The API method to call
     * @param array  optional $params        The parameters to send along
     * @param bool   optional $multipart     Whether to use multipart/form-data
     * @param bool   optional $app_only_auth Whether to use app-only bearer authentication
     *
     * @return mixed The API reply, encoded in the set return_format
     */

    protected function _callApiCurl(
        $httpmethod, $method, $params = array(), $multipart = false, $app_only_auth = false
    )
    {
        list ($authorization, $url, $params, $request_headers)
            = $this->_callApiPreparations(
                $httpmethod, $method, $params, $multipart, $app_only_auth
            );

        $ch                = $this->getCurlInitialization($url);
        $request_headers[] = 'Authorization: ' . $authorization;
        $request_headers[] = 'Expect:';

        if ($httpmethod !== 'GET') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        if (isset($this->_timeout)) {
            curl_setopt($ch, _CURLOPT_TIMEOUT_MS, $this->_timeout);
        }

        if (isset($this->_connectionTimeout)) {
            curl_setopt($ch, _CURLOPT_CONNECTTIMEOUT_MS, $this->_connectionTimeout);
        }

        $result = curl_exec($ch);

        // catch request errors
        if ($result === false) {
            throw new \Exception('Request error for API call: ' . curl_error($ch));
        }

        // certificate validation results
        $validation_result = curl_errno($ch);
        $this->_validateSslCertificate($validation_result);

        $httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        list($headers, $reply) = $this->_parseApiHeaders($result);
        $reply                 = $this->_parseApiReply($reply);
        $rate                  = $this->_getRateLimitInfo($headers);

        switch ($this->_return_format) {
            case CODEBIRD_RETURNFORMAT_ARRAY:
                $reply['httpstatus'] = $httpstatus;
                $reply['rate']       = $rate;
                break;
            case CODEBIRD_RETURNFORMAT_OBJECT:
                $reply->httpstatus = $httpstatus;
                $reply->rate       = $rate;
                break;
        }
        return $reply;
    }

    /**
     * Calls the API without cURL
     *
     * @param string          $httpmethod      The HTTP method to use for making the request
     * @param string          $method          The API method to call
     * @param array  optional $params          The parameters to send along
     * @param bool   optional $multipart       Whether to use multipart/form-data
     * @param bool   optional $app_only_auth   Whether to use app-only bearer authentication
     *
     * @return mixed The API reply, encoded in the set return_format
     */

    protected function _callApiNoCurl(
        $httpmethod, $method, $params = array(), $multipart = false, $app_only_auth = false
    )
    {
        list ($authorization, $url, $params, $request_headers)
            = $this->_callApiPreparations(
                $httpmethod, $method, $params, $multipart, $app_only_auth
            );

        $hostname          = parse_url($url, PHP_URL_HOST);
        $request_headers[] = 'Authorization: ' . $authorization;
        $request_headers[] = 'Accept: */*';
        $request_headers[] = 'Connection: Close';
        if ($httpmethod !== 'GET' && ! $multipart) {
            $request_headers[]  = 'Content-Type: application/x-www-form-urlencoded';
        }

        $contextOptions = array(
            'http' => array(
                'method'           => $httpmethod,
                'protocol_version' => '1.1',
                'header'           => implode("\r\n", $request_headers),
                'timeout'          => $this->_timeout / 1000,
                'content'          => $httpmethod === 'POST' ? $params : null,
                'ignore_errors'    => true
            )
        );

        list($reply, $headers) = $this->getNoCurlInitialization($url, $contextOptions, $hostname);
        $result  = '';
        foreach ($headers as $header) {
            $result .= $header . "\r\n";
        }
        $result .= "\r\n" . $reply;

        // find HTTP status
        $httpstatus = '500';
        $match      = array();
        if (!empty($headers[0]) && preg_match('/HTTP\/\d\.\d (\d{3})/', $headers[0], $match)) {
            $httpstatus = $match[1];
        }

        list($headers, $reply) = $this->_parseApiHeaders($result);
        $reply                 = $this->_parseApiReply($reply);
        $rate                  = $this->_getRateLimitInfo($headers);
        switch ($this->_return_format) {
            case CODEBIRD_RETURNFORMAT_ARRAY:
                $reply['httpstatus'] = $httpstatus;
                $reply['rate']       = $rate;
                break;
            case CODEBIRD_RETURNFORMAT_OBJECT:
                $reply->httpstatus = $httpstatus;
                $reply->rate       = $rate;
                break;
        }
        return $reply;
    }

    /**
     * Do preparations to make the API call
     *
     * @param string  $httpmethod      The HTTP method to use for making the request
     * @param string  $method          The API method to call
     * @param array   $params          The parameters to send along
     * @param bool    $multipart       Whether to use multipart/form-data
     * @param bool    $app_only_auth   Whether to use app-only bearer authentication
     *
     * @return array (string authorization, string url, array params, array request_headers)
     */
    protected function _callApiPreparations(
        $httpmethod, $method, $params, $multipart, $app_only_auth
    )
    {
        $authorization = null;
        $url           = $this->_getEndpoint($method);
        $request_headers = array();
        if ($httpmethod === 'GET') {
            if (! $app_only_auth) {
                $authorization = $this->_sign($httpmethod, $url, $params);
            }
            if (json_encode($params) !== '{}'
                && json_encode($params) !== '[]'
            ) {
                $url .= '?' . http_build_query($params);
            }
        } else {
            if ($multipart) {
                if (! $app_only_auth) {
                    $authorization = $this->_sign($httpmethod, $url, array());
                }
                $params = $this->_buildMultipart($method, $params);
            } else {
                if (! $app_only_auth) {
                    $authorization = $this->_sign($httpmethod, $url, $params);
                }
                $params        = http_build_query($params);
            }
            if ($multipart) {
                $first_newline      = strpos($params, "\r\n");
                $multipart_boundary = substr($params, 2, $first_newline - 2);
                $request_headers[]  = 'Content-Type: multipart/form-data; boundary='
                    . $multipart_boundary;
            }
        }
        if ($app_only_auth) {
            if (self::$_oauth_consumer_key === null
                && self::$_oauth_bearer_token === null
            ) {
                throw new \Exception('To make an app-only auth API request, consumer key or bearer token must be set.');
            }
            // automatically fetch bearer token, if necessary
            if (self::$_oauth_bearer_token === null) {
                $this->oauth2_token();
            }
            $authorization = 'Bearer ' . self::$_oauth_bearer_token;
        }

        return array(
            $authorization, $url, $params, $request_headers
        );
    }

    /**
     * Parses the API reply to separate headers from the body
     *
     * @param string $reply The actual raw HTTP request reply
     *
     * @return array (headers, reply)
     */
    protected function _parseApiHeaders($reply) {
        // split headers and body
        $headers = array();
        $reply = explode("\r\n\r\n", $reply, 4);

        // check if using proxy
        $proxy_strings = array();
        $proxy_strings[strtolower('HTTP/1.0 200 Connection Established')] = true;
        $proxy_strings[strtolower('HTTP/1.1 200 Connection Established')] = true;
        if (array_key_exists(strtolower(substr($reply[0], 0, 35)), $proxy_strings)) {
            array_shift($reply);
        } elseif (count($reply) > 2) {
            $headers = array_shift($reply);
            $reply = array(
                $headers,
                implode("\r\n", $reply)
            );
        }

        $headers_array = explode("\r\n", $reply[0]);
        foreach ($headers_array as $header) {
            $header_array = explode(': ', $header, 2);
            $key = $header_array[0];
            $value = '';
            if (count($header_array) > 1) {
                $value = $header_array[1];
            }
            $headers[$key] = $value;
        }

        if (count($reply) > 1) {
            $reply = $reply[1];
        } else {
            $reply = '';
        }

        return array($headers, $reply);
    }

    /**
     * Parses the API reply to encode it in the set return_format
     *
     * @param string $reply The actual HTTP body, JSON-encoded or URL-encoded
     *
     * @return array|object|string The parsed reply
     */
    protected function _parseApiReply($reply)
    {
        $need_array = $this->_return_format === CODEBIRD_RETURNFORMAT_ARRAY;
        if ($reply === '[]') {
            switch ($this->_return_format) {
                case CODEBIRD_RETURNFORMAT_ARRAY:
                    return array();
                case CODEBIRD_RETURNFORMAT_JSON:
                    return '{}';
                case CODEBIRD_RETURNFORMAT_OBJECT:
                    return new \stdClass;
            }
        }
        if (! $parsed = json_decode($reply, $need_array)) {
            if ($reply) {
                if (stripos($reply, '<' . '?xml version="1.0" encoding="UTF-8"?' . '>') === 0) {
                    // we received XML...
                    // since this only happens for errors,
                    // don't perform a full decoding
                    preg_match('/<request>(.*)<\/request>/', $reply, $request);
                    preg_match('/<error>(.*)<\/error>/', $reply, $error);
                    $parsed['request'] = htmlspecialchars_decode($request[1]);
                    $parsed['error'] = htmlspecialchars_decode($error[1]);
                } else {
                    // assume query format
                    $reply = explode('&', $reply);
                    foreach ($reply as $element) {
                        if (stristr($element, '=')) {
                            list($key, $value) = explode('=', $element, 2);
                            $parsed[$key] = $value;
                        } else {
                            $parsed['message'] = $element;
                        }
                    }
                }
            }
            $reply = json_encode($parsed);
        }
        switch ($this->_return_format) {
            case CODEBIRD_RETURNFORMAT_ARRAY:
                return $parsed;
            case CODEBIRD_RETURNFORMAT_JSON:
                return $reply;
            case CODEBIRD_RETURNFORMAT_OBJECT:
                return (object) $parsed;
        }
        return $parsed;
    }
}
