<?php

namespace Codebird;
require_once ('test/codebirdm.php');

/**
 * A Twitter library in PHP.
 *
 * @package   codebird-test
 * @author    Jublo Limited <support@jublo.net>
 * @copyright 2010-2018 Jublo Limited <support@jublo.net>
 * @license   https://opensource.org/licenses/GPL-3.0 GNU General Public License 3.0
 * @link      https://github.com/jublonet/codebird-php
 */

/**
 * OAuth tests
 *
 * @package codebird-test
 */
class Oauth_Test extends \PHPUnit\Framework\TestCase
{
  /**
   * Initialise Codebird class
   *
   * @return \Codebird\Codebird The Codebird class
   */
  protected function getCB()
  {
    Codebird::setConsumerKey('123', '456');
    $cb = new CodebirdM();

    return $cb;
  }

  /**
   * Tests oauth_authenticate
   */
  public function testOauthAuthenticate()
  {
    $cb = $this->getCB();
    $cb->setToken('123', '456');
    $this->assertEquals(
      'https://api.twitter.com/oauth/authenticate?oauth_token=123',
      $cb->oauth_authenticate()
    );
    $this->assertEquals(
      'https://api.twitter.com/oauth/authenticate?oauth_token=123&force_login=1',
      $cb->oauth_authenticate($force_login = true)
    );
    $this->assertEquals(
      'https://api.twitter.com/oauth/authenticate?'
      . 'oauth_token=123&force_login=1&screen_name=TwitterAPI',
      $cb->oauth_authenticate($force_login = true, $screen_name = 'TwitterAPI')
    );
    $this->assertEquals(
      'https://api.twitter.com/oauth/authenticate?'
      . 'oauth_token=123&screen_name=TwitterAPI',
      $cb->oauth_authenticate($force_login = false, $screen_name = 'TwitterAPI')
    );
  }

  /**
   * Tests oauth_authorize
   */
  public function testOauthAuthorize()
  {
    $cb = $this->getCB();
    $cb->setToken('123', '456');
    $this->assertEquals(
      'https://api.twitter.com/oauth/authorize?oauth_token=123',
      $cb->oauth_authorize()
    );
    $this->assertEquals(
      'https://api.twitter.com/oauth/authorize?oauth_token=123&force_login=1',
      $cb->oauth_authorize($force_login = true)
    );
    $this->assertEquals(
      'https://api.twitter.com/oauth/authorize?'
      . 'oauth_token=123&force_login=1&screen_name=TwitterAPI',
      $cb->oauth_authorize($force_login = true, $screen_name = 'TwitterAPI')
    );
    $this->assertEquals(
      'https://api.twitter.com/oauth/authorize?'
      . 'oauth_token=123&screen_name=TwitterAPI',
      $cb->oauth_authorize($force_login = false, $screen_name = 'TwitterAPI')
    );
  }

  /**
   * Tests oauth2_token
   */
  public function testOauth2Token()
  {
    $cb = $this->getCB();
    $expected = new \stdClass;
    $expected->token_type = 'bearer';
    $expected->access_token = 'VqiO0n2HrKE';
    $expected->httpstatus = '200';
    $expected->rate = null;
    $this->assertEquals($expected, $cb->oauth2_token());
  }

  /**
   * Tests _getBearerAuthorization
    * @expectedException \Exception
    * @expectedExceptionMessage To make an app-only auth API request, consumer key or bearer token must be set.
   */
  public function testGetBearerAuthorization1()
  {
    $cb = $this->getCB();
    Codebird::setConsumerKey(null, null);
    $cb->setBearerToken(null);
    $cb->call('_getBearerAuthorization', []);
  }

  /**
   * Tests _getBearerAuthorization
   */
  public function testGetBearerAuthorization2()
  {
    $cb = $this->getCB();
    $cb->setBearerToken('12345678');
    $this->assertEquals('Bearer 12345678', $cb->call('_getBearerAuthorization', []));

    // automatic fetching
    $cb->setBearerToken(null);
    $this->assertEquals('Bearer VqiO0n2HrKE', $cb->call('_getBearerAuthorization', []));
  }
}
