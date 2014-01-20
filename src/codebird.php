<?php

namespace Codebird;

/**
 * A Twitter library in PHP.
 *
 * @package codebird
 * @version 2.4.1
 * @author Jublo IT Solutions &lt;support@jublo.net&gt;
 * @copyright 2010-2014 Jublo IT Solutions &lt;support@jublo.net&gt;
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
    'CURLE_SSL_ISSUER_ERROR' => 83
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
     * The API endpoint to use for OAuth requests
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
    protected $_version = '2.4.1';

    /**
     * Returns singleton class instance
     * Always use this method unless you're working with multiple authenticated users at once
     *
     * @return Codebird The instance
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
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
        $apiparams = array();
        if (count($params) > 0) {
            if (is_array($params[0])) {
                $apiparams = $params[0];
            } else {
                parse_str($params[0], $apiparams);
                // remove auto-added slashes if on magic quotes steroids
                if (get_magic_quotes_gpc()) {
                    foreach($apiparams as $key => $value) {
                        if (is_array($value)) {
                            $apiparams[$key] = array_map('stripslashes', $value);
                        } else {
                            $apiparams[$key] = stripslashes($value);
                        }
                    }
                }
            }
        }

        // stringify null and boolean parameters
        foreach ($apiparams as $key => $value) {
            if (! is_scalar($value)) {
                continue;
            }
            if (is_null($value)) {
                $apiparams[$key] = 'null';
            } elseif (is_bool($value)) {
                $apiparams[$key] = $value ? 'true' : 'false';
            }
        }

        $app_only_auth = false;
        if (count($params) > 1) {
            $app_only_auth = !! $params[1];
        }

        // map function name to API method
        $method = '';

        // replace _ by /
        $path = explode('_', $fn);
        for ($i = 0; $i < count($path); $i++) {
            if ($i > 0) {
                $method .= '/';
            }
            $method .= $path[$i];
        }
        // undo replacement for URL parameters
        $url_parameters_with_underscore = array('screen_name');
        foreach ($url_parameters_with_underscore as $param) {
            $param = strtoupper($param);
            $replacement_was = str_replace('_', '/', $param);
            $method = str_replace($replacement_was, $param, $method);
        }

        // replace AA by URL parameters
        $method_template = $method;
        $match   = array();
        if (preg_match('/[A-Z_]{2,}/', $method, $match)) {
            foreach ($match as $param) {
                $param_l = strtolower($param);
                $method_template = str_replace($param, ':' . $param_l, $method_template);
                if (!isset($apiparams[$param_l])) {
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

        $httpmethod = $this->_detectMethod($method_template, $apiparams);
        $multipart  = $this->_detectMultipart($method_template);

        return $this->_callApi(
            $httpmethod,
            $method,
            $method_template,
            $apiparams,
            $multipart,
            $app_only_auth
        );
    }

    /**
     * Uncommon API methods
     */

    /**
     * Gets the OAuth authenticate URL for the current request token
     *
     * @return string The OAuth authenticate URL
     */
    public function oauth_authenticate($force_login = NULL, $screen_name = NULL)
    {
        if ($this->_oauth_token == null) {
            throw new \Exception('To get the authenticate URL, the OAuth token must be set.');
        }
        $url = self::$_endpoint_oauth . 'oauth/authenticate?oauth_token=' . $this->_url($this->_oauth_token);
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
     *
     * @return string The OAuth authorize URL
     */
    public function oauth_authorize($force_login = NULL, $screen_name = NULL)
    {
        if ($this->_oauth_token == null) {
            throw new \Exception('To get the authorize URL, the OAuth token must be set.');
        }
        $url = self::$_endpoint_oauth . 'oauth/authorize?oauth_token=' . $this->_url($this->_oauth_token);
        if ($force_login) {
            $url .= "&force_login=1";
        }
        if ($screen_name) {
            $url .= "&screen_name=" . $screen_name;
        }
        return $url;
    }

    /**
     * Gets the OAuth bearer token
     *
     * @return string The OAuth bearer token
     */

    public function oauth2_token()
    {
        if (! function_exists('curl_init')) {
            throw new \Exception('To make API requests, the PHP curl extension must be available.');
        }
        if (self::$_oauth_consumer_key == null) {
            throw new \Exception('To obtain a bearer token, the consumer key must be set.');
        }
        $ch  = false;
        $post_fields = array(
            'grant_type' => 'client_credentials'
        );
        $url = self::$_endpoint_oauth . 'oauth2/token';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');

        curl_setopt($ch, CURLOPT_USERPWD, self::$_oauth_consumer_key . ':' . self::$_oauth_consumer_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Expect:'
        ));
        $reply = curl_exec($ch);

        // certificate validation results
        $validation_result = curl_errno($ch);
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
            throw new \Exception('Error ' . $validation_result . ' while validating the Twitter API certificate.');
        }

        $httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $reply = $this->_parseApiReply('oauth2/token', $reply);
        switch ($this->_return_format) {
            case CODEBIRD_RETURNFORMAT_ARRAY:
                $reply['httpstatus'] = $httpstatus;
                if ($httpstatus == 200) {
                    self::setBearerToken($reply['access_token']);
                }
                break;
            case CODEBIRD_RETURNFORMAT_JSON:
                if ($httpstatus == 200) {
                    $parsed = json_decode($reply);
                    self::setBearerToken($parsed->access_token);
                }
                break;
            case CODEBIRD_RETURNFORMAT_OBJECT:
                $reply->httpstatus = $httpstatus;
                if ($httpstatus == 200) {
                    self::setBearerToken($reply->access_token);
                }
                break;
        }
        return $reply;
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
    private function _url($data)
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
    private function _sha1($data)
    {
        if (self::$_oauth_consumer_secret == null) {
            throw new \Exception('To generate a hash, the consumer secret must be set.');
        }
        if (!function_exists('hash_hmac')) {
            throw new \Exception('To generate a hash, the PHP hash extension must be available.');
        }
        return base64_encode(hash_hmac('sha1', $data, self::$_oauth_consumer_secret . '&'
            . ($this->_oauth_token_secret != null ? $this->_oauth_token_secret : ''), true));
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
     * @param string          $httpmethod Usually either 'GET' or 'POST' or 'DELETE'
     * @param string          $method     The API method to call
     * @param array  optional $params     The API call parameters, associative
     *
     * @return string Authorization HTTP header
     */
    protected function _sign($httpmethod, $method, $params = array())
    {
        if (self::$_oauth_consumer_key == null) {
            throw new \Exception('To generate a signature, the consumer key must be set.');
        }
        $sign_params      = array(
            'consumer_key' => self::$_oauth_consumer_key,
            'version' => '1.0',
            'timestamp' => time(),
            'nonce' => $this->_nonce(),
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

        $params = array_merge($oauth_params, array(
            'oauth_signature' => $signature
        ));
        ksort($params);
        $authorization = 'Authorization: OAuth ';
        foreach ($params as $key => $value) {
            $authorization .= $key . '="' . $this->_url($value) . '", ';
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
        switch($method) {
            case 'account/settings':
                $method = count($params) > 0 ? $method . '__post' : $method;
                break;
        }

        $httpmethods         = array();
        $httpmethods['GET']  = array(
            // Timelines
            'statuses/mentions_timeline',
            'statuses/user_timeline',
            'statuses/home_timeline',
            'statuses/retweets_of_me',

            // Tweets
            'statuses/retweets/:id',
            'statuses/show/:id',
            'statuses/oembed',

            // Search
            'search/tweets',

            // Direct Messages
            'direct_messages',
            'direct_messages/sent',
            'direct_messages/show',

            // Friends & Followers
            'friendships/no_retweets/ids',
            'friends/ids',
            'followers/ids',
            'friendships/lookup',
            'friendships/incoming',
            'friendships/outgoing',
            'friendships/show',
            'friends/list',
            'followers/list',

            // Users
            'account/settings',
            'account/verify_credentials',
            'blocks/list',
            'blocks/ids',
            'users/lookup',
            'users/show',
            'users/search',
            'users/contributees',
            'users/contributors',
            'users/profile_banner',

            // Suggested Users
            'users/suggestions/:slug',
            'users/suggestions',
            'users/suggestions/:slug/members',

            // Favorites
            'favorites/list',

            // Lists
            'lists/list',
            'lists/statuses',
            'lists/memberships',
            'lists/subscribers',
            'lists/subscribers/show',
            'lists/members/show',
            'lists/members',
            'lists/show',
            'lists/subscriptions',

            // Saved searches
            'saved_searches/list',
            'saved_searches/show/:id',

            // Places & Geo
            'geo/id/:place_id',
            'geo/reverse_geocode',
            'geo/search',
            'geo/similar_places',

            // Trends
            'trends/place',
            'trends/available',
            'trends/closest',

            // OAuth
            'oauth/authenticate',
            'oauth/authorize',

            // Help
            'help/configuration',
            'help/languages',
            'help/privacy',
            'help/tos',
            'application/rate_limit_status'
        );
        $httpmethods['POST'] = array(
            // Tweets
            'statuses/destroy/:id',
            'statuses/update',
            'statuses/retweet/:id',
            'statuses/update_with_media',

            // Direct Messages
            'direct_messages/destroy',
            'direct_messages/new',

            // Friends & Followers
            'friendships/create',
            'friendships/destroy',
            'friendships/update',

            // Users
            'account/settings__post',
            'account/update_delivery_device',
            'account/update_profile',
            'account/update_profile_background_image',
            'account/update_profile_colors',
            'account/update_profile_image',
            'blocks/create',
            'blocks/destroy',
            'account/update_profile_banner',
            'account/remove_profile_banner',

            // Favorites
            'favorites/destroy',
            'favorites/create',

            // Lists
            'lists/members/destroy',
            'lists/subscribers/create',
            'lists/subscribers/destroy',
            'lists/members/create_all',
            'lists/members/create',
            'lists/destroy',
            'lists/update',
            'lists/create',
            'lists/members/destroy_all',

            // Saved Searches
            'saved_searches/create',
            'saved_searches/destroy/:id',

            // Places & Geo
            'geo/place',

            // Spam Reporting
            'users/report_spam',

            // OAuth
            'oauth/access_token',
            'oauth/request_token',
            'oauth2/token',
            'oauth2/invalidate_token'
        );
        foreach ($httpmethods as $httpmethod => $methods) {
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
     * @return void
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
                continue;
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
                    if (// is it a supported image format?
                        in_array($data[2], $this->_supported_media_files)
                    ) {
                        // try to read the file
                        ob_start();
                        readfile($value);
                        $data = ob_get_contents();
                        ob_end_clean();
                        if (strlen($data) == 0) {
                            continue;
                        }
                        $value = $data;
                    }
                }

                /*
                $multipart_request .=
                    "\r\nContent-Transfer-Encoding: base64";
                $value = base64_encode($value);
                */
            }

            $multipart_request .=
                "\r\n\r\n" . $value . "\r\n";
        }
        $multipart_request .= '--' . $multipart_border . '--';

        return $multipart_request;
    }


    /**
     * Builds the complete API endpoint url
     *
     * @param string $method           The API method to call
     * @param string $method_template  The API method template to call
     *
     * @return string The URL to send the request to
     */
    protected function _getEndpoint($method, $method_template)
    {
        if (substr($method, 0, 5) == 'oauth') {
            $url = self::$_endpoint_oauth . $method;
        } else {
            $url = self::$_endpoint . $method . '.json';
        }
        return $url;
    }

    /**
     * Calls the API using cURL
     *
     * @param string          $httpmethod      The HTTP method to use for making the request
     * @param string          $method          The API method to call
     * @param string          $method_template The templated API method to call
     * @param array  optional $params          The parameters to send along
     * @param bool   optional $multipart       Whether to use multipart/form-data
     * @param bool   optional $app_only_auth   Whether to use app-only bearer authentication
     *
     * @return mixed The API reply, encoded in the set return_format
     */

    protected function _callApi($httpmethod, $method, $method_template, $params = array(), $multipart = false, $app_only_auth = false)
    {
        if (! function_exists('curl_init')) {
            throw new \Exception('To make API requests, the PHP curl extension must be available.');
        }
        $url = $this->_getEndpoint($method, $method_template);
        $ch  = false;
        if ($httpmethod == 'GET') {
            $url_with_params = $url;
            if (count($params) > 0) {
                $url_with_params .= '?' . http_build_query($params);
            }
            $authorization = $this->_sign($httpmethod, $url, $params);
            $ch = curl_init($url_with_params);
        } else {
            if ($multipart) {
                $authorization = $this->_sign($httpmethod, $url, array());
                $params        = $this->_buildMultipart($method_template, $params);
            } else {
                $authorization = $this->_sign($httpmethod, $url, $params);
                $params        = http_build_query($params);
            }
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        if ($app_only_auth) {
            if (self::$_oauth_consumer_key == null) {
                throw new \Exception('To make an app-only auth API request, the consumer key must be set.');
            }
            // automatically fetch bearer token, if necessary
            if (self::$_oauth_bearer_token == null) {
                $this->oauth2_token();
            }
            $authorization = 'Authorization: Bearer ' . self::$_oauth_bearer_token;
        }
        $request_headers = array();
        if (isset($authorization)) {
            $request_headers[] = $authorization;
            $request_headers[] = 'Expect:';
        }
        if ($multipart) {
            $first_newline      = strpos($params, "\r\n");
            $multipart_boundary = substr($params, 2, $first_newline - 2);
            $request_headers[]  = 'Content-Length: ' . strlen($params);
            $request_headers[]  = 'Content-Type: multipart/form-data; boundary='
                . $multipart_boundary;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');

        $reply = curl_exec($ch);

        // certificate validation results
        $validation_result = curl_errno($ch);
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
            throw new \Exception('Error ' . $validation_result . ' while validating the Twitter API certificate.');
        }

        $httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $reply = $this->_parseApiReply($method_template, $reply);
        if ($this->_return_format == CODEBIRD_RETURNFORMAT_OBJECT) {
            $reply->httpstatus = $httpstatus;
        } elseif ($this->_return_format == CODEBIRD_RETURNFORMAT_ARRAY) {
            $reply['httpstatus'] = $httpstatus;
        }
        return $reply;
    }

    /**
     * Parses the API reply to encode it in the set return_format
     *
     * @param string $method The method that has been called
     * @param string $reply  The actual reply, JSON-encoded or URL-encoded
     *
     * @return array|object The parsed reply
     */
    protected function _parseApiReply($method, $reply)
    {
        // split headers and body
        $headers = array();
        $reply = explode("\r\n\r\n", $reply, 4);

        // check if using proxy
        if (substr($reply[0], 0, 35) === 'HTTP/1.1 200 Connection Established') {
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

        $need_array = $this->_return_format == CODEBIRD_RETURNFORMAT_ARRAY;
        if ($reply == '[]') {
            switch ($this->_return_format) {
                case CODEBIRD_RETURNFORMAT_ARRAY:
                    return array();
                case CODEBIRD_RETURNFORMAT_JSON:
                    return '{}';
                case CODEBIRD_RETURNFORMAT_OBJECT:
                    return new \stdClass;
            }
        }
        $parsed = array();
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
                            list($key, $value) = explode('=', $element);
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

?>
