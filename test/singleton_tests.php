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
 * Singleton tests
 *
 * @package codebird-test
 */
class Singleton_Test extends \PHPUnit_Framework_TestCase
{
  /**
   * Tests getInstance
   */
  public function testGetInstance()
  {
    $cb = CodebirdT::getInstance();
    $this->assertInstanceOf('\Codebird\Codebird', $cb);
  }
}
