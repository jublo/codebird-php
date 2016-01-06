<?php

namespace Codebird;
require_once ('test/codebirdt.php');

/**
 * A Twitter library in PHP.
 *
 * @package   codebird-test
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2010-2016 Jublo Solutions <support@jublo.net>
 * @license   https://opensource.org/licenses/GPL-3.0 GNU General Public License 3.0
 * @link      https://github.com/jublonet/codebird-php
 */

/**
 * Codebird testing class with mock data source
 *
 * @package codebird-test
 */
class CodebirdM extends CodebirdT
{
  /**
   * Stores the request data
   */
  protected $_requests = [];

  /**
   * Mock API replies
   */
  protected static $_mock_replies = [
    'default' => [
      'httpstatus' => 404,
      'reply' => "HTTP/1.1 404 Not Found\r\ncontent-length: 68\r\ncontent-type: application/json;charset=utf-8\r\ndate: Sun, 06 Dec 2015 14:43:28 GMT\r\nserver: tsa_b\r\nset-cookie: guest_id=v1%3A144941300885288055; Domain=.twitter.com; Path=/; Expires=Tue, 05-Dec-2017 14:43:28 UTC\r\nstrict-transport-security: max-age=631138519\r\nx-connection-hash: 12218aef9e9757609afb08e661fa3b9b\r\nx-response-time: 2\r\n\r\n{\"errors\":[{\"message\":\"Sorry, that page does not exist\",\"code\":34}]}"
    ],
    'proxy1' => [
      'httpstatus' => 404,
      'reply' => "HTTP/1.1 200 Connection Established\r\n\r\nHTTP/1.1 404 Not Found\r\ncontent-length: 68\r\ncontent-type: application/json;charset=utf-8\r\ndate: Sun, 06 Dec 2015 14:43:28 GMT\r\nserver: tsa_b\r\nset-cookie: guest_id=v1%3A144941300885288055; Domain=.twitter.com; Path=/; Expires=Tue, 05-Dec-2017 14:43:28 UTC\r\nstrict-transport-security: max-age=631138519\r\nx-connection-hash: 12218aef9e9757609afb08e661fa3b9b\r\nx-response-time: 2\r\n\r\n{\"errors\":[{\"message\":\"Sorry, that page does not exist\",\"code\":34}]}"
    ],
    'GET http://www.example.org/found.txt' => [
      'httpstatus' => 200,
      'reply' => "HTTP/1.1 200 OK\r\nContent-Length: 12\r\n\r\nA test file."
    ],
    'GET https://api.twitter.com/1.1/users/show.json?screen_name=TwitterAPI' => [
      'httpstatus' => 200,
      'reply' => "HTTP/1.1 200 OK\r\ncache-control: no-cache, no-store, must-revalidate, pre-check=0, post-check=0\r\ncontent-disposition: attachment; filename=json.json\r\ncontent-length: 2801\r\ncontent-type: application/json;charset=utf-8\r\ndate: Sun, 06 Dec 2015 14:55:46 GMT\r\nexpires: Tue, 31 Mar 1981 05:00:00 GMT\r\nlast-modified: Sun, 06 Dec 2015 14:55:46 GMT\r\npragma: no-cache\r\nserver: tsa_b\r\nset-cookie: lang=en-gb; Path=/\r\nset-cookie: guest_id=v1%3A144941374684866365; Domain=.twitter.com; Path=/; Expires=Tue, 05-Dec-2017 14:55:46 UTC\r\nstatus: 200 OK\r\nstrict-transport-security: max-age=631138519\r\nx-access-level: read-write-directmessages\r\nx-connection-hash: 1906b689730b92318bccf65b496f74d0\r\nx-content-type-options: nosniff\r\nx-frame-options: SAMEORIGIN\r\nx-rate-limit-limit: 181\r\nx-rate-limit-remaining: 177\r\nx-rate-limit-reset: 1449414513\r\nx-response-time: 44\r\nx-transaction: 663cc05c64857ba0\r\nx-twitter-response-tags: BouncerCompliant\r\nx-xss-protection: 1; mode=block\r\n\r\n{\"id\":6253282,\"id_str\":\"6253282\",\"name\":\"Twitter API\",\"screen_name\":\"twitterapi\",\"location\":\"San Francisco, CA\",\"profile_location\":null,\"description\":\"The Real Twitter API. I tweet about API changes, service issues and happily answer questions about Twitter and our API. Don't get an answer? It's on my website.\",\"url\":\"http:\/\/t.co\/78pYTvWfJd\",\"entities\":{\"url\":{\"urls\":[{\"url\":\"http:\/\/t.co\/78pYTvWfJd\",\"expanded_url\":\"http:\/\/dev.twitter.com\",\"display_url\":\"dev.twitter.com\",\"indices\":[0,22]}]},\"description\":{\"urls\":[]}},\"protected\":false,\"followers_count\":4993679,\"friends_count\":48,\"listed_count\":13001,\"created_at\":\"Wed May 23 06:01:13 +0000 2007\",\"favourites_count\":27,\"utc_offset\":-28800,\"time_zone\":\"Pacific Time (US & Canada)\",\"geo_enabled\":true,\"verified\":true,\"statuses_count\":3553,\"lang\":\"en\",\"status\":{\"created_at\":\"Tue Nov 24 08:56:07 +0000 2015\",\"id\":669077021138493440,\"id_str\":\"669077021138493440\",\"text\":\"Additional 64-bit entity ID migration coming in Feb 2016 https:\/\/t.co\/eQIGvw1rsJ\",\"source\":\"\u003ca href=\\\"https:\/\/about.twitter.com\/products\/tweetdeck\\\" rel=\\\"nofollow\\\"\u003eTweetDeck\u003c\/a\u003e\",\"truncated\":false,\"in_reply_to_status_id\":null,\"in_reply_to_status_id_str\":null,\"in_reply_to_user_id\":null,\"in_reply_to_user_id_str\":null,\"in_reply_to_screen_name\":null,\"geo\":null,\"coordinates\":null,\"place\":null,\"contributors\":null,\"retweet_count\":67,\"favorite_count\":79,\"entities\":{\"hashtags\":[],\"symbols\":[],\"user_mentions\":[],\"urls\":[{\"url\":\"https:\/\/t.co\/eQIGvw1rsJ\",\"expanded_url\":\"https:\/\/twittercommunity.com\/t\/migration-of-twitter-core-entities-to-64-bit-ids\/56881\",\"display_url\":\"twittercommunity.com\/t\/migration-of\u2026\",\"indices\":[57,80]}]},\"favorited\":false,\"retweeted\":false,\"possibly_sensitive\":false,\"lang\":\"en\"},\"contributors_enabled\":false,\"is_translator\":false,\"is_translation_enabled\":false,\"profile_background_color\":\"C0DEED\",\"profile_background_image_url\":\"http:\/\/pbs.twimg.com\/profile_background_images\/656927849\/miyt9dpjz77sc0w3d4vj.png\",\"profile_background_image_url_https\":\"https:\/\/pbs.twimg.com\/profile_background_images\/656927849\/miyt9dpjz77sc0w3d4vj.png\",\"profile_background_tile\":true,\"profile_image_url\":\"http:\/\/pbs.twimg.com\/profile_images\/2284174872\/7df3h38zabcvjylnyfe3_normal.png\",\"profile_image_url_https\":\"https:\/\/pbs.twimg.com\/profile_images\/2284174872\/7df3h38zabcvjylnyfe3_normal.png\",\"profile_banner_url\":\"https:\/\/pbs.twimg.com\/profile_banners\/6253282\/1431474710\",\"profile_link_color\":\"0084B4\",\"profile_sidebar_border_color\":\"C0DEED\",\"profile_sidebar_fill_color\":\"DDEEF6\",\"profile_text_color\":\"333333\",\"profile_use_background_image\":true,\"has_extended_profile\":false,\"default_profile\":false,\"default_profile_image\":false,\"following\":true,\"follow_request_sent\":false,\"notifications\":false}"
    ],
    'POST https://api.twitter.com/oauth2/token' => [
      'httpstatus' => 200,
      'reply' => "HTTP/1.1 200 OK\r\ncache-control: no-cache, no-store, must-revalidate, pre-check=0, post-check=0\r\ncontent-disposition: attachment; filename=json.json\r\ncontent-length: 52\r\ncontent-type: application/json;charset=utf-8\r\ndate: Sun, 06 Dec 2015 15:53:02 GMT\r\nexpires: Tue, 31 Mar 1981 05:00:00 GMT\r\nlast-modified: Sun, 06 Dec 2015 15:53:01 GMT\r\nml: S\r\npragma: no-cache\r\nserver: tsa_b\r\nset-cookie: guest_id=v1%3A144941718194388038; Domain=.twitter.com; Path=/; Expires=Tue, 05-Dec-2017 15:53:02 UTC\r\nstatus: 200 OK\r\nstrict-transport-security: max-age=631138519\r\nx-connection-hash: 97f4d4e6a33433b477510d8c58a0b026\r\nx-content-type-options: nosniff\r\nx-frame-options: DENY\r\nx-response-time: 87\r\nx-transaction: 6a0e5e8144d7e6df\r\nx-tsa-request-body-time: 164\r\nx-twitter-response-tags: BouncerCompliant\r\nx-ua-compatible: IE=edge,chrome=1\r\nx-xss-protection: 1; mode=block\r\n\r\n{\"token_type\":\"bearer\",\"access_token\":\"VqiO0n2HrKE\"}"
    ]
  ];

  /**
   * Gets a mock cURL handle
   * @param string $url the URL for the curl initialization
   * @return string connection ID
   */
  protected function _curl_init($url = null)
  {
    $request = [
      'url' => $url,
      CURLOPT_RETURNTRANSFER => false
    ];
    $id = mt_rand();
    $this->_requests[$id] = $request;

    return $id;
  }

  /**
   * Sets mock cURL parameters
   * @param string $id     connection ID
   * @param int    $option cURL option to set
   * @param mixed  $value  Value to set for the option
   * @return bool
   */
  protected function _curl_setopt($id, $option, $value)
  {
    if (!isset($this->_requests[$id])) {
      return false;
    }

    $this->_requests[$id][$option] = $value;

    return true;
  }

  /**
   * Executes mock cURL transfer
   * @param string $id connection ID
   * @return bool|string
   */
  protected function _curl_exec($id)
  {
    if (!isset($this->_requests[$id])) {
      return false;
    }

    $request = $this->_requests[$id];
    $url     = $this->_requests[$id]['url'];
    $reply   = self::$_mock_replies['default'];

    $httpmethod = 'GET';
    if (isset($request[CURLOPT_POST]) && $request[CURLOPT_POST]) {
      $httpmethod = 'POST';
    }
    if (isset($request[CURLOPT_CUSTOMREQUEST])) {
      $httpmethod = $request[CURLOPT_CUSTOMREQUEST];
    }

    $index = $httpmethod . ' ' . $url;

    if (isset(self::$_mock_replies[$index])) {
      $reply = self::$_mock_replies[$index];
    }

    $this->_requests[$id][CURLINFO_HTTP_CODE] = $reply['httpstatus'];
    $this->_requests[$id]['reply'] = $reply['reply'];

    if (! $this->_requests[$id][CURLOPT_HEADER]
      && stristr($reply['reply'], "\r\n\r\n")
    ) {
      $reply_parts = explode("\r\n\r\n", $reply['reply'], 2);
      $reply['reply'] = $reply_parts[1];
    }

    if ($this->_requests[$id][CURLOPT_RETURNTRANSFER]) {
      return $reply['reply'];
    }

    return true;
  }

  /**
   * Gets cURL error
   * @param string $id connection ID
   * @return string
   */
  protected function _curl_error($id)
  {
    return '';
  }

  /**
   * Gets cURL error
   * @param string $id connection ID
   * @return int
   */
  protected function _curl_errno($id)
  {
    return 0;
  }

  /**
   * Gets info about cURL connection
   * @param string $id  connection ID
   * @param int    $opt option to get
   * @return mixed
   */
  protected function _curl_getinfo($id, $opt = 0)
  {
    if (!isset($this->_requests[$id])) {
      return false;
    }

    $request = $this->_requests[$id];

    if (!$opt) {
      return [
        'url' => $request['url'],
        'http_code' => $request[CURLINFO_HTTP_CODE]
      ];
    }

    if (isset($request[$opt])) {
      return $request[$opt];
    }

    return false;
  }

  /**
   * Gets fake time
   * @return int Timestamp
   */
  protected function _time()
  {
    return 1412345678;
  }

  /**
   * Gets fake time
   * @param bool $get_as_float
   * @return int Timestamp
   */
  protected function _microtime($get_as_float = false)
  {
    if ($get_as_float) {
      return 1412345678.777;
    }
    return '777 1412345678';
  }
}
