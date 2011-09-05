<?php
/**
 * Ryan Chouinard's Components
 *
 * @category   Rych
 * @package    Rych_Bencode
 * @author     Ryan Chouinard <rchouinard@gmail.com>
 * @copyright  Copyright (c) 2010 Ryan Chouinard
 * @license    New BSD License
 */

/**
 * @namespace
 */
namespace Rych\Bencode\Exception;

/**
 * @see Rych\Bencode\Exception
 */
require_once 'Rych/Bencode/Exception.php';

/**
 * @uses       RuntimeException
 * @uses       Rych\Bencode\Exception
 * @category   Rych
 * @package    Rych_Bencode
 * @author     Ryan Chouinard <rchouinard@gmail.com>
 * @license    New BSD License
 */
class RuntimeException extends \RuntimeException implements \Rych\Bencode\Exception
{
}