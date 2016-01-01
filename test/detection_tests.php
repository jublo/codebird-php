<?php

namespace Codebird;
require_once ('test/codebirdm.php');

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
 * Detection tests
 *
 * @package codebird-test
 */
class Detection_Test extends \PHPUnit_Framework_TestCase
{
  /**
   * Initialise Codebird class
   *
   * @return \Codebird\Codebird The Codebird class
   */
  protected function getCB()
  {
    $cb = new CodebirdM();

    return $cb;
  }

  /**
   * Tests _detectMethod
   * @expectedException \Exception
   * @expectedExceptionMessage Can't find HTTP method to use for "non-existent".
   */
  public function testDetectMethod1()
  {
    $cb = $this->getCB();
    $params = [];
    $cb->call('_detectMethod', 'non-existent', $params);
  }

  /**
   * Tests _detectMethod
   */
  public function testDetectMethod2()
  {
    $cb = $this->getCB();

    // forced httpmethod
    $params = ['httpmethod' => 'DELETE'];
    $this->assertEquals(
      'DELETE',
      $cb->call('_detectMethod', 'doesnt-matter', $params)
    );

    // normal detection
    $params = [];
    $this->assertEquals('GET', $cb->call('_detectMethod', 'search/tweets', $params));
    $this->assertEquals('POST', $cb->call('_detectMethod', 'statuses/update', $params));
    $this->assertEquals(
      'PUT',
      $cb->call(
        '_detectMethod',
        'ton/bucket/:bucket/:file?resumable=true&resumeId=:resumeId',
        $params
      )
    );

    // parameter-based detection
    $this->assertEquals('GET', $cb->call('_detectMethod', 'account/settings', $params));
    $params = ['test' => 12];
    $this->assertEquals('POST', $cb->call('_detectMethod', 'account/settings', $params));

    $httpmethods_by_param = [
      'POST' => [
        'campaign_id' => [
          'ads/accounts/:account_id/line_items',
          'ads/sandbox/accounts/:account_id/line_items'
        ],
        'name' => [
          'ads/accounts/:account_id/app_lists',
          'ads/accounts/:account_id/campaigns',
          'ads/accounts/:account_id/cards/app_download',
          'ads/accounts/:account_id/cards/image_app_download',
          'ads/accounts/:account_id/cards/image_conversation',
          'ads/accounts/:account_id/cards/lead_gen',
          'ads/accounts/:account_id/cards/video_app_download',
          'ads/accounts/:account_id/cards/video_conversation',
          'ads/accounts/:account_id/cards/website',
          'ads/accounts/:account_id/tailored_audiences',
          'ads/accounts/:account_id/web_event_tags',
          'ads/sandbox/accounts/:account_id/app_lists',
          'ads/sandbox/accounts/:account_id/campaigns',
          'ads/sandbox/accounts/:account_id/cards/app_download',
          'ads/sandbox/accounts/:account_id/cards/image_app_download',
          'ads/sandbox/accounts/:account_id/cards/image_conversation',
          'ads/sandbox/accounts/:account_id/cards/lead_gen',
          'ads/sandbox/accounts/:account_id/cards/video_app_download',
          'ads/sandbox/accounts/:account_id/cards/video_conversation',
          'ads/sandbox/accounts/:account_id/cards/website',
          'ads/sandbox/accounts/:account_id/tailored_audiences',
          'ads/sandbox/accounts/:account_id/web_event_tags'
        ],
        'tailored_audience_id' => [
          'ads/accounts/:account_id/tailored_audience_changes',
          'ads/sandbox/accounts/:account_id/tailored_audience_changes'
        ],
        'targeting_value' => [
          'ads/accounts/:account_id/targeting_criteria',
          'ads/sandbox/accounts/:account_id/targeting_criteria'
        ],
        'tweet_ids' => [
          'ads/accounts/:account_id/promoted_tweets',
          'ads/sandbox/accounts/:account_id/promoted_tweets'
        ],
        'user_id' => [
          'ads/accounts/:account_id/promoted_accounts',
          'ads/sandbox/accounts/:account_id/promoted_accounts'
        ],
        'video_media_id' => [
          'ads/accounts/:account_id/videos',
          'ads/sandbox/accounts/:account_id/videos'
        ]
      ],
      'PUT' => [
        'name' => [
          'ads/accounts/:account_id/cards/image_conversation/:card_id',
          'ads/accounts/:account_id/cards/video_conversation/:card_id',
          'ads/accounts/:account_id/cards/website/:card_id',
          'ads/sandbox/accounts/:account_id/cards/image_conversation/:card_id',
          'ads/sandbox/accounts/:account_id/cards/video_conversation/:card_id',
          'ads/sandbox/accounts/:account_id/cards/website/:card_id'
        ]
      ]
    ];
    foreach ($httpmethods_by_param as $httpmethod => $methods_by_param) {
      foreach ($methods_by_param as $param => $methods) {
        foreach ($methods as $method) {
          $params = [];
          $this->assertEquals(
            'GET',
            $cb->call('_detectMethod', $method, $params),
            $method
          );
          $params[$param] = '123';
          $this->assertEquals(
            $httpmethod,
            $cb->call('_detectMethod', $method, $params),
            $method
          );
        }
      }
    }
  }

  /**
   * Tests _detectMultipart
   */
  public function testDetectMultipart()
  {
    $cb = $this->getCB();
    $this->assertFalse($cb->call('_detectMultipart', ['statuses/update']));
    $this->assertTrue($cb->call('_detectMultipart', ['statuses/update_with_media']));
    $this->assertTrue($cb->call('_detectMultipart', ['media/upload']));
  }

  /**
   * Tests _detectMedia
   */
  public function testDetectMedia()
  {
    $cb = $this->getCB();
    $this->assertFalse($cb->call('_detectMedia', ['statuses/update']));
    $this->assertTrue($cb->call('_detectMedia', ['media/upload']));
  }

  /**
   * Tests _detectJsonBody
   */
  public function testDetectJsonBody()
  {
    $cb = $this->getCB();
    $this->assertFalse($cb->call('_detectJsonBody', ['statuses/update']));
    $this->assertTrue($cb->call('_detectJsonBody', ['collections/entries/curate']));
  }

  /**
   * Tests _detectBinaryBody
   */
  public function testDetectBinaryBody()
  {
    $cb = $this->getCB();
    $this->assertFalse($cb->call('_detectBinaryBody', ['statuses/update']));
    $this->assertTrue($cb->call('_detectBinaryBody', ['ton/bucket/:bucket']));
    $this->assertTrue($cb->call('_detectBinaryBody', ['ton/bucket/:bucket?resumable=true']));
    $this->assertTrue($cb->call(
      '_detectBinaryBody',
      ['ton/bucket/:bucket/:file?resumable=true&resumeId=:resumeId']
    ));
  }

  /**
   * Tests _detectStreaming
   */
  public function testDetectStreaming()
  {
    $cb = $this->getCB();
    $this->assertFalse($cb->call('_detectStreaming', ['statuses/update']));
    $this->assertEquals('public', $cb->call('_detectStreaming', ['statuses/sample']));
    $this->assertEquals('public', $cb->call('_detectStreaming', ['statuses/filter']));
    $this->assertEquals('public', $cb->call('_detectStreaming', ['statuses/firehose']));
    $this->assertEquals('user', $cb->call('_detectStreaming', ['user']));
    $this->assertEquals('site', $cb->call('_detectStreaming', ['site']));
  }

  /**
   * Tests _getEndpoint
   */
  public function testGetEndpoint()
  {
    $cb = $this->getCB();
    $this->assertEquals(
      'https://api.twitter.com/1.1/statuses/update.json',
      $cb->call('_getEndpoint', ['statuses/update', 'statuses/update']),
      'statuses/update'
    );
    $this->assertEquals(
      'https://api.twitter.com/oauth/authenticate',
      $cb->call('_getEndpoint', ['oauth/authenticate', 'oauth/authenticate']),
      'oauth/authenticate'
    );
    $this->assertEquals(
      'https://api.twitter.com/oauth2/token',
      $cb->call('_getEndpoint', ['oauth2/token', 'oauth2/token']),
      'oauth2/token'
    );
    $this->assertEquals(
      'https://upload.twitter.com/1.1/media/upload.json',
      $cb->call('_getEndpoint', ['media/upload', 'media/upload']),
      'media/upload'
    );
    $this->assertEquals(
      'https://stream.twitter.com/1.1/statuses/filter.json',
      $cb->call('_getEndpoint', ['statuses/filter', 'statuses/filter']),
      'statuses/filter'
    );
    $this->assertEquals(
      'https://sitestream.twitter.com/1.1/site.json',
      $cb->call('_getEndpoint', ['site', 'site']),
      'site'
    );
    $this->assertEquals(
      'https://userstream.twitter.com/1.1/user.json',
      $cb->call('_getEndpoint', ['user', 'user']),
      'user'
    );
    $this->assertEquals(
      'https://ton.twitter.com/1.1/ton/bucket/ta_partner',
      $cb->call('_getEndpoint', ['ton/bucket/ta_partner', 'ton/bucket/:bucket']),
      'ton/bucket/:bucket'
    );
    $this->assertEquals(
      'https://ads-api.twitter.com/0/accounts/1234/campaigns',
      $cb->call(
        '_getEndpoint',
        ['ads/accounts/1234/campaigns', 'ads/accounts/:account_id/campaigns']
      ),
      'ads/accounts/:account_id/campaigns'
    );
    $this->assertEquals(
      'https://ads-api-sandbox.twitter.com/0/accounts/1234/campaigns',
      $cb->call(
        '_getEndpoint',
        ['ads/sandbox/accounts/1234/campaigns', 'ads/sandbox/accounts/:account_id/campaigns']
      ),
      'ads/sandbox/accounts/:account_id/campaigns'
    );
  }
}
