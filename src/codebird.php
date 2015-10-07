<?php

namespace Codebird;

/**
 * A Twitter library in PHP.
 *
 * @package   codebird
 * @version   3.0.0-dev
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
$constants = [
    'CURLE_SSL_CERTPROBLEM' => 58,
    'CURLE_SSL_CACERT' => 60,
    'CURLE_SSL_CACERT_BADFILE' => 77,
    'CURLE_SSL_CRL_BADFILE' => 82,
    'CURLE_SSL_ISSUER_ERROR' => 83
];
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
     * The Streaming API endpoints to use
     */
    protected static $_endpoints_streaming = [
        'public' => 'https://stream.twitter.com/1.1/',
        'user'   => 'https://userstream.twitter.com/1.1/',
        'site'   => 'https://sitestream.twitter.com/1.1/'
    ];

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
    protected $_supported_media_files = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];

    /**
     * The callback to call with any new streaming messages
     */
    protected $_streaming_callback = null;

    /**
     * The current Codebird version
     */
    protected $_version = '3.0.0-dev';

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
     * Remote media download timeout
     */
    protected $_remoteDownloadTimeout = 5000;

    /**
     * Proxy
     */
    protected $_proxy = [];

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
     * Sets remote media download timeout in milliseconds
     *
     * @param int $timeout Remote media timeout in milliseconds
     *
     * @return void
     */
    public function setRemoteDownloadTimeout($timeout)
    {
        $this->_remoteDownloadTimeout = (int) $timeout;
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
     * Sets streaming callback
     *
     * @param callable $callback The streaming callback
     *
     * @return void
     */
    public function setStreamingCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('This is not a proper callback.');
        }
        $this->_streaming_callback = $callback;
    }

    /**
     * Get allowed API methods, sorted by GET or POST
     * Watch out for multiple-method "account/settings"!
     *
     * @return array $apimethods
     */
    public function getApiMethods()
    {
        static $httpmethods = [
            'GET' => [
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
                'site',
                'statuses/firehose',
                'statuses/home_timeline',
                'statuses/mentions_timeline',
                'statuses/oembed',
                'statuses/retweeters/ids',
                'statuses/retweets/:id',
                'statuses/retweets_of_me',
                'statuses/sample',
                'statuses/show/:id',
                'statuses/user_timeline',
                'trends/available',
                'trends/closest',
                'trends/place',
                'user',
                'users/contributees',
                'users/contributors',
                'users/profile_banner',
                'users/search',
                'users/show',
                'users/suggestions',
                'users/suggestions/:slug',
                'users/suggestions/:slug/members'
            ],
            'POST' => [
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
                'statuses/filter',
                'statuses/lookup',
                'statuses/retweet/:id',
                'statuses/update',
                'statuses/update_with_media', // deprecated, use media/upload
                'users/lookup',
                'users/report_spam'
            ]
        ];
        return $httpmethods;
    }

    /**
     * Main API handler working on any requests you issue
     *
     * @param string $fn    The member function you called
     * @param array $params The parameters you sent along
     *
     * @return string The API reply encoded in the set return_format
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
        $apiparams = [];
        if (count($params) === 0) {
            return $apiparams;
        }

        if (is_array($params[0])) {
            // given parameters are array
            $apiparams = $params[0];
            return $apiparams;
        }

        // user gave us query-style params
        parse_str($params[0], $apiparams);
        if (! is_array($apiparams)) {
            $apiparams = [];
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
     * @return string[] (string method, string method_template)
     */
    protected function _mapFnToApiMethod($fn, &$apiparams)
    {
        // replace _ by /
        $method = $this->_mapFnInsertSlashes($fn);

        // undo replacement for URL parameters
        $method = $this->_mapFnRestoreParamUnderscores($method);

        // replace AA by URL parameters
        $method_template = $method;
        $match           = [];
        if (preg_match_all('/[A-Z_]{2,}/', $method, $match)) {
            foreach ($match as $param) {
                $param = $param[0];
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

        return [$method, $method_template];
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
        $url_parameters_with_underscore = ['screen_name', 'place_id'];
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
        if (! in_array($type, ['authenticate', 'authorize'])) {
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
     * @return resource handle
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
            'codebird-php/' . $this->getVersion() . ' +https://github.com/jublonet/codebird-php'
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
     *
     * @param string $url            the URL for the curl initialization
     * @param array  $contextOptions the options for the stream context
     * @param string $hostname       the hostname to verify the SSL FQDN for
     *
     * @return array the read data
     */
    protected function getNoCurlInitialization($url, $contextOptions, $hostname = '')
    {
        $httpOptions = [];

        $httpOptions['header'] = [
            'User-Agent: codebird-php/' . $this->getVersion() . ' +https://github.com/jublonet/codebird-php'
        ];

        $httpOptions['ssl'] = [
            'verify_peer'  => true,
            'cafile'       => __DIR__ . '/cacert.pem',
            'verify_depth' => 5,
            'peer_name'    => $hostname
        ];

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
            ['http' => $httpOptions]
        );

        // concatenate $options['http']['header']
        $options['http']['header'] = implode("\r\n", $options['http']['header']);

        // silent the file_get_contents function
        $content = @file_get_contents($url, false, stream_context_create($options));

        $headers = [];
        // API is responding
        if (isset($http_response_header)) {
            $headers = $http_response_header;
        }

        return [
            $content,
            $headers
        ];
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

    /**
     * @param string $name
     */
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
        $post_fields = [
            'grant_type' => 'client_credentials'
        ];
        $url = self::$_endpoint_oauth . 'oauth2/token';
        $ch = $this->getCurlInitialization($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        curl_setopt($ch, CURLOPT_USERPWD, self::$_oauth_consumer_key . ':' . self::$_oauth_consumer_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Expect:'
        ]);
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

        if ($hostname === false) {
            throw new \Exception('Incorrect API endpoint host.');
        }

        $contextOptions = [
            'http' => [
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
            ]
        ];
        list($reply, $headers) = $this->getNoCurlInitialization($url, $contextOptions, $hostname);
        $result  = '';
        foreach ($headers as $header) {
            $result .= $header . "\r\n";
        }
        $result .= "\r\n" . $reply;

        // find HTTP status
        $httpstatus = '500';
        $match      = [];
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
     * @return string reply
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
                    $parsed = json_decode($reply, false, 512, JSON_BIGINT_AS_STRING);
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
        return [
            'limit'     => $headers['x-rate-limit-limit'],
            'remaining' => $headers['x-rate-limit-remaining'],
            'reset'     => $headers['x-rate-limit-reset']
        ];
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
                [
                    CURLE_SSL_CERTPROBLEM,
                    CURLE_SSL_CACERT,
                    CURLE_SSL_CACERT_BADFILE,
                    CURLE_SSL_CRL_BADFILE,
                    CURLE_SSL_ISSUER_ERROR
                ]
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
            return array_map([
                $this,
                '_url'
            ], $data);
        } elseif (is_scalar($data)) {
            return str_replace([
                '+',
                '!',
                '*',
                "'",
                '(',
                ')'
            ], [
                ' ',
                '%21',
                '%2A',
                '%27',
                '%28',
                '%29'
            ], rawurlencode($data));
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
            . ($this->_oauth_token_secret !== null
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
     * Signature helper
     *
     * @param string $httpmethod   Usually either 'GET' or 'POST' or 'DELETE'
     * @param string $method       The API method to call
     * @param array  $base_params  The signature base parameters
     *
     * @return string signature
     */
    protected function _getSignature($httpmethod, $method, $base_params)
    {
        // convert params to string
        $base_string = '';
        foreach ($base_params as $key => $value) {
            $base_string .= $key . '=' . $value . '&';
        }

        // trim last ampersand
        $base_string = substr($base_string, 0, -1);

        // hash it
        return $this->_sha1(
            $httpmethod . '&' .
            $this->_url($method) . '&' .
            $this->_url($base_string)
        );
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
    protected function _sign($httpmethod, $method, $params = [], $append_to_get = false)
    {
        if (self::$_oauth_consumer_key === null) {
            throw new \Exception('To generate a signature, the consumer key must be set.');
        }
        $sign_base_params = array_map(
            [$this, '_url'],
            [
                'oauth_consumer_key'     => self::$_oauth_consumer_key,
                'oauth_version'          => '1.0',
                'oauth_timestamp'        => time(),
                'oauth_nonce'            => $this->_nonce(),
                'oauth_signature_method' => 'HMAC-SHA1'
            ]
        );
        if ($this->_oauth_token !== null) {
            $sign_base_params['oauth_token'] = $this->_url($this->_oauth_token);
        }
        $oauth_params = $sign_base_params;

        // merge in the non-OAuth params
        $sign_base_params = array_merge(
            $sign_base_params,
            array_map([$this, '_url'], $params)
        );
        ksort($sign_base_params);

        $signature = $this->_getSignature($httpmethod, $method, $sign_base_params);

        $params = $append_to_get ? $sign_base_params : $oauth_params;
        $params['oauth_signature'] = $signature;

        ksort($params);
        if ($append_to_get) {
            $authorization = '';
            foreach ($params as $key => $value) {
                $authorization .= $key . '="' . $this->_url($value) . '", ';
            }
            return substr($authorization, 0, -1);
        }
        $authorization = 'OAuth ';
        foreach ($params as $key => $value) {
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
        $multiparts = [
            // Tweets
            'statuses/update_with_media',
            'media/upload',

            // Users
            'account/update_profile_background_image',
            'account/update_profile_image',
            'account/update_profile_banner'
        ];
        return in_array($method, $multiparts);
    }

    /**
     * Merge multipart string from parameters array
     *
     * @param array  $possible_files List of possible filename parameters
     * @param string $border         The multipart border
     * @param array  $params         The parameters to send along
     *
     * @return string request
     */
    protected function _getMultipartRequestFromParams($possible_files, $border, $params)
    {
        $request = '';
        foreach ($params as $key => $value) {
            // is it an array?
            if (is_array($value)) {
                throw new \Exception('Using URL-encoded parameters is not supported for uploading media.');
            }
            $request .=
                '--' . $border . "\r\n"
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
                        $data = @file_get_contents($value);
                        if ($data === false || strlen($data) === 0) {
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
                        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_remoteDownloadTimeout);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_remoteDownloadTimeout / 2);
                        // find files that have been redirected
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        // process compressed images
                        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate,sdch');
                        $result = curl_exec($ch);
                        if ($result !== false) {
                            $value = $result;
                        }
                    } else {
                        $contextOptions = [
                            'http' => [
                                'method'           => 'GET',
                                'protocol_version' => '1.1',
                                'timeout'          => $this->_remoteDownloadTimeout
                            ],
                            'ssl' => [
                                'verify_peer'  => false
                            ]
                        ];
                        list($result) = $this->getNoCurlInitialization($value, $contextOptions);
                        if ($result !== false) {
                            $value = $result;
                        }
                    }
                }
            }

            $request .= "\r\n\r\n" . $value . "\r\n";
        }

        return $request;
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
        $possible_files = [
            // Tweets
            'statuses/update_with_media' => 'media[]',
            'media/upload' => 'media',
            // Accounts
            'account/update_profile_background_image' => 'image',
            'account/update_profile_image' => 'image',
            'account/update_profile_banner' => 'banner'
        ];
        // method might have files?
        if (! in_array($method, array_keys($possible_files))) {
            return;
        }

        $possible_files = explode(' ', $possible_files[$method]);

        $multipart_border = '--------------------' . $this->_nonce();
        $multipart_request =
            $this->_getMultipartRequestFromParams($possible_files, $multipart_border, $params)
            . '--' . $multipart_border . '--';

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
        $medias = [
            'media/upload'
        ];
        return in_array($method, $medias);
    }

    /**
     * Detects if API call should use streaming endpoint, and if yes, which one
     *
     * @param string $method The API method to call
     *
     * @return string|false Variant of streaming API to be used
     */
    protected function _detectStreaming($method) {
        $streamings = [
            'public' => [
                'statuses/sample',
                'statuses/filter',
                'statuses/firehose'
            ],
            'user' => ['user'],
            'site' => ['site']
        ];
        foreach ($streamings as $key => $values) {
            if (in_array($method, $values)) {
                return $key;
            }
        }

        return false;
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
        } elseif ($variant = $this->_detectStreaming($method)) {
            $url = self::$_endpoints_streaming[$variant] . $method . '.json';
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
     * @return string The API reply, encoded in the set return_format
     */

    protected function _callApi($httpmethod, $method, $params = [], $multipart = false, $app_only_auth = false)
    {
        if (! $app_only_auth
            && $this->_oauth_token === null
            && substr($method, 0, 5) !== 'oauth'
        ) {
                throw new \Exception('To call this API, the OAuth access token must be set.');
        }
        // use separate API access for streaming API
        if ($this->_detectStreaming($method) !== false) {
            return $this->_callApiStreaming($httpmethod, $method, $params, $app_only_auth);
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
     * @return string The API reply, encoded in the set return_format
     */

    protected function _callApiCurl(
        $httpmethod, $method, $params = [], $multipart = false, $app_only_auth = false
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
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_timeout);
        }

        if (isset($this->_connectionTimeout)) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_connectionTimeout);
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
     * @return string The API reply, encoded in the set return_format
     */

    protected function _callApiNoCurl(
        $httpmethod, $method, $params = [], $multipart = false, $app_only_auth = false
    )
    {
        list ($authorization, $url, $params, $request_headers)
            = $this->_callApiPreparations(
                $httpmethod, $method, $params, $multipart, $app_only_auth
            );

        $hostname = parse_url($url, PHP_URL_HOST);
        if ($hostname === false) {
            throw new \Exception('Incorrect API endpoint host.');
        }

        $request_headers[] = 'Authorization: ' . $authorization;
        $request_headers[] = 'Accept: */*';
        $request_headers[] = 'Connection: Close';
        if ($httpmethod !== 'GET' && ! $multipart) {
            $request_headers[]  = 'Content-Type: application/x-www-form-urlencoded';
        }

        $contextOptions = [
            'http' => [
                'method'           => $httpmethod,
                'protocol_version' => '1.1',
                'header'           => implode("\r\n", $request_headers),
                'timeout'          => $this->_timeout / 1000,
                'content'          => $httpmethod === 'POST' ? $params : null,
                'ignore_errors'    => true
            ]
        ];

        list($reply, $headers) = $this->getNoCurlInitialization($url, $contextOptions, $hostname);
        $result  = '';
        foreach ($headers as $header) {
            $result .= $header . "\r\n";
        }
        $result .= "\r\n" . $reply;

        // find HTTP status
        $httpstatus = '500';
        $match      = [];
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
     * Do preparations to make the API GET call
     *
     * @param string  $httpmethod      The HTTP method to use for making the request
     * @param string  $url             The URL to call
     * @param array   $params          The parameters to send along
     * @param bool    $app_only_auth   Whether to use app-only bearer authentication
     *
     * @return string[] (string authorization, string url)
     */
    protected function _callApiPreparationsGet(
        $httpmethod, $url, $params, $app_only_auth
    ) {
        return [
            $app_only_auth                ? null : $this->_sign($httpmethod, $url, $params),
            json_encode($params) === '[]' ? $url : $url . '?' . http_build_query($params)
        ];
    }

    /**
     * Do preparations to make the API POST call
     *
     * @param string  $httpmethod      The HTTP method to use for making the request
     * @param string  $url             The URL to call
     * @param string  $method          The API method to call
     * @param array   $params          The parameters to send along
     * @param bool    $multipart       Whether to use multipart/form-data
     * @param bool    $app_only_auth   Whether to use app-only bearer authentication
     *
     * @return array (string authorization, array params, array request_headers)
     */
    protected function _callApiPreparationsPost(
        $httpmethod, $url, $method, $params, $multipart, $app_only_auth
    ) {
        $authorization   = null;
        $request_headers = [];
        if ($multipart) {
            if (! $app_only_auth) {
                $authorization = $this->_sign($httpmethod, $url, []);
            }
            $params = $this->_buildMultipart($method, $params);
        } else {
            if (! $app_only_auth) {
                $authorization = $this->_sign($httpmethod, $url, $params);
            }
            $params = http_build_query($params);
        }
        if ($multipart) {
            $first_newline      = strpos($params, "\r\n");
            $multipart_boundary = substr($params, 2, $first_newline - 2);
            $request_headers[]  = 'Content-Type: multipart/form-data; boundary='
                . $multipart_boundary;
        }
        return [$authorization, $params, $request_headers];
    }

    /**
     * Get Bearer authorization string
     *
     * @return string authorization
     */
    protected function _getBearerAuthorization()
    {
        if (self::$_oauth_consumer_key === null
            && self::$_oauth_bearer_token === null
        ) {
            throw new \Exception('To make an app-only auth API request, consumer key or bearer token must be set.');
        }
        // automatically fetch bearer token, if necessary
        if (self::$_oauth_bearer_token === null) {
            $this->oauth2_token();
        }
        return 'Bearer ' . self::$_oauth_bearer_token;
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
        $url             = $this->_getEndpoint($method);
        $request_headers = [];
        if ($httpmethod === 'GET') {
            // GET
            list ($authorization, $url) =
                $this->_callApiPreparationsGet($httpmethod, $url, $params, $app_only_auth);
        } else {
            // POST
            list ($authorization, $params, $request_headers) =
                $this->_callApiPreparationsPost($httpmethod, $url, $method, $params, $multipart, $app_only_auth);
        }
        if ($app_only_auth) {
            $authorization = $this->_getBearerAuthorization();
        }

        return [
            $authorization, $url, $params, $request_headers
        ];
    }

    /**
     * Calls the streaming API
     *
     * @param string          $httpmethod      The HTTP method to use for making the request
     * @param string          $method          The API method to call
     * @param array  optional $params          The parameters to send along
     * @param bool   optional $app_only_auth   Whether to use app-only bearer authentication
     *
     * @return void
     */

    protected function _callApiStreaming(
        $httpmethod, $method, $params = [], $app_only_auth = false
    )
    {
        if ($this->_streaming_callback === null) {
            throw new \Exception('Set streaming callback before consuming a stream.');
        }

        $params['delimited'] = 'length';

        list ($authorization, $url, $params, $request_headers)
            = $this->_callApiPreparations(
                $httpmethod, $method, $params, false, $app_only_auth
            );

        $hostname = parse_url($url, PHP_URL_HOST);
        $path     = parse_url($url, PHP_URL_PATH);
        $query    = parse_url($url, PHP_URL_QUERY);
        if ($hostname === false) {
            throw new \Exception('Incorrect API endpoint host.');
        }

        $request_headers[] = 'Authorization: ' . $authorization;
        $request_headers[] = 'Accept: */*';
        if ($httpmethod !== 'GET') {
            $request_headers[]  = 'Content-Type: application/x-www-form-urlencoded';
            $request_headers[]  = 'Content-Length: ' . strlen($params);
        }

        $errno   = 0;
        $errstr  = '';
        $ch = stream_socket_client(
            'ssl://' . $hostname . ':443',
            $errno, $errstr,
            $this->_connectionTimeout,
            STREAM_CLIENT_CONNECT
        );

        // send request
        $request = $httpmethod . ' '
            . $path . ($query ? '?' . $query : '') . " HTTP/1.1\r\n"
            . 'Host: ' . $hostname . "\r\n"
            . implode("\r\n", $request_headers)
            . "\r\n\r\n";
        if ($httpmethod !== 'GET') {
            $request .= $params;
        }
        fputs($ch, $request);
        stream_set_blocking($ch, 0);
        stream_set_timeout($ch, 0);

        // collect headers
        do {
            $result  = stream_get_line($ch, 1048576, "\r\n\r\n");
        } while(!$result);
        $headers = explode("\r\n", $result);

        // find HTTP status
        $httpstatus = '500';
        $match      = [];
        if (!empty($headers[0]) && preg_match('/HTTP\/\d\.\d (\d{3})/', $headers[0], $match)) {
            $httpstatus = $match[1];
        }

        list($headers,) = $this->_parseApiHeaders($result);
        $rate           = $this->_getRateLimitInfo($headers);

        if ($httpstatus !== '200') {
            $reply = [
                'httpstatus' => $httpstatus,
                'rate'       => $rate
            ];
            switch ($this->_return_format) {
                case CODEBIRD_RETURNFORMAT_ARRAY:
                    return $reply;
                case CODEBIRD_RETURNFORMAT_OBJECT:
                    return (object) $reply;
                case CODEBIRD_RETURNFORMAT_JSON:
                    return json_encode($reply);
            }
        }

        $signal_function = function_exists('pcntl_signal_dispatch');
        $data            = '';
        $last_message    = time();
        $message_length  = 0;

        while (!feof($ch)) {
            // call signal handlers, if any
            if ($signal_function) {
                pcntl_signal_dispatch();
            }
            $cha = [$ch];
            $write = $except = null;
            if (false === ($num_changed_streams = stream_select($cha, $write, $except, 0, 200000))) {
                break;
            } elseif ($num_changed_streams === 0) {
                if (time() - $last_message >= 1) {
                    // deliver empty message, allow callback to cancel stream
                    $cancel_stream = $this->_deliverStreamingMessage(null);
                    if ($cancel_stream) {
                        break;
                    }
                    $last_message = time();
                }
                continue;
            }
            $chunk_length = fgets($ch, 10);
            if ($chunk_length === '' || !$chunk_length = hexdec($chunk_length)) {
                continue;
            }

            $chunk = '';
            do {
                $chunk .= fread($ch, $chunk_length);
                $chunk_length -= strlen($chunk);
            } while($chunk_length > 0);

            if(0 === $message_length) {
                $message_length = (int) strstr($chunk, "\r\n", true);
                if ($message_length) {
                    $chunk = substr($chunk, strpos($chunk, "\r\n") + 2);
                } else {
                    continue;
                }

                $data = $chunk;
            } else {
                $data .= $chunk;
            }

            if (strlen($data) < $message_length) {
                continue;
            }

            $reply = $this->_parseApiReply($data);
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

            $cancel_stream = $this->_deliverStreamingMessage($reply);
            if ($cancel_stream) {
                break;
            }

            $data           = '';
            $message_length = 0;
            $last_message   = time();
        }

        return;
    }

    /**
     * Calls streaming callback with received message
     *
     * @param string|array|object message
     *
     * @return bool Whether to cancel streaming
     */
    protected function _deliverStreamingMessage($message)
    {
        return call_user_func($this->_streaming_callback, $message);
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
        $headers = [];
        $reply = explode("\r\n\r\n", $reply, 4);

        // check if using proxy
        $proxy_tester = strtolower(substr($reply[0], 0, 35));
        if ($proxy_tester === 'http/1.0 200 connection established'
            || $proxy_tester === 'http/1.1 200 connection established'
        ) {
            array_shift($reply);
        } elseif (count($reply) > 2) {
            $headers = array_shift($reply);
            $reply = [
                $headers,
                implode("\r\n", $reply)
            ];
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

        return [$headers, $reply];
    }

    /**
     * Parses the API reply to encode it in the set return_format
     *
     * @param string $reply The actual HTTP body, JSON-encoded or URL-encoded
     *
     * @return array|string|object The parsed reply
     */
    protected function _parseApiReply($reply)
    {
        $need_array = $this->_return_format === CODEBIRD_RETURNFORMAT_ARRAY;
        if ($reply === '[]') {
            switch ($this->_return_format) {
                case CODEBIRD_RETURNFORMAT_ARRAY:
                    return [];
                case CODEBIRD_RETURNFORMAT_JSON:
                    return '{}';
                case CODEBIRD_RETURNFORMAT_OBJECT:
                    return new \stdClass;
            }
        }
        if (! $parsed = json_decode($reply, $need_array, 512, JSON_BIGINT_AS_STRING)) {
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
