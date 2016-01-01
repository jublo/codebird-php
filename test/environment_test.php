<?php

namespace Codebird;

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
 * Environment tests
 *
 * @package codebird-test
 */
class Environment_Test extends \PHPUnit_Framework_TestCase
{
  /**
   * Tests PHP version
   */
  public function testPhpVersion()
  {
    $this->assertTrue(
      version_compare('5.5', phpversion(), '<='),
      'Codebird requires PHP 5.5 or above'
    );
  }

  /**
   * Tests OpenSSL module availability
   */
  public function testOpenssl()
  {
    $this->assertTrue(function_exists('openssl_open'));
  }
}
