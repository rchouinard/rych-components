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
 *
 */
namespace Rych\Bencode;

/**
 * @category   Rych
 * @package    Rych_Bencode
 * @author     Ryan Chouinard <rchouinard@gmail.com>
 * @license    New BSD License
 */
class Encoder
{

    /**
     * @var mixed
     */
    protected $_value;

    /**
     * Constructor
     *
     * @param mixed $value The value to encode.
     * @return void
     */
    protected function __construct($value)
    {
        $this->_value = $value;
    }

    /**
     * Encode a value into a bencode encoded string.
     *
     * @param mixed $value The value to encode.
     * @return string
     */
    static public function encode($value)
    {
        $encoder = new self($value);
        return $encoder->_encodeValue();
    }

    /**
     * Encode the passed in value.
     *
     * @param mixed $value The value to encode.
     * @return string
     */
    protected function _encodeValue($value = null)
    {
        $value = is_null($value) ? $this->_value : $value;

        if (is_array($value) && (isset ($value[0]) || empty ($value))) {
            return $this->_encodeList($value);
        } else if (is_array($value)) {
            return $this->_encodeDict($value);
        } else if (is_numeric($value)) {
            $value = sprintf('%.0f', round($value, 0));
            return $this->_encodeInteger($value);
        } else {
            return $this->_encodeString($value);
        }
    }

    /**
     * Encodes an integer into a bencode encoded string.
     *
     * @param integer $value The value to encode.
     * @return string
     */
    protected function _encodeInteger($value = null)
    {
        $value = is_null($value) ? $this->_value : $value;
        return sprintf('i%se', $value);
    }

    /**
     * Encodes a string into a bencode encoded string.
     *
     * @param string $value The value to encode.
     * @return string
     */
    protected function _encodeString($value = null)
    {
        $value = is_null($value) ? $this->_value : $value;
        return sprintf('%d:%s', strlen($value), $value);
    }

    /**
     * Encodes an array into a bencode encoded string.
     *
     * @param array $value The value to encode.
     * @return string
     */
    protected function _encodeList($value = null)
    {
        $value = is_null($value) ? $this->_value : $value;

        $list = '';
        foreach ($value as $decodedValue) {
            $list .= $this->_encodeValue($decodedValue);
        }
        return "l{$list}e";
    }

    /**
     * Encodes an associative array into a bencode encoded string.
     *
     * @param array $value The value to encode.
     * @return string
     */
    protected function _encodeDict($value = null)
    {
        $value = is_null($value) ? $this->_value : $value;

        // Extract array from object
        if (is_object($value) && method_exists($value, 'toArray')) {
            $value = $value->toArray();
        } else if (is_object($value)) {
            $value = (array) $value;
        }

        ksort($value);
        $dict = '';
        foreach ($value as $key => $decodedValue) {
            $dict .= $this->_encodeString($key) . $this->_encodeValue($decodedValue);
        }

        return "d{$dict}e";
    }

}