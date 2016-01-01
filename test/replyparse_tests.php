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
 * Reply parsing tests
 *
 * @package codebird-test
 */
class Replyparse_Test extends \PHPUnit_Framework_TestCase
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
   * Tests _getHttpStatusFromHeaders
   */
  public function testGetHttpStatusFromHeaders()
  {
    $cb = $this->getCB();
    $this->assertEquals(
      '500',
      $cb->call('_getHttpStatusFromHeaders', [['']])
    );
    $this->assertEquals(
      '200',
      $cb->call('_getHttpStatusFromHeaders', [
        ['HTTP/1.1 200 OK'],
        ['X-Some-Data: test']
      ])
    );
    $this->assertEquals(
      '404',
      $cb->call('_getHttpStatusFromHeaders', [
        ['HTTP/1.1 404 Not Found'],
        ['X-Some-Data: test']
      ])
    );
  }

  /**
   * Tests _parseBearerReply
   */
  public function testParseBearerReply()
  {
    $cb = $this->getCB();
    $cb->setBearerToken(null);
    // get raw reply from mock collection
    $reply = $cb->getStatic('_mock_replies')['POST https://api.twitter.com/oauth2/token'];
    // check that bearer token is not yet set
    $this->assertNull($cb->getStatic('_bearer_token'));
    // parse it as object
    $result = $cb->call(
      '_parseBearerReply',
      [
        $reply['reply'],
        $reply['httpstatus']
      ]
    );
    $expected = new \stdClass;
    $expected->token_type = 'bearer';
    $expected->access_token = 'VqiO0n2HrKE';
    $expected->httpstatus = 200;
    $expected->rate = null;
    $this->assertEquals($expected, $result);
    // check that bearer token was actually set
    $this->assertNotNull($cb->getStatic('_bearer_token'));

    // array
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
    $cb->setBearerToken(null);
    $result = $cb->call(
      '_parseBearerReply',
      [
        $reply['reply'],
        $reply['httpstatus']
      ]
    );
    $expected = [
      'token_type'   => 'bearer',
      'access_token' => 'VqiO0n2HrKE',
      'httpstatus'   => 200,
      'rate'         => null
    ];
    $this->assertEquals($expected, $result);
    // check that bearer token was actually set
    $this->assertNotNull($cb->getStatic('_bearer_token'));

    // JSON
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_JSON);
    $cb->setBearerToken(null);
    $result = $cb->call(
      '_parseBearerReply',
      [
        $reply['reply'],
        $reply['httpstatus']
      ]
    );
    $expected = '{"token_type":"bearer","access_token":"VqiO0n2HrKE"}';
    $this->assertEquals($expected, $result);
    // check that bearer token was actually set
    $this->assertNotNull($cb->getStatic('_bearer_token'));
  }

  /**
   * Tests _getRateLimitInfo
   */
  public function testGetRateLimitInfo()
  {
    $cb = $this->getCB();
    $headers = [
      'content-length' => 68,
      'content-type'   => 'application/json;charset=utf-8',
      'date'           => 'Sun, 06 Dec 2015 14:43:28 GMT'
    ];
    $this->assertNull($cb->call('_getRateLimitInfo', [$headers]));

    // set rate-limit headers
    $headers['x-rate-limit-limit'] = 180;
    $headers['x-rate-limit-remaining'] = 123;
    $headers['x-rate-limit-reset'] = time() + 234;
    $rate = $cb->call('_getRateLimitInfo', [$headers]);
    $expected = new \stdClass;
    $expected->limit = $headers['x-rate-limit-limit'];
    $expected->remaining = $headers['x-rate-limit-remaining'];
    $expected->reset = $headers['x-rate-limit-reset'];
    $this->assertEquals($expected, $rate);

    // array
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
    $rate = $cb->call('_getRateLimitInfo', [$headers]);
    $expected = [
      'limit'     => $headers['x-rate-limit-limit'],
      'remaining' => $headers['x-rate-limit-remaining'],
      'reset'     => $headers['x-rate-limit-reset']
    ];
    $this->assertEquals($expected, $rate);
  }

  /**
   * Tests _validateSslCertificate
   */
  public function testValidateSslCertificate1()
  {
    $cb = $this->getCB();
    $cb->call('_validateSslCertificate', [0]);
  }

  /**
   * Tests _validateSslCertificate
   * @expectedException \Exception
   * @expectedExceptionMessage Error 58 while validating the Twitter API certificate.
   */
  public function testValidateSslCertificate2()
  {
    $cb = $this->getCB();
    $cb->call('_validateSslCertificate', [CURLE_SSL_CERTPROBLEM]);
  }

  /**
   * Tests _appendHttpStatusAndRate
   */
  public function testAppendHttpStatusAndRate()
  {
    $cb = $this->getCB();

    // object
    $reply = new \stdClass;
    $reply->somedata = 123;
    $reply->moredata = '456';
    $rate = new \stdClass;
    $rate->field1 = 180;
    $rate->field2 = 177;
    $expected = new \stdClass;
    $expected->somedata = 123;
    $expected->moredata = '456';
    $expected->httpstatus = 409;
    $expected->rate = new \stdClass;
    $expected->rate->field1 = 180;
    $expected->rate->field2 = 177;
    $this->assertEquals(
      $expected,
      $cb->call('_appendHttpStatusAndRate', [$reply, 409, $rate])
    );

    // array
    $reply            = (array) $reply;
    $rate             = (array) $rate;
    $expected         = (array) $expected;
    $expected['rate'] = (array) $expected['rate'];
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
    $this->assertEquals(
      $expected,
      $cb->call('_appendHttpStatusAndRate', [$reply, 409, $rate])
    );

    // JSON
    $reply    = '{"somedata":123,"moredata":"456"}';
    $expected = '{"somedata":123,"moredata":"456","httpstatus":409,'
      . '"rate":{"field1":180,"field2":177}}';
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_JSON);
    $this->assertEquals(
      $expected,
      $cb->call('_appendHttpStatusAndRate', [$reply, 409, $rate])
    );
  }

  /**
   * Tests _parseApiHeaders
   */
  public function testParseApiHeaders()
  {
    $cb   = $this->getCB();
    $data = $cb->getStatic('_mock_replies')['default']['reply'];
    list($headers, $reply) = $cb->call('_parseApiHeaders', [$data]);
    $expected_headers = [
      'HTTP/1.1 404 Not Found' => '',
      'content-length' => '68',
      'content-type' => 'application/json;charset=utf-8',
      'date' => 'Sun, 06 Dec 2015 14:43:28 GMT',
      'server' => 'tsa_b',
      'set-cookie' => 'guest_id=v1%3A144941300885288055; Domain=.twitter.com'
        . '; Path=/; Expires=Tue, 05-Dec-2017 14:43:28 UTC',
      'strict-transport-security' => 'max-age=631138519',
      'x-connection-hash' => '12218aef9e9757609afb08e661fa3b9b',
      'x-response-time' => '2'
    ];
    $expected_reply = '{"errors":[{"message":"Sorry, that page does not exist","code":34}]}';
    $this->assertEquals($expected_headers, $headers);
    $this->assertEquals($expected_reply, $reply);

    // proxy
    $data = $cb->getStatic('_mock_replies')['proxy1']['reply'];
    list($headers, $reply) = $cb->call('_parseApiHeaders', [$data]);
    $this->assertEquals($expected_headers, $headers);
    $this->assertEquals($expected_reply, $reply);
  }

  /**
   * Tests _parseApiReplyPrefillHeaders
   */
  public function testParseApiReplyPrefillHeaders()
  {
    $cb = $this->getCB();
    $headers = [
      'X-TON-Min-Chunk-Size' => '12345',
      'X-TON-Max-Chunk-Size' => '23456',
      'Range'                => 'bytes 0-1234567/2345678'
    ];

    // non-empty reply: no touching
    $this->assertEquals(
      '123',
      $cb->call('_parseApiReplyPrefillHeaders', [$headers, '123'])
    );

    // no location header: no touching
    $this->assertEquals(
      '',
      $cb->call('_parseApiReplyPrefillHeaders', [$headers, ''])
    );

    $headers['Location'] = 'https://twitter.com';
    $this->assertEquals(
      '{"Location":"https:\/\/twitter.com","X-TON-Min-Chunk-Size":"12345",'
      . '"X-TON-Max-Chunk-Size":"23456","Range":"bytes 0-1234567\/2345678"}',
      $cb->call('_parseApiReplyPrefillHeaders', [$headers, ''])
    );
  }

  /**
   * Tests _parseApiReply
   */
  public function testParseApiReply1()
  {
    $cb = $this->getCB();

    // object
    $this->assertEquals(new \stdClass, $cb->call('_parseApiReply', ['[]']));

    // array
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
    $this->assertEquals([], $cb->call('_parseApiReply', ['[]']));

    // JSON
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_JSON);
    $this->assertEquals('{}', $cb->call('_parseApiReply', ['[]']));
  }

  /**
   * Tests _parseApiReply
   */
  public function testParseApiReply2()
  {
    $cb = $this->getCB();
    $reply = '{"id_str":"6253282","profile_location":null,'
      . '"status":{"created_at":"Tue Nov 24 08:56:07 +0000 2015","id":669077021138493440}}';

    // object
    $expected = new \stdClass;
    $expected->id_str = '6253282';
    $expected->profile_location = null;
    $expected->status = new \stdClass;
    $expected->status->created_at = 'Tue Nov 24 08:56:07 +0000 2015';
    $expected->status->id = 669077021138493440;
    $result = $cb->call('_parseApiReply', [$reply]);
    $this->assertEquals($expected, $result);
    $this->assertSame($expected->status->id, $result->status->id);

    // array
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
    $expected = (array) $expected;
    $expected['status'] = (array) $expected['status'];
    $this->assertEquals($expected, $cb->call('_parseApiReply', [$reply]));

    // JSON
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_JSON);
    $this->assertEquals($reply, $cb->call('_parseApiReply', [$reply]));

    // query-string format
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
    $this->assertEquals(
      [
        'oauth_token' => 'ABC',
        'oauth_token_secret' => 'def',
        'oauth_callback_confirmed' => 'true'
      ],
      $cb->call(
        '_parseApiReply',
        ['oauth_token=ABC&oauth_token_secret=def&oauth_callback_confirmed=true']
      )
    );

    // message
    $this->assertEquals(
      ['message' => 'This is just a message.'],
      $cb->call('_parseApiReply', ['This is just a message.'])
    );
  }
}
