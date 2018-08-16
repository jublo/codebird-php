<?php

namespace Codebird;

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
 * Environment tests
 *
 * @package codebird-test
 */
class Environment_Test extends \PHPUnit\Framework\TestCase
{
  /**
   * Tests PHP version
   */
  public function testPhpVersion()
  {
    $this->assertTrue(
      version_compare('7.1', phpversion(), '<='),
      'Codebird requires PHP 7.1 or above'
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
