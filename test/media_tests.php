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
 * Media tests
 *
 * @package codebird-test
 */
class Media_Test extends \PHPUnit\Framework\TestCase
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
   * Tests _fetchRemoteFile
   */
  public function testFetchRemoteFile()
  {
    $cb = $this->getCB();
    $expected = $cb->call('_fetchRemoteFile', ['http://www.example.org/found.txt']);
    $this->assertEquals($expected, 'A test file.');
  }

  /**
   * Tests _fetchRemoteFile
    * @expectedException \Exception
    * @expectedExceptionMessage Downloading a remote media file failed.
   */
  public function testFetchRemoteFile1()
  {
    $cb = $this->getCB();
    $reply = $cb->call('_fetchRemoteFile', ['http://www.example.org/not-found.jpg']);
  }
}
