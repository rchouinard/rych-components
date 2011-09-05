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
 * @see Rych\Bencode\Exception\InvalidArgumentException
 */
require_once 'Rych/Bencode/Exception/InvalidArumentException.php';

/**
 * @see Rych\Bencode\Exception\RuntimeException
 */
require_once 'Rych/Bencode/Exception/RuntimeException.php';

use Rych\Bencode\Exception\RuntimeException,
    Rych\Bencode\Exception\InvalidArgumentException;

/**
 * @category   Rych
 * @package    Rych_Bencode
 * @author     Ryan Chouinard <rchouinard@gmail.com>
 * @license    New BSD License
 */
class Decoder
{

    /**
     * Flag determining whether dictionaries should be decoded as arrays or objects.
     *
     * @var string
     */
    protected $_decodeType;

    /**
     * Current offset, which will be advanced as the source is decoded.
     *
     * @var integer
     */
    protected $_offset = 0;

    /**
     * Contains the original encoded string.
     *
     * @var string
     */
    protected $_source;

    /**
     * Cache holding the length of the encoded string.
     *
     * @var integer
     */
    protected $_sourceLength;

    /**
     * Constructor
     *
     * @param string $source The bencode encoded string.
     * @param string $decodeType How decoded dictionaries should be returned.
     * @return void
     */
    protected function __construct($source, $decodeType)
    {
        $this->_source = $source;
        $this->_sourceLength = strlen($this->_source);

        if ($decodeType != Bencode::TYPE_ARRAY && $decodeType != Bencode::TYPE_OBJECT) {
            throw new InvalidArgumentException("Unknown decode type '{$decodeType}', please use one of the constants Bencode::TYPE_*");
        }

        $this->_decodeType = $decodeType;
    }

    /**
     * Decodes a bencode encoded string.
     *
     * @param string $source The bencode encoded string.
     * @param string $decodeType How decoded dictionaries should be returned.
     * @return array
     */
    static public function decode($source, $decodeType = Bencode::TYPE_ARRAY)
    {
        $decoder = new self($source, $decodeType);
        return $decoder->_decodeValue();
    }

    /**
     * Decode the next bencode token.
     *
     * @return mixed
     */
    protected function _decodeValue()
    {
        $currentChar = $this->_getChar();
        switch ($currentChar) {

            // Integer
            case 'i':
                return $this->_decodeInteger();
                break;

            // List
            case 'l':
                return $this->_decodeList();
                break;

            // Dictionary
            case 'd':
                return $this->_decodeDict();
                break;

            // String
            default:
                if ($this->_isDigit($currentChar)) {
                    return $this->_decodeString();
                }

        }

        throw new RuntimeException("Unknown entity '{$currentChar}' found at offset {$this->_offset}");
    }

    /**
     * Decode a bencode encoded integer.
     *
     * From the bencode spec:
     *   Integers are encoded as follows: i<integer encoded in base ten ASCII>e
     *     The initial i and trailing e are beginning and ending delimiters.
     *     You can have negative numbers such as i-3e. Only the significant
     *     digits should be used, one cannot pad the Integer with zeroes, such
     *     as i04e. However, i0e is valid.
     *
     *   Example: i3e represents the integer "3"
     *
     * @throwns Rych\Bencode\Exception\RuntimeException
     * @return integer
     */
    protected function _decodeInteger()
    {
        $tokenOffset = $this->_offset;

        $offsetOfE = strpos($this->_source, 'e', $tokenOffset);
        if (false === $offsetOfE) {
            throw new RuntimeException("Unterminated integer entity at offset {$tokenOffset}");
        }

        $value = substr($this->_source, $tokenOffset + 1, ($offsetOfE - 1) - $tokenOffset);

        if (!ctype_digit(ltrim($value, '-')) || (string) ($value + 0) != $value) {
            throw new RuntimeException("Invalid integer entity at offset {$tokenOffset}");
        }

        $this->_offset = $offsetOfE + 1;

        // The +0 auto-casts the value to either an integer or a float (in cases
        // where an integer would overrun the max limits of integer types)
        return $value + 0;
    }

    /**
     * Decode a bencode encoded list.
     *
     * From the bencode spec:
     *   Lists are encoded as follows: l<bencoded values>e
     *     The initial l and trailing e are beginning and ending delimiters.
     *     Lists may contain any bencoded type, including integers, strings,
     *     dictionaries, and even lists within other lists.
     *
     *   Example: l4:spam4:eggse represents the list of two strings:
     *   [ "spam", "eggs" ]
     *
     * @throwns Rych\Bencode\Exception\RuntimeException
     * @return array
     */
    protected function _decodeList()
    {
        $tokenOffset = $this->_offset++;

        $value = array ();
        $terminated = false;

        do {
            if ('e' == $this->_getChar()) {
                $terminated = true;
                break;
            }

            $value[] = $this->_decodeValue();
        } while (false !== $this->_getChar());

        // Check if we ran out of characters, or found the "e"
        if (!$terminated && false === $this->_getChar()) {
            throw new RuntimeException("Unterminated list entity at offset {$tokenOffset}");
        }

        ++$this->_offset;
        return $value;
    }

    /**
     * Decode a bencode encoded dictionary.
     *
     * From the bencode spec:
     *   Dictionaries are encoded as follows:
     *   d<bencoded string><bencoded element>e
     *     The initial d and trailing e are the beginning and ending delimiters.
     *     Note that the keys must be bencoded strings. The values may be any
     *     bencoded type, including integers, strings, lists, and other
     *     dictionaries. Keys must be strings and appear in sorted order
     *     (sorted as raw strings, not alphanumerics). The strings should be
     *     compared using a binary comparison, not a culture-specific "natural"
     *     comparison.
     *
     *   Example: d3:cow3:moo4:spam4:eggse represents the dictionary
     *     { "cow" => "moo", "spam" => "eggs" }
     *   Example: d4:spaml1:a1:bee' represents the dictionary
     *     { "spam" => [ "a", "b" ] }
     *   Example: d9:publisher3:bob17:publisher-webpage15:www.example.com18:publisher.location4:homee
     *     represents { "publisher" => "bob", "publisher-webpage" => "www.example.com", "publisher.location" => "home" }
     *
     * @throwns Rych\Bencode\Exception\RuntimeException
     * @return array|stdClass
     */
    protected function _decodeDict()
    {
        $tokenOffset = $this->_offset++;

        $value = array ();
        $terminated = false;

        do {
            if ('e' == $this->_getChar()) {
                $terminated = true;
                break;
            }

            try {
                $keyOffset = $this->_offset;
                $key = $this->_decodeString();
                if (array_key_exists($key, $value)) {
                    throw new RuntimeException("Duplicate dictionary key at offset {$keyOffset}");
                }
            } catch (RuntimeException $e) {
                throw new RuntimeException("Invalid dictionary key at offset {$keyOffset}", null, $e);
            }

            $value[$key] = $this->_decodeValue();

        } while (false !== $this->_getChar());

        // Check if we ran out of characters, or found the "e"
        if (!$terminated && false === $this->_getChar()) {
            throw new RuntimeException("Unterminated dictionary entity at offset {$tokenOffset}");
        }

        ++$this->_offset;
        return ($this->_decodeType) ? (object) $value : $value;
    }

    /**
     * Decode a bencode encoded string.
     *
     * From the bencode spec:
     *   Bencoded strings are encoded as follows:
     *   <string length encoded in base ten ASCII>:<string data>, or key:value
     *     Note that there is no constant beginning delimiter, and no ending
     *     delimiter.
     *
     *   Example: 4:spam represents the string "spam"
     *
     * @throwns Rych\Bencode\Exception\RuntimeException
     * @return string
     */
    protected function _decodeString()
    {
        $tokenOffset = $this->_offset;

        // Find the colon
        // *points to belly* -- FOUND IT! :-D
        $offsetOfColon = strpos($this->_source, ':', $tokenOffset);
        if (false === $offsetOfColon) {
            throw new RuntimeException("Unterminated string entity at offset {$tokenOffset}");
        }

        $length = substr($this->_source, $tokenOffset, $offsetOfColon);
        if (!ctype_digit($length)) {
            throw new RuntimeException("Invalid string entity at offset {$tokenOffset}");
        }

        $value = substr($this->_source, $offsetOfColon + 1, $length);
        if (strlen($value) != $length) {
            throw new RuntimeException("Unexpected end of string entity at offset {$tokenOffset}");
        }

        $this->_offset = $offsetOfColon + $length + 1;

        return $value;
    }

    /**
     * Retrieve the character at the current offset in $_source.
     *
     * @param integer $offset Numeric offset to use, if not the global offset
     * @return string
     */
    protected function _getChar($offset = null)
    {
        if (null === $offset) {
            $offset = $this->_offset;
        }

        if (empty ($this->_source) || $this->_offset >= $this->_sourceLength) {
            return false;
        }

        return $this->_source{$offset};
    }

    /**
     * @return boolean
     */
    protected function _isDigit($char)
    {
        return ctype_digit($char);
    }

}