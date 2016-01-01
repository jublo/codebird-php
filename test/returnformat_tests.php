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
 * Return format tests
 *
 * @package codebird-test
 */
class Returnformat_Test extends \PHPUnit_Framework_TestCase
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
   * Tests array return format
   */
  public function testArrayFormat()
  {
    $cb = $this->getCB();
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
    $reply = $cb->users_show(['screen_name' => 'TwitterAPI']);
    $this->assertTrue(is_array($reply));
  }

  /**
   * Tests object return format
   */
  public function testObjectFormat()
  {
    $cb = $this->getCB();
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_OBJECT);
    $reply = $cb->users_show(['screen_name' => 'TwitterAPI']);
    $this->assertInstanceOf('stdClass', $reply);
  }

  /**
   * Tests JSON return format
   */
  public function testJsonFormat()
  {
    $cb = $this->getCB();
    $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_JSON);
    $reply = $cb->users_show(['screen_name' => 'TwitterAPI']);
    $data = json_decode($reply);
    $this->assertNotFalse($data);
  }
}
