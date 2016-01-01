<?php

namespace Codebird;
require_once ('src/codebird.php');

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
 * Codebird testing class
 *
 * @package codebird-test
 */
class CodebirdT extends Codebird
{
  /**
   * Returns properties
   *
   * @param string $property The property to get
   *
   * @return mixed Property
   */
  public function get($property)
  {
    if (property_exists($this, $property)) {
      return $this->$property;
    }
    throw new \Exception('Property ' . $property . ' is not defined.');
  }

  /**
   * Returns static properties
   *
   * @param string $property The property to get
   *
   * @return mixed Property
   */
  public function getStatic($property)
  {
    if (property_exists($this, $property)) {
      return static::$$property;
    }
    throw new \Exception('Static property ' . $property . ' is not defined.');
  }

  /**
   * Calls methods
   *
   * @param string $property The property to get
   *
   * @return mixed Property
   */
  public function call($method, $params = [], &$params2 = null)
  {
    $methods_byref = [
      '_mapFnToApiMethod',
      '_mapFnInlineParams',
      '_detectMethod'
    ];
    if (in_array($method, $methods_byref)) {
      return $this->$method($params, $params2);
    }
    if (method_exists($this, $method)) {
      return call_user_func_array([$this, $method], $params);
    }
    throw new \Exception('Method ' . $method . ' is not defined.');
  }

  /**
   * Calls methods
   *
   * @param string $property The property to get
   *
   * @return mixed Property
   */
  public function callStatic($method, $params = [])
  {
    if (function_exists([self, $method])) {
      return call_user_func_array([self, $method], $params);
    }
    throw new \Exception('Static method ' . $method . ' is not defined.');
  }

  /**
   * Streaming callback test
   */
  public static function streamingCallbackTest()
  {
  }
}
