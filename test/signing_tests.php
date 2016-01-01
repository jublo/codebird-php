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
 * OAuth signing tests
 *
 * @package codebird-test
 */
class Signing_Test extends \PHPUnit_Framework_TestCase
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
   * Tests _url
   */
  public function testUrl()
  {
    $cb = $this->getCB();
    // string
    $this->assertEquals(
      'q%20%2B%20b%21%22%C2%A7%24%25%26%2F%28%29%3D%3F%23%2A13%2C3%C3%A4.',
      $cb->call('_url', ['q + b!"§$%&/()=?#*13,3ä.'])
    );
    // array
    $this->assertEquals(
      [
        'q%20%2B%20b%21%22%C2%A7%24%25%26%2F%28%29%3D%3F%23%2A13%2C3%C3%A4.',
        'test123'
      ],
      $cb->call('_url', [[
        'q + b!"§$%&/()=?#*13,3ä.',
        'test123'
      ]])
    );
  }

  /**
   * Tests _sha1
   */
  public function testSha1()
  {
    $cb = $this->getCB();
    $this->assertEquals(
      'ydAlpYjrGHU7psDQ9HPgHTcwEuw=',
      $cb->call('_sha1', ['q + b!"§$%&/()=?#*13,3ä.'])
    );

    // with access token secret
    $cb->setToken('678', '789');
    $this->assertEquals(
      'CtivZhAHiX49ZMUuHXtKabLAuo0=',
      $cb->call('_sha1', ['q + b!"§$%&/()=?#*13,3ä.'])
    );
  }

  /**
   * Tests _nonce
   */
  public function testNonce1()
  {
    $cb = $this->getCB();
    // default length
    $this->assertEquals(
      '4247c524',
      $cb->call('_nonce', [])
    );
    // custom length
    $this->assertEquals(
      '4247c5248da',
      $cb->call('_nonce', [11])
    );
  }

  /**
   * Tests _nonce
   * @expectedException \Exception
   * @expectedExceptionMessage Invalid nonce length.
   */
  public function testNonce2()
  {
    $cb = $this->getCB();
    // invalid length
    $cb->call('_nonce', [0]);
  }

  /**
   * Tests _getSignature
   */
  public function testGetSignature()
  {
    $cb = $this->getCB();
    $base_params = [
      'oauth_consumer_key' => '123',
      'oauth_nonce' => '12345678',
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_timestamp' => '1400000000',
      'oauth_token' => '567',
      'oauth_version' => '1.0',
      'q' => 'Test search.'
    ];
    $this->assertEquals(
      'ZON/m9bHvciPdtyK9BlokjeiW4M=',
      $cb->call('_getSignature', ['GET', 'search/tweets', $base_params])
    );
  }

  /**
   * Tests _sign
   * @expectedException \Exception
   * @expectedExceptionMessage To generate a signature, the consumer key must be set.
   */
  public function testSign1()
  {
    $cb = $this->getCB();
    $cb->setConsumerKey(null, null);
    $params = ['q' => 'Test search.'];
    $cb->call('_sign', ['GET', 'search/tweets', $params]);
  }

  /**
   * Tests _sign
   */
  public function testSign2()
  {
    $cb = $this->getCB();
    $params = ['q' => 'Test search.'];
    $this->assertEquals(
      'OAuth oauth_consumer_key="123", oauth_nonce="4247c524", oauth_signature'
      . '="lOLNd5l6cGB9kWACxWLNKJwSD%2FI%3D", oauth_signature_method="HMAC-SHA'
      . '1", oauth_timestamp="1412345678", oauth_version="1.0"',
      $cb->call('_sign', ['GET', 'search/tweets', $params])
    );

    // with oauth token
    $cb->setToken('678', '789');
    $this->assertEquals(
      'OAuth oauth_consumer_key="123", oauth_nonce="4247c524", oauth_signature'
      . '="XzegzFKEqs2PpUMym5T%2BwhEmTz4%3D", oauth_signature_method="HMAC-SHA'
      . '1", oauth_timestamp="1412345678", oauth_token="678", oauth_version="1.0"',
      $cb->call('_sign', ['GET', 'search/tweets', $params])
    );
  }
}
