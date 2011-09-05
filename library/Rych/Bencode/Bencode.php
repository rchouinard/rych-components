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
namespace Rych\Bencode;

/**
 * @see        Rych\Bencode\Decoder
 */
require_once 'Rych/Bencode/Decoder.php';

/**
 * @see        Rych\Bencode\Encoder
 */
require_once 'Rych/Bencode/Encoder.php';

/**
 * @uses       Rych\Bencode\Decoder
 * @uses       Rych\Bencode\Encoder
 * @category   Rych
 * @package    Rych_Bencode
 * @author     Ryan Chouinard <rchouinard@gmail.com>
 * @license    New BSD License
 */
class Bencode
{

    /**
     * How objects should be encoded.
     */
    const TYPE_ARRAY    = 0;
    const TYPE_OBJECT   = 1;

    /**
     * Proxy method to {@link Rych\Bencode\Decoder::decode()}.
     *
     * @uses Rych\Bencode\Decoder::decode()
     * @param string $encodedValue Bencode encoded string.
     * @param string $decodeType Optional; flag indicating how to decode objects. See {@link Rych\Bencode\Decoder::decode()} for details.
     * @return mixed Either an array or an object, as indicated with $decodeType.
     */
    public static function decode($encodedValue, $decodeType = self::TYPE_ARRAY)
    {
        return Decoder::decode($encodedValue, $decodeType);
    }

    /**
     * Proxy method to {@link Rych\Bencode\Encoder::encode()}.
     *
     * @uses Rych\Bencode\Encoder::encode()
     * @param mixed $valueToEncode
     * @return string
     */
    public static function encode($valueToEncode)
    {
        return Encoder::encode($valueToEncode);
    }

}