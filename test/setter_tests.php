<?php

namespace Codebird;
require_once ('test/codebirdt.php');

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
 * Setter function tests
 *
 * @package codebird-test
 */
class Setter_Test extends \PHPUnit\Framework\TestCase
{
  /**
   * Tests setConsumerKey
   */
  public function testSetConsumerKey()
  {
    $cb = new CodebirdT();
    $cb->setConsumerKey('123', '456');
    $this->assertEquals('123', $cb->getStatic('_consumer_key'));
    $this->assertEquals('456', $cb->getStatic('_consumer_secret'));
  }

  /**
   * Tests setBearerToken
   */
  public function testSetBearerToken()
  {
    $cb = new CodebirdT();
    $cb->setBearerToken('789');
    $this->assertEquals('789', $cb->getStatic('_bearer_token'));
  }

  /**
   * Tests getVersion
   */
  public function testGetVersion()
  {
    $cb = new CodebirdT();
    $version = $cb->getVersion();
    $this->assertEquals($version, $cb->getStatic('_version'));
    $this->assertRegexp('/^[1-9]\d*\.\d+\.\d+(-([a-z]+\.[1-9]\d*|dev))?$/', $version);
  }

  /**
   * Tests setToken
   */
  public function testSetToken()
  {
    $cb = new CodebirdT();
    $cb->setToken('123', '456');
    $this->assertEquals('123', $cb->get('_oauth_token'));
    $this->assertEquals('456', $cb->get('_oauth_token_secret'));
  }

  /**
   * Tests logout
   */
  public function testLogout()
  {
    $cb = new CodebirdT();
    $cb->setToken('123', '456');
    $cb->logout();
    $this->assertNull($cb->get('_oauth_token'));
    $this->assertNull($cb->get('_oauth_token_secret'));
  }

  /**
   * Tests setUseCurl
   */
  public function testSetUseCurl()
  {
    $cb = new CodebirdT();
    $cb->setUseCurl(true);
    $this->assertTrue($cb->get('_use_curl'));
    $cb->setUseCurl(false);
    $this->assertFalse($cb->get('_use_curl'));
    $cb->setUseCurl('123');
    $this->assertTrue($cb->get('_use_curl'));
  }

  /**
   * Tests setTimeout
   */
  public function testSetTimeout()
  {
    $cb = new CodebirdT();
    $cb->setTimeout(123);
    $this->assertEquals(123, $cb->get('_timeouts')['request']);
    $cb->setTimeout(0);
    $this->assertEquals(0, $cb->get('_timeouts')['request']);
    $cb->setTimeout(-123);
    $this->assertEquals(0, $cb->get('_timeouts')['request']);
  }

  /**
   * Tests setConnectionTimeout
   */
  public function testSetConnectionTimeout()
  {
    $cb = new CodebirdT();
    $cb->setConnectionTimeout(123);
    $this->assertEquals(123, $cb->get('_timeouts')['connect']);
    $cb->setConnectionTimeout(0);
    $this->assertEquals(0, $cb->get('_timeouts')['connect']);
    $cb->setConnectionTimeout(-123);
    $this->assertEquals(0, $cb->get('_timeouts')['connect']);
  }

  /**
   * Tests setConnectionTimeout
   */
  public function testSetRemoteDownloadTimeout()
  {
    $cb = new CodebirdT();
    $cb->setRemoteDownloadTimeout(123);
    $this->assertEquals(123, $cb->get('_timeouts')['remote']);
    $cb->setRemoteDownloadTimeout(0);
    $this->assertEquals(0, $cb->get('_timeouts')['remote']);
    $cb->setRemoteDownloadTimeout(-123);
    $this->assertEquals(0, $cb->get('_timeouts')['remote']);
  }

  /**
   * Tests setReturnFormat
   */
  public function testSetReturnFormat()
  {
    $cb = new CodebirdT();
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_JSON);
    $this->assertEquals($cb->get('_return_format'), CODEBIRD_RETURNFORMAT_JSON);
  }

  /**
   * Tests setProxy
   */
  public function testSetProxy()
  {
    $cb = new CodebirdT();
    $cb->setProxy('127.0.0.1', '8888');
    $this->assertEquals('127.0.0.1', $cb->get('_proxy')['host']);
    $this->assertEquals('8888', $cb->get('_proxy')['port']);
    $this->assertEquals(CURLPROXY_HTTP, $cb->get('_proxy')['type']);

    $cb->setProxy('127.0.0.1', '8888', CURLPROXY_SOCKS5);
    $this->assertEquals('127.0.0.1', $cb->get('_proxy')['host']);
    $this->assertEquals('8888', $cb->get('_proxy')['port']);
    $this->assertEquals(CURLPROXY_SOCKS5, $cb->get('_proxy')['type']);
  }

  /**
   * Tests setProxy
    * @expectedException \Exception
    * @expectedExceptionMessage Invalid proxy type specified.
   */
  public function testSetProxy2()
  {
    $cb = new CodebirdT();
    $cb->setProxy('127.0.0.1', '8888', 1);
  }

  /**
   * Tests setProxyAuthentication
   */
  public function testSetProxyAuthentication()
  {
    $cb = new CodebirdT();
    $cb->setProxyAuthentication('ABCDEF');
    $this->assertEquals('ABCDEF', $cb->get('_proxy')['authentication']);
  }

  /**
   * Tests setStreamingCallback
   */
  public function testSetStreamingCallback1()
  {
    $callback = ['\Codebird\CodebirdT', 'streamingCallbackTest'];
    $cb = new CodebirdT();
    $cb->setStreamingCallback($callback);
    $this->assertSame(
      array_diff($callback, $cb->get('_streaming_callback')),
      array_diff($cb->get('_streaming_callback'), $callback)
    );
  }

  /**
    * Tests setStreamingCallback
    * @expectedException \Exception
    * @expectedExceptionMessage This is not a proper callback.
    */
  public function testSetStreamingCallback2()
  {
    $cb = new CodebirdT();
    $cb->setStreamingCallback(['\Codebird\CodebirdTX', 'somewhere']);
  }

  /**
    * Tests getApiMethods
    */
  public function testGetApiMethods()
  {
    $cb = new CodebirdT();
    $methods = $cb->getApiMethods();
    $this->assertArrayHasKey('GET', $cb->getStatic('_api_methods'));
    $this->assertArrayHasKey('POST', $cb->getStatic('_api_methods'));
    $this->assertArrayHasKey('PUT', $cb->getStatic('_api_methods'));
    $this->assertArrayHasKey('DELETE', $cb->getStatic('_api_methods'));
    $this->assertEquals($methods, $cb->getStatic('_api_methods'));
  }

  /**
    * Tests hasProxy
    */
  public function testHasProxy()
  {
    $cb = new CodebirdT();
    $this->assertFalse($cb->call('_hasProxy'));
    $cb->setProxy('127.0.0.1', '8888');
    $this->assertTrue($cb->call('_hasProxy'));
  }

  /**
    * Tests getProxyHost
    */
  public function testGetProxyHost()
  {
    $cb = new CodebirdT();
    $this->assertNull($cb->call('_getProxyHost'));
    $cb->setProxy('127.0.0.1', '8888');
    $this->assertEquals('127.0.0.1', $cb->call('_getProxyHost'));
  }

  /**
    * Tests getProxyPort
    */
  public function testGetProxyPort()
  {
    $cb = new CodebirdT();
    $this->assertNull($cb->call('_getProxyPort'));
    $cb->setProxy('127.0.0.1', '8888');
    $this->assertEquals('8888', $cb->call('_getProxyPort'));
  }
}
