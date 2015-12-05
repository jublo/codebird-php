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
  protected static $_consumer_key = null;

  /**
   * The corresponding consumer secret
   */
  protected static $_consumer_secret = null;

  /**
   * The app-only bearer token. Used to authorize app-only requests
   */
  protected static $_bearer_token = null;

  /**
   * The API endpoints to use
   */
  protected static $_endpoints = [
    'ads'          => [
      'production' => 'https://ads-api.twitter.com/0/',
      'sandbox'    => 'https://ads-api-sandbox.twitter.com/0/'
    ],
    'media'        => 'https://upload.twitter.com/1.1/',
    'oauth'        => 'https://api.twitter.com/',
    'rest'         => 'https://api.twitter.com/1.1/',
    'streaming'    => [
      'public'     => 'https://stream.twitter.com/1.1/',
      'user'       => 'https://userstream.twitter.com/1.1/',
      'site'       => 'https://sitestream.twitter.com/1.1/'
    ],
    'ton'          => 'https://ton.twitter.com/1.1/'
  ];

  /**
   * Supported API methods
   */
  protected static $_api_methods = [
    'GET' => [
      'account/settings',
      'account/verify_credentials',
      'ads/accounts',
      'ads/accounts/:account_id',
      'ads/accounts/:account_id/app_event_provider_configurations',
      'ads/accounts/:account_id/app_event_provider_configurations/:id',
      'ads/accounts/:account_id/app_event_tags',
      'ads/accounts/:account_id/app_event_tags/:id',
      'ads/accounts/:account_id/app_lists',
      'ads/accounts/:account_id/authenticated_user_access',
      'ads/accounts/:account_id/campaigns',
      'ads/accounts/:account_id/campaigns/:campaign_id',
      'ads/accounts/:account_id/cards/app_download',
      'ads/accounts/:account_id/cards/app_download/:card_id',
      'ads/accounts/:account_id/cards/image_app_download',
      'ads/accounts/:account_id/cards/image_app_download/:card_id',
      'ads/accounts/:account_id/cards/image_conversation',
      'ads/accounts/:account_id/cards/image_conversation/:card_id',
      'ads/accounts/:account_id/cards/lead_gen',
      'ads/accounts/:account_id/cards/lead_gen/:card_id',
      'ads/accounts/:account_id/cards/video_app_download',
      'ads/accounts/:account_id/cards/video_app_download/:id',
      'ads/accounts/:account_id/cards/video_conversation',
      'ads/accounts/:account_id/cards/video_conversation/:card_id',
      'ads/accounts/:account_id/cards/website',
      'ads/accounts/:account_id/cards/website/:card_id',
      'ads/accounts/:account_id/features',
      'ads/accounts/:account_id/funding_instruments',
      'ads/accounts/:account_id/funding_instruments/:id',
      'ads/accounts/:account_id/line_items',
      'ads/accounts/:account_id/line_items/:line_item_id',
      'ads/accounts/:account_id/promotable_users',
      'ads/accounts/:account_id/promoted_accounts',
      'ads/accounts/:account_id/promoted_tweets',
      'ads/accounts/:account_id/reach_estimate',
      'ads/accounts/:account_id/scoped_timeline',
      'ads/accounts/:account_id/tailored_audience_changes',
      'ads/accounts/:account_id/tailored_audience_changes/:id',
      'ads/accounts/:account_id/tailored_audiences',
      'ads/accounts/:account_id/tailored_audiences/:id',
      'ads/accounts/:account_id/targeting_criteria',
      'ads/accounts/:account_id/targeting_criteria/:id',
      'ads/accounts/:account_id/targeting_suggestions',
      'ads/accounts/:account_id/tweet/preview',
      'ads/accounts/:account_id/tweet/preview/:tweet_id',
      'ads/accounts/:account_id/videos',
      'ads/accounts/:account_id/videos/:id',
      'ads/accounts/:account_id/web_event_tags',
      'ads/accounts/:account_id/web_event_tags/:web_event_tag_id',
      'ads/bidding_rules',
      'ads/iab_categories',
      'ads/insights/accounts/:account_id',
      'ads/insights/accounts/:account_id/available_audiences',
      'ads/line_items/placements',
      'ads/sandbox/accounts',
      'ads/sandbox/accounts/:account_id',
      'ads/sandbox/accounts/:account_id/app_event_provider_configurations',
      'ads/sandbox/accounts/:account_id/app_event_provider_configurations/:id',
      'ads/sandbox/accounts/:account_id/app_event_tags',
      'ads/sandbox/accounts/:account_id/app_event_tags/:id',
      'ads/sandbox/accounts/:account_id/app_lists',
      'ads/sandbox/accounts/:account_id/authenticated_user_access',
      'ads/sandbox/accounts/:account_id/campaigns',
      'ads/sandbox/accounts/:account_id/campaigns/:campaign_id',
      'ads/sandbox/accounts/:account_id/cards/app_download',
      'ads/sandbox/accounts/:account_id/cards/app_download/:card_id',
      'ads/sandbox/accounts/:account_id/cards/image_app_download',
      'ads/sandbox/accounts/:account_id/cards/image_app_download/:card_id',
      'ads/sandbox/accounts/:account_id/cards/image_conversation',
      'ads/sandbox/accounts/:account_id/cards/image_conversation/:card_id',
      'ads/sandbox/accounts/:account_id/cards/lead_gen',
      'ads/sandbox/accounts/:account_id/cards/lead_gen/:card_id',
      'ads/sandbox/accounts/:account_id/cards/video_app_download',
      'ads/sandbox/accounts/:account_id/cards/video_app_download/:id',
      'ads/sandbox/accounts/:account_id/cards/video_conversation',
      'ads/sandbox/accounts/:account_id/cards/video_conversation/:card_id',
      'ads/sandbox/accounts/:account_id/cards/website',
      'ads/sandbox/accounts/:account_id/cards/website/:card_id',
      'ads/sandbox/accounts/:account_id/features',
      'ads/sandbox/accounts/:account_id/funding_instruments',
      'ads/sandbox/accounts/:account_id/funding_instruments/:id',
      'ads/sandbox/accounts/:account_id/line_items',
      'ads/sandbox/accounts/:account_id/line_items/:line_item_id',
      'ads/sandbox/accounts/:account_id/promotable_users',
      'ads/sandbox/accounts/:account_id/promoted_accounts',
      'ads/sandbox/accounts/:account_id/promoted_tweets',
      'ads/sandbox/accounts/:account_id/reach_estimate',
      'ads/sandbox/accounts/:account_id/scoped_timeline',
      'ads/sandbox/accounts/:account_id/tailored_audience_changes',
      'ads/sandbox/accounts/:account_id/tailored_audience_changes/:id',
      'ads/sandbox/accounts/:account_id/tailored_audiences',
      'ads/sandbox/accounts/:account_id/tailored_audiences/:id',
      'ads/sandbox/accounts/:account_id/targeting_criteria',
      'ads/sandbox/accounts/:account_id/targeting_criteria/:id',
      'ads/sandbox/accounts/:account_id/targeting_suggestions',
      'ads/sandbox/accounts/:account_id/tweet/preview',
      'ads/sandbox/accounts/:account_id/tweet/preview/:tweet_id',
      'ads/sandbox/accounts/:account_id/videos',
      'ads/sandbox/accounts/:account_id/videos/:id',
      'ads/sandbox/accounts/:account_id/web_event_tags',
      'ads/sandbox/accounts/:account_id/web_event_tags/:web_event_tag_id',
      'ads/sandbox/bidding_rules',
      'ads/sandbox/iab_categories',
      'ads/sandbox/insights/accounts/:account_id',
      'ads/sandbox/insights/accounts/:account_id/available_audiences',
      'ads/sandbox/line_items/placements',
      'ads/sandbox/stats/accounts/:account_id',
      'ads/sandbox/stats/accounts/:account_id/campaigns',
      'ads/sandbox/stats/accounts/:account_id/campaigns/:id',
      'ads/sandbox/stats/accounts/:account_id/funding_instruments',
      'ads/sandbox/stats/accounts/:account_id/funding_instruments/:id',
      'ads/sandbox/stats/accounts/:account_id/line_items',
      'ads/sandbox/stats/accounts/:account_id/line_items/:id',
      'ads/sandbox/stats/accounts/:account_id/promoted_accounts',
      'ads/sandbox/stats/accounts/:account_id/promoted_accounts/:id',
      'ads/sandbox/stats/accounts/:account_id/promoted_tweets',
      'ads/sandbox/stats/accounts/:account_id/promoted_tweets/:id',
      'ads/sandbox/stats/accounts/:account_id/reach/campaigns',
      'ads/sandbox/targeting_criteria/app_store_categories',
      'ads/sandbox/targeting_criteria/behavior_taxonomies',
      'ads/sandbox/targeting_criteria/behaviors',
      'ads/sandbox/targeting_criteria/devices',
      'ads/sandbox/targeting_criteria/events',
      'ads/sandbox/targeting_criteria/interests',
      'ads/sandbox/targeting_criteria/languages',
      'ads/sandbox/targeting_criteria/locations',
      'ads/sandbox/targeting_criteria/network_operators',
      'ads/sandbox/targeting_criteria/platform_versions',
      'ads/sandbox/targeting_criteria/platforms',
      'ads/sandbox/targeting_criteria/tv_channels',
      'ads/sandbox/targeting_criteria/tv_genres',
      'ads/sandbox/targeting_criteria/tv_markets',
      'ads/sandbox/targeting_criteria/tv_shows',
      'ads/stats/accounts/:account_id',
      'ads/stats/accounts/:account_id/campaigns',
      'ads/stats/accounts/:account_id/campaigns/:id',
      'ads/stats/accounts/:account_id/funding_instruments',
      'ads/stats/accounts/:account_id/funding_instruments/:id',
      'ads/stats/accounts/:account_id/line_items',
      'ads/stats/accounts/:account_id/line_items/:id',
      'ads/stats/accounts/:account_id/promoted_accounts',
      'ads/stats/accounts/:account_id/promoted_accounts/:id',
      'ads/stats/accounts/:account_id/promoted_tweets',
      'ads/stats/accounts/:account_id/promoted_tweets/:id',
      'ads/stats/accounts/:account_id/reach/campaigns',
      'ads/targeting_criteria/app_store_categories',
      'ads/targeting_criteria/behavior_taxonomies',
      'ads/targeting_criteria/behaviors',
      'ads/targeting_criteria/devices',
      'ads/targeting_criteria/events',
      'ads/targeting_criteria/interests',
      'ads/targeting_criteria/languages',
      'ads/targeting_criteria/locations',
      'ads/targeting_criteria/network_operators',
      'ads/targeting_criteria/platform_versions',
      'ads/targeting_criteria/platforms',
      'ads/targeting_criteria/tv_channels',
      'ads/targeting_criteria/tv_genres',
      'ads/targeting_criteria/tv_markets',
      'ads/targeting_criteria/tv_shows',
      'application/rate_limit_status',
      'blocks/ids',
      'blocks/list',
      'collections/entries',
      'collections/list',
      'collections/show',
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
      'account/settings',
      'account/update_delivery_device',
      'account/update_profile',
      'account/update_profile_background_image',
      'account/update_profile_banner',
      'account/update_profile_colors',
      'account/update_profile_image',
      'ads/accounts/:account_id/app_lists',
      'ads/accounts/:account_id/campaigns',
      'ads/accounts/:account_id/cards/app_download',
      'ads/accounts/:account_id/cards/image_app_download',
      'ads/accounts/:account_id/cards/image_conversation',
      'ads/accounts/:account_id/cards/lead_gen',
      'ads/accounts/:account_id/cards/video_app_download',
      'ads/accounts/:account_id/cards/video_conversation',
      'ads/accounts/:account_id/cards/website',
      'ads/accounts/:account_id/line_items',
      'ads/accounts/:account_id/promoted_accounts',
      'ads/accounts/:account_id/promoted_tweets',
      'ads/accounts/:account_id/tailored_audience_changes',
      'ads/accounts/:account_id/tailored_audiences',
      'ads/accounts/:account_id/targeting_criteria',
      'ads/accounts/:account_id/tweet',
      'ads/accounts/:account_id/videos',
      'ads/accounts/:account_id/web_event_tags',
      'ads/batch/accounts/:account_id/campaigns',
      'ads/batch/accounts/:account_id/line_items',
      'ads/sandbox/accounts/:account_id/app_lists',
      'ads/sandbox/accounts/:account_id/campaigns',
      'ads/sandbox/accounts/:account_id/cards/app_download',
      'ads/sandbox/accounts/:account_id/cards/image_app_download',
      'ads/sandbox/accounts/:account_id/cards/image_conversation',
      'ads/sandbox/accounts/:account_id/cards/lead_gen',
      'ads/sandbox/accounts/:account_id/cards/video_app_download',
      'ads/sandbox/accounts/:account_id/cards/video_conversation',
      'ads/sandbox/accounts/:account_id/cards/website',
      'ads/sandbox/accounts/:account_id/line_items',
      'ads/sandbox/accounts/:account_id/promoted_accounts',
      'ads/sandbox/accounts/:account_id/promoted_tweets',
      'ads/sandbox/accounts/:account_id/tailored_audience_changes',
      'ads/sandbox/accounts/:account_id/tailored_audiences',
      'ads/sandbox/accounts/:account_id/targeting_criteria',
      'ads/sandbox/accounts/:account_id/tweet',
      'ads/sandbox/accounts/:account_id/videos',
      'ads/sandbox/accounts/:account_id/web_event_tags',
      'ads/sandbox/batch/accounts/:account_id/campaigns',
      'ads/sandbox/batch/accounts/:account_id/line_items',
      'blocks/create',
      'blocks/destroy',
      'collections/create',
      'collections/destroy',
      'collections/entries/add',
      'collections/entries/curate',
      'collections/entries/move',
      'collections/entries/remove',
      'collections/update',
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
      'ton/bucket/:bucket',
      'ton/bucket/:bucket?resumable=true',
      'users/lookup',
      'users/report_spam'
    ],
    'PUT' => [
      'ads/accounts/:account_id/campaigns/:campaign_id',
      'ads/accounts/:account_id/cards/app_download/:card_id',
      'ads/accounts/:account_id/cards/image_app_download/:card_id',
      'ads/accounts/:account_id/cards/image_conversation/:card_id',
      'ads/accounts/:account_id/cards/lead_gen/:card_id',
      'ads/accounts/:account_id/cards/video_app_download/:id',
      'ads/accounts/:account_id/cards/video_conversation/:card_id',
      'ads/accounts/:account_id/cards/website/:card_id',
      'ads/accounts/:account_id/line_items/:line_item_id',
      'ads/accounts/:account_id/promoted_tweets/:id',
      'ads/accounts/:account_id/tailored_audiences/global_opt_out',
      'ads/accounts/:account_id/targeting_criteria',
      'ads/accounts/:account_id/videos/:id',
      'ads/accounts/:account_id/web_event_tags/:web_event_tag_id',
      'ads/sandbox/accounts/:account_id/campaigns/:campaign_id',
      'ads/sandbox/accounts/:account_id/cards/app_download/:card_id',
      'ads/sandbox/accounts/:account_id/cards/image_app_download/:card_id',
      'ads/sandbox/accounts/:account_id/cards/image_conversation/:card_id',
      'ads/sandbox/accounts/:account_id/cards/lead_gen/:card_id',
      'ads/sandbox/accounts/:account_id/cards/video_app_download/:id',
      'ads/sandbox/accounts/:account_id/cards/video_conversation/:card_id',
      'ads/sandbox/accounts/:account_id/cards/website/:card_id',
      'ads/sandbox/accounts/:account_id/line_items/:line_item_id',
      'ads/sandbox/accounts/:account_id/promoted_tweets/:id',
      'ads/sandbox/accounts/:account_id/tailored_audiences/global_opt_out',
      'ads/sandbox/accounts/:account_id/targeting_criteria',
      'ads/sandbox/accounts/:account_id/videos/:id',
      'ads/sandbox/accounts/:account_id/web_event_tags/:web_event_tag_id',
      'ton/bucket/:bucket/:file?resumable=true&resumeId=:resumeId'
    ],
    'DELETE' => [
      'ads/accounts/:account_id/campaigns/:campaign_id',
      'ads/accounts/:account_id/cards/app_download/:card_id',
      'ads/accounts/:account_id/cards/image_app_download/:card_id',
      'ads/accounts/:account_id/cards/image_conversation/:card_id',
      'ads/accounts/:account_id/cards/lead_gen/:card_id',
      'ads/accounts/:account_id/cards/video_app_download/:id',
      'ads/accounts/:account_id/cards/video_conversation/:card_id',
      'ads/accounts/:account_id/cards/website/:card_id',
      'ads/accounts/:account_id/line_items/:line_item_id',
      'ads/accounts/:account_id/promoted_tweets/:id',
      'ads/accounts/:account_id/tailored_audiences/:id',
      'ads/accounts/:account_id/targeting_criteria/:id',
      'ads/accounts/:account_id/videos/:id',
      'ads/accounts/:account_id/web_event_tags/:web_event_tag_id',
      'ads/sandbox/accounts/:account_id/campaigns/:campaign_id',
      'ads/sandbox/accounts/:account_id/cards/app_download/:card_id',
      'ads/sandbox/accounts/:account_id/cards/image_app_download/:card_id',
      'ads/sandbox/accounts/:account_id/cards/image_conversation/:card_id',
      'ads/sandbox/accounts/:account_id/cards/lead_gen/:card_id',
      'ads/sandbox/accounts/:account_id/cards/video_app_download/:id',
      'ads/sandbox/accounts/:account_id/cards/video_conversation/:card_id',
      'ads/sandbox/accounts/:account_id/cards/website/:card_id',
      'ads/sandbox/accounts/:account_id/line_items/:line_item_id',
      'ads/sandbox/accounts/:account_id/promoted_tweets/:id',
      'ads/sandbox/accounts/:account_id/tailored_audiences/:id',
      'ads/sandbox/accounts/:account_id/targeting_criteria/:id',
      'ads/sandbox/accounts/:account_id/videos/:id',
      'ads/sandbox/accounts/:account_id/web_event_tags/:web_event_tag_id'
    ]
  ];

  /**
   * Possible file name parameters
   */
  protected static $_possible_files = [
    // Tweets
    'statuses/update_with_media' => ['media[]'],
    'media/upload' => ['media'],
    // Accounts
    'account/update_profile_background_image' => ['image'],
    'account/update_profile_image' => ['image'],
    'account/update_profile_banner' => ['banner']
  ];

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
   * Timeouts
   */
  protected $_timeouts = [
    'request' => 10000,
    'connect' => 3000,
    'remote'  => 5000
  ];

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
    self::$_consumer_key    = $key;
    self::$_consumer_secret = $secret;
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
    self::$_bearer_token = $token;
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
    $this->_timeouts['request'] = (int) $timeout;
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
    $this->_timeouts['connect'] = (int) $timeout;
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
    $this->_timeouts['remote'] = (int) $timeout;
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
   * Watch out for multiple-method API methods!
   *
   * @return array $apimethods
   */
  public function getApiMethods()
  {
    return self::$_api_methods;
  }

  /**
   * Main API handler working on any requests you issue
   *
   * @param string $function The member function you called
   * @param array  $params   The parameters you sent along
   *
   * @return string The API reply encoded in the set return_format
   */

  public function __call($function, $params)
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
    if ($function === 'oauth_requestToken') {
      $this->setToken(null, null);
    }

    // map function name to API method
    list($method, $method_template) = $this->_mapFnToApiMethod($function, $apiparams);

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
   * @param string $function        Function called
   * @param array  $apiparams byref API parameters
   *
   * @return string[] (string method, string method_template)
   */
  protected function _mapFnToApiMethod($function, &$apiparams)
  {
    // replace _ by /
    $method = $this->_mapFnInsertSlashes($function);

    // undo replacement for URL parameters
    $method = $this->_mapFnRestoreParamUnderscores($method);

    // replace AA by URL parameters
    list ($method, $method_template) = $this->_mapFnInlineParams($method, $apiparams);

    if (substr($method, 0, 4) !== 'ton/') {
      // replace A-Z by _a-z
      for ($i = 0; $i < 26; $i++) {
        $method  = str_replace(chr(65 + $i), '_' . chr(97 + $i), $method);
        $method_template = str_replace(chr(65 + $i), '_' . chr(97 + $i), $method_template);
      }
    }

    return [$method, $method_template];
  }

  /**
   * API method mapping: Replaces _ with / character
   *
   * @param string $function Function called
   *
   * @return string API method to call
   */
  protected function _mapFnInsertSlashes($function)
  {
    return str_replace('_', '/', $function);
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
    $params = [
      'screen_name', 'place_id',
      'account_id', 'campaign_id', 'card_id', 'line_item_id',
      'tweet_id', 'web_event_tag_id'
    ];
    foreach ($params as $param) {
      $param = strtoupper($param);
      $replacement_was = str_replace('_', '/', $param);
      $method = str_replace($replacement_was, $param, $method);
    }

    return $method;
  }

  /**
   * Inserts inline parameters into the method name
   *
   * @param string      $method    The method to call
   * @param array byref $apiparams The parameters to send along
   *
   * @return string[] (string method, string method_template)
   */
  protected function _mapFnInlineParams($method, &$apiparams)
  {
    $method_template = $method;
    $match           = [];
    if (preg_match_all('/[A-Z_]{2,}/', $method, $match)) {
      foreach ($match[0] as $param) {
        $param_l = strtolower($param);
        if ($param_l === 'resumeid') {
          $param_l = 'resumeId';
        }
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

    return [$method, $method_template];
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
    $url = self::$_endpoints['oauth'] . 'oauth/' . $type . '?oauth_token=' . $this->_url($this->_oauth_token);
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
    $connection = curl_init($url);

    curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($connection, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($connection, CURLOPT_HEADER, 1);
    curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($connection, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
    curl_setopt(
      $connection, CURLOPT_USERAGENT,
      'codebird-php/' . $this->getVersion() . ' +https://github.com/jublonet/codebird-php'
    );

    if ($this->hasProxy()) {
      curl_setopt($connection, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
      curl_setopt($connection, CURLOPT_PROXY, $this->getProxyHost());
      curl_setopt($connection, CURLOPT_PROXYPORT, $this->getProxyPort());

      if ($this->hasProxyAuthentication()) {
        curl_setopt($connection, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($connection, CURLOPT_PROXYUSERPWD, $this->getProxyAuthentication());
      }
    }

    return $connection;
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
    if (self::$_consumer_key === null) {
      throw new \Exception('To obtain a bearer token, the consumer key must be set.');
    }
    $post_fields = [
      'grant_type' => 'client_credentials'
    ];
    $url        = self::$_endpoints['oauth'] . 'oauth2/token';
    $connection = $this->getCurlInitialization($url);
    curl_setopt($connection, CURLOPT_POST, 1);
    curl_setopt($connection, CURLOPT_POSTFIELDS, $post_fields);

    curl_setopt($connection, CURLOPT_USERPWD, self::$_consumer_key . ':' . self::$_consumer_secret);
    curl_setopt($connection, CURLOPT_HTTPHEADER, [
      'Expect:'
    ]);
    $result = curl_exec($connection);

    // catch request errors
    if ($result === false) {
      throw new \Exception('Request error for bearer token: ' . curl_error($connection));
    }

    // certificate validation results
    $validation_result = curl_errno($connection);
    $this->_validateSslCertificate($validation_result);

    $httpstatus = curl_getinfo($connection, CURLINFO_HTTP_CODE);
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
    if (self::$_consumer_key == null) {
      throw new \Exception('To obtain a bearer token, the consumer key must be set.');
    }

    $url      = self::$_endpoints['oauth'] . 'oauth2/token';
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
            self::$_consumer_key
            . ':'
            . self::$_consumer_secret
          ),
        'timeout'          => $this->_timeouts['request'] / 1000,
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
    $httpstatus = $this->_getHttpStatusFromHeaders($headers);
    $reply      = $this->_parseBearerReply($result, $httpstatus);
    return $reply;
  }


  /**
   * General helpers to avoid duplicate code
   */

  /**
   * Extract HTTP status code from headers
   *
   * @param array $headers The headers to parse
   *
   * @return string The HTTP status code
   */
  protected function _getHttpStatusFromHeaders($headers)
  {
    $httpstatus = '500';
    $match      = [];
    if (!empty($headers[0]) && preg_match('/HTTP\/\d\.\d (\d{3})/', $headers[0], $match)) {
      $httpstatus = $match[1];
    }
    return $httpstatus;
  }

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
   * @return null|array|object The rate-limiting information
   */
  protected function _getRateLimitInfo($headers)
  {
    if (! isset($headers['x-rate-limit-limit'])) {
      return null;
    }
    $rate = [
      'limit'     => $headers['x-rate-limit-limit'],
      'remaining' => $headers['x-rate-limit-remaining'],
      'reset'     => $headers['x-rate-limit-reset']
    ];
    if ($this->_return_format === CODEBIRD_RETURNFORMAT_OBJECT) {
      return (object) $rate;
    }
    return $rate;
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
    }
    return '';
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
    if (self::$_consumer_secret === null) {
      throw new \Exception('To generate a hash, the consumer secret must be set.');
    }
    if (!function_exists('hash_hmac')) {
      throw new \Exception('To generate a hash, the PHP hash extension must be available.');
    }
    return base64_encode(hash_hmac(
      'sha1',
      $data,
      self::$_consumer_secret
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
    if (self::$_consumer_key === null) {
      throw new \Exception('To generate a signature, the consumer key must be set.');
    }
    $sign_base_params = array_map(
      [$this, '_url'],
      [
        'oauth_consumer_key'     => self::$_consumer_key,
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
   * @param string      $method The API method to call
   * @param array byref $params The parameters to send along
   *
   * @return string The HTTP method that should be used
   */
  protected function _detectMethod($method, &$params)
  {
    if (isset($params['httpmethod'])) {
      $httpmethod = $params['httpmethod'];
      unset($params['httpmethod']);
      return $httpmethod;
    }
    $apimethods = $this->getApiMethods();

    // multi-HTTP method API methods
    switch ($method) {
      case 'ads/accounts/:account_id/campaigns':
      case 'ads/sandbox/accounts/:account_id/campaigns':
        if (isset($params['funding_instrument_id'])) {
          return 'POST';
        }
        break;
      case 'ads/accounts/:account_id/line_items':
      case 'ads/sandbox/accounts/:account_id/line_items':
        if (isset($params['campaign_id'])) {
          return 'POST';
        }
        break;
      case 'ads/accounts/:account_id/targeting_criteria':
      case 'ads/sandbox/accounts/:account_id/targeting_criteria':
        if (isset($params['targeting_value'])) {
          return 'POST';
        }
        break;
      case 'ads/accounts/:account_id/app_lists':
      case 'ads/accounts/:account_id/campaigns':
      case 'ads/accounts/:account_id/cards/app_download':
      case 'ads/accounts/:account_id/cards/image_app_download':
      case 'ads/accounts/:account_id/cards/image_conversion':
      case 'ads/accounts/:account_id/cards/lead_gen':
      case 'ads/accounts/:account_id/cards/video_app_download':
      case 'ads/accounts/:account_id/cards/video_conversation':
      case 'ads/accounts/:account_id/cards/website':
      case 'ads/accounts/:account_id/tailored_audiences':
      case 'ads/accounts/:account_id/web_event_tags':
      case 'ads/sandbox/accounts/:account_id/app_lists':
      case 'ads/sandbox/accounts/:account_id/campaigns':
      case 'ads/sandbox/accounts/:account_id/cards/app_download':
      case 'ads/sandbox/accounts/:account_id/cards/image_app_download':
      case 'ads/sandbox/accounts/:account_id/cards/image_conversion':
      case 'ads/sandbox/accounts/:account_id/cards/lead_gen':
      case 'ads/sandbox/accounts/:account_id/cards/video_app_download':
      case 'ads/sandbox/accounts/:account_id/cards/video_conversation':
      case 'ads/sandbox/accounts/:account_id/cards/website':
      case 'ads/sandbox/accounts/:account_id/tailored_audiences':
      case 'ads/sandbox/accounts/:account_id/web_event_tags':
        if (isset($params['name'])) {
          return 'POST';
        }
        break;
      case 'ads/accounts/:account_id/promoted_accounts':
      case 'ads/sandbox/accounts/:account_id/promoted_accounts':
        if (isset($params['user_id'])) {
          return 'POST';
        }
        break;
      case 'ads/accounts/:account_id/promoted_tweets':
      case 'ads/sandbox/accounts/:account_id/promoted_tweets':
        if (isset($params['tweet_ids'])) {
          return 'POST';
        }
        break;
      case 'ads/accounts/:account_id/videos':
      case 'ads/sandbox/accounts/:account_id/videos':
        if (isset($params['video_media_id'])) {
          return 'POST';
        }
        break;
      case 'ads/accounts/:account_id/tailored_audience_changes':
      case 'ads/sandbox/accounts/:account_id/tailored_audience_changes':
        if (isset($params['tailored_audience_id'])) {
          return 'POST';
        }
        break;
      case 'ads/accounts/:account_id/cards/image_conversation/:card_id':
      case 'ads/accounts/:account_id/cards/video_conversation/:card_id':
      case 'ads/accounts/:account_id/cards/website/:card_id':
      case 'ads/sandbox/accounts/:account_id/cards/image_conversation/:card_id':
      case 'ads/sandbox/accounts/:account_id/cards/video_conversation/:card_id':
      case 'ads/sandbox/accounts/:account_id/cards/website/:card_id':
        if (isset($params['name'])) {
          return 'PUT';
        }
        break;
      default:
        // prefer POST and PUT if parameters are set
        if (count($params) > 0) {
          if (isset($apimethods['POST'][$method])) {
            return 'POST';
          }
          if (isset($apimethods['PUT'][$method])) {
            return 'PUT';
          }
        }
    }

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
      // no multipart for these, for now:
      //'account/update_profile_background_image',
      //'account/update_profile_image',
      //'account/update_profile_banner'
    ];
    return in_array($method, $multiparts);
  }

  /**
   * Merge multipart string from parameters array
   *
   * @param string $method_template The method template to call
   * @param string $border          The multipart border
   * @param array  $params          The parameters to send along
   *
   * @return string request
   */
  protected function _getMultipartRequestFromParams($method_template, $border, $params)
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
      $data = $this->_checkForFiles($method_template, $key, $value);
      if ($data !== false) {
        $value = $data;
      }

      $request .= "\r\n\r\n" . $value . "\r\n";
    }

    return $request;
  }

  /**
   * Check for files
   *
   * @param string $method_template The method template to call
   * @param string $key             The parameter name
   * @param string $value           The possible file name or URL
   *
   * @return mixed
   */
  protected function _checkForFiles($method_template, $key, $value) {
    if (!in_array($key, self::$_possible_files[$method_template])) {
      return false;
    }
    $data = $this->_buildBinaryBody($value);
    if ($data === $value) {
      return false;
    }
    return $data;
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
    // method might have files?
    if (! in_array($method, array_keys(self::$_possible_files))) {
      return;
    }

    $multipart_border = '--------------------' . $this->_nonce();
    $multipart_request =
      $this->_getMultipartRequestFromParams($method, $multipart_border, $params)
      . '--' . $multipart_border . '--';

    return $multipart_request;
  }

  /**
   * Detect filenames in upload parameters
   *
   * @param mixed $input The data or file name to parse
   *
   * @return null|string
   */
  protected function _buildBinaryBody($input)
  {
    if (// is it a file, a readable one?
      @file_exists($input)
      && @is_readable($input)
    ) {
      // try to read the file
      $data = @file_get_contents($input);
      if ($data !== false && strlen($data) !== 0) {
        return $data;
      }
    } elseif (// is it a remote file?
      filter_var($input, FILTER_VALIDATE_URL)
      && preg_match('/^https?:\/\//', $input)
    ) {
      $data = $this->_fetchRemoteFile($input);
      if ($data !== false) {
        return $data;
      }
    }
    return $input;
  }

  /**
   * Fetches a remote file
   *
   * @param string $url The URL to download from
   *
   * @return mixed The file contents or FALSE
   */
  protected function _fetchRemoteFile($url)
  {
    // try to fetch the file
    if ($this->_use_curl) {
      $connection = $this->getCurlInitialization($url);
      curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($connection, CURLOPT_HEADER, 0);
      // no SSL validation for downloading media
      curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 1);
      curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($connection, CURLOPT_TIMEOUT_MS, $this->_timeouts['remote']);
      curl_setopt($connection, CURLOPT_CONNECTTIMEOUT_MS, $this->_timeouts['remote'] / 2);
      // find files that have been redirected
      curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
      // process compressed images
      curl_setopt($connection, CURLOPT_ENCODING, 'gzip,deflate,sdch');
      $result = curl_exec($connection);
      if ($result !== false) {
        return $result;
      }
      return false;
    }
    // no cURL
    $contextOptions = [
      'http' => [
        'method'           => 'GET',
        'protocol_version' => '1.1',
        'timeout'          => $this->_timeouts['remote']
      ],
      'ssl' => [
        'verify_peer'  => false
      ]
    ];
    list($result) = $this->getNoCurlInitialization($url, $contextOptions);
    if ($result !== false) {
      return $result;
    }
    return false;
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
   * Detects if API call should use JSON body
   *
   * @param string $method The API method to call
   *
   * @return bool Whether the method is defined as accepting JSON body
   */
  protected function _detectJsonBody($method) {
    $json_bodies = [
      'collections/entries/curate'
    ];
    return in_array($method, $json_bodies);
  }

  /**
   * Detects if API call should use binary body
   *
   * @param string $method_template The API method to call
   *
   * @return bool Whether the method is defined as accepting binary body
   */
  protected function _detectBinaryBody($method_template) {
    $binary = [
      'ton/bucket/:bucket',
      'ton/bucket/:bucket?resumable=true',
      'ton/bucket/:bucket/:file?resumable=true&resumeId=:resumeId'
    ];
    return in_array($method_template, $binary);
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
   * @param string $method_template The API method to call
   *
   * @return string The URL to send the request to
   */
  protected function _getEndpoint($method, $method_template)
  {
    $url = self::$_endpoints['rest'] . $method . '.json';
    if (substr($method_template, 0, 5) === 'oauth') {
      $url = self::$_endpoints['oauth'] . $method;
    } elseif ($this->_detectMedia($method_template)) {
      $url = self::$_endpoints['media'] . $method . '.json';
    } elseif ($variant = $this->_detectStreaming($method_template)) {
      $url = self::$_endpoints['streaming'][$variant] . $method . '.json';
    } elseif ($this->_detectBinaryBody($method_template)) {
      $url = self::$_endpoints['ton'] . $method;
    } elseif (substr($method_template, 0, 12) === 'ads/sandbox/') {
      $url = self::$_endpoints['ads']['sandbox'] . substr($method, 12);
    } elseif (substr($method_template, 0, 4) === 'ads/') {
      $url = self::$_endpoints['ads']['production'] . substr($method, 4);
    }
    return $url;
  }

  /**
   * Calls the API
   *
   * @param string          $httpmethod      The HTTP method to use for making the request
   * @param string          $method          The API method to call
   * @param string          $method_template The API method template to call
   * @param array  optional $params          The parameters to send along
   * @param bool   optional $multipart       Whether to use multipart/form-data
   * @param bool   optional $app_only_auth   Whether to use app-only bearer authentication
   *
   * @return string The API reply, encoded in the set return_format
   */

  protected function _callApi($httpmethod, $method, $method_template, $params = [], $multipart = false, $app_only_auth = false)
  {
    if (! $app_only_auth
      && $this->_oauth_token === null
      && substr($method, 0, 5) !== 'oauth'
    ) {
        throw new \Exception('To call this API, the OAuth access token must be set.');
    }
    // use separate API access for streaming API
    if ($this->_detectStreaming($method) !== false) {
      return $this->_callApiStreaming($httpmethod, $method, $method_template, $params, $app_only_auth);
    }

    if ($this->_use_curl) {
      return $this->_callApiCurl($httpmethod, $method, $method_template, $params, $multipart, $app_only_auth);
    }
    return $this->_callApiNoCurl($httpmethod, $method, $method_template, $params, $multipart, $app_only_auth);
  }

  /**
   * Calls the API using cURL
   *
   * @param string          $httpmethod    The HTTP method to use for making the request
   * @param string          $method        The API method to call
   * @param string          $method_template The API method template to call
   * @param array  optional $params        The parameters to send along
   * @param bool   optional $multipart     Whether to use multipart/form-data
   * @param bool   optional $app_only_auth Whether to use app-only bearer authentication
   *
   * @return string The API reply, encoded in the set return_format
   */

  protected function _callApiCurl(
    $httpmethod, $method, $method_template, $params = [], $multipart = false, $app_only_auth = false
  )
  {
    list ($authorization, $url, $params, $request_headers)
      = $this->_callApiPreparations(
        $httpmethod, $method, $method_template, $params, $multipart, $app_only_auth
      );

    $connection        = $this->getCurlInitialization($url);
    $request_headers[] = 'Authorization: ' . $authorization;
    $request_headers[] = 'Expect:';

    if ($httpmethod !== 'GET') {
      curl_setopt($connection, CURLOPT_POST, 1);
      curl_setopt($connection, CURLOPT_POSTFIELDS, $params);
      if (in_array($httpmethod, ['POST', 'PUT', 'DELETE'])) {
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, $httpmethod);
      }
    }

    curl_setopt($connection, CURLOPT_HTTPHEADER, $request_headers);
    curl_setopt($connection, CURLOPT_TIMEOUT_MS, $this->_timeouts['request']);
    curl_setopt($connection, CURLOPT_CONNECTTIMEOUT_MS, $this->_timeouts['connect']);

    $result = curl_exec($connection);

    // catch request errors
    if ($result === false) {
      throw new \Exception('Request error for API call: ' . curl_error($connection));
    }

    // certificate validation results
    $validation_result = curl_errno($connection);
    $this->_validateSslCertificate($validation_result);

    $httpstatus            = curl_getinfo($connection, CURLINFO_HTTP_CODE);
    list($headers, $reply) = $this->_parseApiHeaders($result);
    // TON API & redirects
    $reply                 = $this->_parseApiReplyPrefillHeaders($headers, $reply);
    $reply                 = $this->_parseApiReply($reply);
    $rate                  = $this->_getRateLimitInfo($headers);

    $reply = $this->_appendHttpStatusAndRate($reply, $httpstatus, $rate);
    return $reply;
  }

  /**
   * Calls the API without cURL
   *
   * @param string          $httpmethod      The HTTP method to use for making the request
   * @param string          $method          The API method to call
   * @param string          $method_template The API method template to call
   * @param array  optional $params          The parameters to send along
   * @param bool   optional $multipart       Whether to use multipart/form-data
   * @param bool   optional $app_only_auth   Whether to use app-only bearer authentication
   *
   * @return string The API reply, encoded in the set return_format
   */

  protected function _callApiNoCurl(
    $httpmethod, $method, $method_template, $params = [], $multipart = false, $app_only_auth = false
  )
  {
    list ($authorization, $url, $params, $request_headers)
      = $this->_callApiPreparations(
        $httpmethod, $method, $method_template, $params, $multipart, $app_only_auth
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
        'timeout'          => $this->_timeouts['request'] / 1000,
        'content'          => in_array($httpmethod, ['POST', 'PUT']) ? $params : null,
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
    $httpstatus            = $this->_getHttpStatusFromHeaders($headers);
    list($headers, $reply) = $this->_parseApiHeaders($result);
    // TON API & redirects
    $reply                 = $this->_parseApiReplyPrefillHeaders($headers, $reply);
    $reply                 = $this->_parseApiReply($reply);
    $rate                  = $this->_getRateLimitInfo($headers);

    $reply = $this->_appendHttpStatusAndRate($reply, $httpstatus, $rate);
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
   * @param string  $method_template The API method template to call
   * @param array   $params          The parameters to send along
   * @param bool    $multipart       Whether to use multipart/form-data
   * @param bool    $app_only_auth   Whether to use app-only bearer authentication
   *
   * @return array (string authorization, array params, array request_headers)
   */
  protected function _callApiPreparationsPost(
    $httpmethod, $url, $method, $method_template, $params, $multipart, $app_only_auth
  ) {
    $authorization   = null;
    $request_headers = [];
    if ($multipart) {
      if (! $app_only_auth) {
        $authorization = $this->_sign($httpmethod, $url, []);
      }
      $params = $this->_buildMultipart($method, $params);
      $first_newline      = strpos($params, "\r\n");
      $multipart_boundary = substr($params, 2, $first_newline - 2);
      $request_headers[]  = 'Content-Type: multipart/form-data; boundary='
        . $multipart_boundary;
    } elseif ($this->_detectJsonBody($method)) {
      $authorization = $this->_sign($httpmethod, $url, []);
      $params = json_encode($params);
      $request_headers[] = 'Content-Type: application/json';
    } elseif ($this->_detectBinaryBody($method_template)) {
      // transform parametric headers to real headers
      foreach ([
          'Content-Type', 'X-TON-Content-Type',
          'X-TON-Content-Length', 'Content-Range'
        ] as $key) {
        if (isset($params[$key])) {
          $request_headers[] = $key . ': ' . $params[$key];
          unset($params[$key]);
        }
      }
      $sign_params = [];
      parse_str(parse_url($method, PHP_URL_QUERY), $sign_params);
      if ($sign_params === null) {
        $sign_params = [];
      }
      $authorization = $this->_sign($httpmethod, $url, $sign_params);
      if (isset($params['media'])) {
        $params = $this->_buildBinaryBody($params['media']);
      } else {
        // resumable upload
        $params = [];
      }
    } else {
      // check for possible files in non-multipart methods
      foreach ($params as $key => $value) {
        $data = $this->_checkForFiles($method_template, $key, $value);
        if ($data !== false) {
          $params[$key] = base64_encode($data);
        }
      }
      if (! $app_only_auth) {
        $authorization = $this->_sign($httpmethod, $url, $params);
      }
      $params = http_build_query($params);
    }
    return [$authorization, $params, $request_headers];
  }

  /**
   * Appends HTTP status and rate limiting info to the reply
   *
   * @param array|object|string $reply      The reply to append to
   * @param string              $httpstatus The HTTP status code to append
   * @param mixed               $rate       The rate limiting info to append
   */
  protected function _appendHttpStatusAndRate($reply, $httpstatus, $rate)
  {
    switch ($this->_return_format) {
      case CODEBIRD_RETURNFORMAT_ARRAY:
        $reply['httpstatus'] = $httpstatus;
        $reply['rate']       = $rate;
        break;
      case CODEBIRD_RETURNFORMAT_OBJECT:
        $reply->httpstatus = $httpstatus;
        $reply->rate       = $rate;
        break;
      case CODEBIRD_RETURNFORMAT_JSON:
        $reply             = json_decode($reply);
        $reply->httpstatus = $httpstatus;
        $reply->rate       = $rate;
        $reply             = json_encode($reply);
        break;
    }
    return $reply;
  }

  /**
   * Get Bearer authorization string
   *
   * @return string authorization
   */
  protected function _getBearerAuthorization()
  {
    if (self::$_consumer_key === null
      && self::$_bearer_token === null
    ) {
      throw new \Exception('To make an app-only auth API request, consumer key or bearer token must be set.');
    }
    // automatically fetch bearer token, if necessary
    if (self::$_bearer_token === null) {
      $this->oauth2_token();
    }
    return 'Bearer ' . self::$_bearer_token;
  }

  /**
   * Do preparations to make the API call
   *
   * @param string  $httpmethod      The HTTP method to use for making the request
   * @param string  $method          The API method to call
   * @param string  $method_template The API method template to call
   * @param array   $params          The parameters to send along
   * @param bool    $multipart       Whether to use multipart/form-data
   * @param bool    $app_only_auth   Whether to use app-only bearer authentication
   *
   * @return array (string authorization, string url, array params, array request_headers)
   */
  protected function _callApiPreparations(
    $httpmethod, $method, $method_template, $params, $multipart, $app_only_auth
  )
  {
    $url             = $this->_getEndpoint($method, $method_template);
    $request_headers = [];
    if ($httpmethod === 'GET') {
      // GET
      list ($authorization, $url) =
        $this->_callApiPreparationsGet($httpmethod, $url, $params, $app_only_auth);
    } else {
      // POST
      list ($authorization, $params, $request_headers) =
        $this->_callApiPreparationsPost($httpmethod, $url, $method, $method_template, $params, $multipart, $app_only_auth);
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
   * @param string          $method_template The API method template to call
   * @param array  optional $params          The parameters to send along
   * @param bool   optional $app_only_auth   Whether to use app-only bearer authentication
   *
   * @return void
   */

  protected function _callApiStreaming(
    $httpmethod, $method, $method_template, $params = [], $app_only_auth = false
  )
  {
    if ($this->_streaming_callback === null) {
      throw new \Exception('Set streaming callback before consuming a stream.');
    }

    $params['delimited'] = 'length';

    list ($authorization, $url, $params, $request_headers)
      = $this->_callApiPreparations(
        $httpmethod, $method, $method_template, $params, false, $app_only_auth
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
    $connection = stream_socket_client(
      'ssl://' . $hostname . ':443',
      $errno, $errstr,
      $this->_timeouts['connect'],
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
    fputs($connection, $request);
    stream_set_blocking($connection, 0);
    stream_set_timeout($connection, 0);

    // collect headers
    do {
      $result  = stream_get_line($connection, 1048576, "\r\n\r\n");
    } while(!$result);
    $headers = explode("\r\n", $result);

    // find HTTP status
    $httpstatus     = $this->_getHttpStatusFromHeaders($headers);
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

    while (!feof($connection)) {
      // call signal handlers, if any
      if ($signal_function) {
        pcntl_signal_dispatch();
      }
      $connection_array = [$connection];
      $write            = $except = null;
      if (false === ($num_changed_streams = stream_select($connection_array, $write, $except, 0, 200000))) {
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
      $chunk_length = fgets($connection, 10);
      if ($chunk_length === '' || !$chunk_length = hexdec($chunk_length)) {
        continue;
      }

      $chunk = '';
      do {
        $chunk .= fread($connection, $chunk_length);
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
        fclose($connection);
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
   * Parses the API headers to return Location and Ton API headers
   *
   * @param array  $headers The headers list
   * @param string $reply   The actual HTTP body
   *
   * @return string $reply
   */
  protected function _parseApiReplyPrefillHeaders($headers, $reply)
  {
    if ($reply === '' && (isset($headers['Location']))) {
      $reply = [
        'Location' => $headers['Location']
      ];
      if (isset($headers['X-TON-Min-Chunk-Size'])) {
        $reply['X-TON-Min-Chunk-Size'] = $headers['X-TON-Min-Chunk-Size'];
      }
      if (isset($headers['X-TON-Max-Chunk-Size'])) {
        $reply['X-TON-Max-Chunk-Size'] = $headers['X-TON-Max-Chunk-Size'];
      }
      if (isset($headers['Range'])) {
        $reply['Range'] = $headers['Range'];
      }
      $reply = json_encode($reply);
    }
    return $reply;
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
