<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych_Bencode
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2009 Ryan Chouinard ({@link http://ryanchouinard.com})
 * @license     New BSD License
 * @version     $Id$
 */

/**
 * @see         Rych_Bencode_Decoder
 */
require_once 'Rych/Bencode/Decoder.php';

/**
 * @see         Rych_Bencode_Encoder
 */
require_once 'Rych/Bencode/Encoder.php';

/**
 * @category    Rych
 * @package     Rych_Bencode
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 */
class Rych_Bencode
{

    /**
     * How objects should be encoded -- arrays or as StdClass.
     */
    const TYPE_ARRAY    = 'array';
    const TYPE_OBJECT   = 'object'; // NOT IMPLEMENTED

    /**
     * Proxy method to {@link Rych_Bencode_Decoder::decode()}.
     *
     * @uses    Rych_Bencode_Decoder::decode()
     * @param   string  $encodedValue
     * @param   string  $objectDecodeType
     * @return  mixed
     */
    public static function decode($encodedValue, $objectDecodeType = self::TYPE_ARRAY)
    {
        return Rych_Bencode_Decoder::decode($encodedValue, $objectDecodeType);
    }

    /**
     * Proxy method to {@link Rych_Bencode_Encoder::encode()}.
     *
     * @uses    Rych_Bencode_Encoder::encode()
     * @param   mixed   $valueToEncode
     * @return  string
     */
    public static function encode($valueToEncode)
    {
        return Rych_Bencode_Encoder::encode($valueToEncode);
    }

}