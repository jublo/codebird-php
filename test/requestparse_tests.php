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
 * Request parsing tests
 *
 * @package codebird-test
 */
class Requestparse_Test extends \PHPUnit\Framework\TestCase
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
    $cb->setToken('234', '567');

    return $cb;
  }

  /**
   * Tests _parseApiParams
   */
  public function testParseApiParams()
  {
    $cb = $this->getCB();
    // empty list
    $this->assertEquals([], $cb->call('_parseApiParams', [[]]));
    // arrays
    $this->assertEquals(['test' => 1], $cb->call('_parseApiParams', [[['test' => 1]]]));
    $this->assertEquals(
      ['media[]' => '123'],
      $cb->call('_parseApiParams', [[['media[]' => 123]]])
    );
    // urlencoded strings
    $this->assertEquals(['testdata' => ''], $cb->call('_parseApiParams', [['testdata']]));
    $this->assertEquals(
      ['param1' => '12', 'param2' => 'ab'],
      $cb->call('_parseApiParams', [['param1=12&param2=ab']])
    );
    $this->assertEquals(
      ['media' => ['123', '456']],
      $cb->call('_parseApiParams', [['media[]=123&media[]=456']])
    );
  }

  /**
   * Tests _stringifyNullBoolParams
   */
  public function testStringifyNullBoolParams()
  {
    $cb = $this->getCB();
    $result = $cb->call(
        '_stringifyNullBoolParams',
        [['a' => 123, 'b' => null, 'c' => true, 'd' => false, 'e' => 'x']]
      );
    $this->assertEquals('123', $result['a']);
    $this->assertNull($result['b']);
    $this->assertEquals('true', $result['c']);
    $this->assertEquals('false', $result['d']);
  }

  /**
   * Tests _mapFnToApiMethod
   */
  public function testMapFnToApiMethod()
  {
    $cb = $this->getCB();
    $apiparams = [
      'test' => 1,
      'account_id' => '1234'
    ];
    $result = $cb->call(
        '_mapFnToApiMethod',
        'ads_accounts_ACCOUNT_ID_cards_appDownload',
        $apiparams
      );
    $this->assertEquals([
      'ads/accounts/1234/cards/app_download',
      'ads/accounts/:account_id/cards/app_download'
    ], $result);
    // check that inline parameter was removed from array
    $this->assertArrayNotHasKey('account_id', $apiparams);
  }

  /**
   * Tests _mapFnInsertSlashes
   */
  public function testMapFnInsertSlashes()
  {
    $cb = $this->getCB();
    $result = $cb->call(
        '_mapFnInsertSlashes',
        ['ads_accounts_ACCOUNT_ID_cards_appDownload']
      );
    $this->assertEquals(
      'ads/accounts/ACCOUNT/ID/cards/appDownload',
      $result
    );
  }

  /**
   * Tests _mapFnRestoreParamUnderscores
   */
  public function testMapFnRestoreParamUnderscores()
  {
    $cb = $this->getCB();
    $params_underscore = [
      'screen_name', 'place_id',
      'account_id', 'campaign_id', 'card_id', 'line_item_id',
      'tweet_id', 'web_event_tag_id'
    ];
    $params_slash = [];
    foreach ($params_underscore as $param) {
      $params_slash[] = str_replace('_', '/', $param);
    }
    for ($i = 0; $i < count($params_underscore); $i++) {
      $result = $cb->call(
          '_mapFnRestoreParamUnderscores',
          ['ads/accounts/' . strtoupper($params_slash[$i]) . '/cards/appDownload']
        );
      $this->assertEquals(
        'ads/accounts/' . strtoupper($params_underscore[$i]) . '/cards/appDownload',
        $result
      );
    }
  }

  /**
   * Tests _mapFnInlineParams
   */
  public function testMapFnInlineParams()
  {
    $cb = $this->getCB();
    // normal parameters
    $apiparams = [
      'test' => 1,
      'account_id' => '1234'
    ];
    $result = $cb->call(
        '_mapFnInlineParams',
        'ads/accounts/ACCOUNT_ID/cards/app_download',
        $apiparams
      );
    $this->assertEquals([
        'ads/accounts/1234/cards/app_download',
        'ads/accounts/:account_id/cards/app_download'
      ],
      $result
    );
    // check that inline parameter was removed from array
    $this->assertArrayNotHasKey('account_id', $apiparams);

    // special parameters (TON API)
    $apiparams = [
      'test'     => 1,
      'bucket'   => 'ta_partner',
      'file'     => 'test_Ab.mp4',
      'resumeId' => '56789'
    ];
    $result = $cb->call(
        '_mapFnInlineParams',
        'ton/bucket/BUCKET/FILE?resumable=true&resumeId=RESUMEID',
        $apiparams
      );
    $this->assertEquals([
        'ton/bucket/ta_partner/test_Ab.mp4?resumable=true&resumeId=56789',
        'ton/bucket/:bucket/:file?resumable=true&resumeId=:resumeId'
      ],
      $result
    );
    $this->assertArrayNotHasKey('bucket', $apiparams);
    $this->assertArrayNotHasKey('file', $apiparams);
    $this->assertArrayNotHasKey('resumeId', $apiparams);
    $this->assertEquals(['test' => 1], $apiparams);
  }

  /**
   * Tests _json_decode
   */
  public function testJsonDecode()
  {
    $json  = '{"id": 123456789123456789, "id_str": "123456789123456789"}';
    $array = [
      'id' => 123456789123456789,
      'id_str' => '123456789123456789'
    ];
    $object = (object) $array;

    $cb = $this->getCB();
    $result = $cb->call('_json_decode', [$json]);
    $this->assertEquals($object, $result);
    $result = $cb->call('_json_decode', [$json, true]);
    $this->assertEquals($array, $result);
  }
}
