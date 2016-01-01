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
 * cURL tests
 *
 * @package codebird-test
 */
class Curl_Test extends \PHPUnit_Framework_TestCase
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
   * Tests _getCurlInitialization
   */
  public function testGetCurlInitialization()
  {
    $cb = $this->getCB();
    $id = $cb->call('_getCurlInitialization', ['https://test']);
    $this->assertEquals(
      [
        'url' => 'https://test',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 0,
        CURLOPT_HEADER => 1,
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CAINFO => substr(__DIR__, 0, -strlen(basename(__DIR__))) . 'src/cacert.pem',
        CURLOPT_USERAGENT => 'codebird-php/' . $cb->getStatic('_version')
          . ' +https://github.com/jublonet/codebird-php'
      ],
      $cb->get('_requests')[$id]
    );
  }
}
