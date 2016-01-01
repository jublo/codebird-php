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
 * Constant tests
 *
 * @package codebird-test
 */
class Constant_Test extends \PHPUnit_Framework_TestCase
{
  /**
   * Tests if constants defined
   */
  public function testConstants()
  {
    $constants = [
      'CODEBIRD_RETURNFORMAT_OBJECT',
      'CODEBIRD_RETURNFORMAT_ARRAY',
      'CODEBIRD_RETURNFORMAT_JSON'
    ];
    foreach ($constants as $constant) {
      $this->assertTrue(
        defined($constant),
        $constant
      );
    }
  }
}
