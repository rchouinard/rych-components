<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych_Bencode
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2010 Ryan Chouinard ({@link http://ryanchouinard.com})
 * @license     New BSD License
 * @version     $Id$
 */

/**
 * @see Rych_Bencode
 */
require_once 'Rych/Bencode.php';

/**
 * @category    Rych
 * @package     Rych_Bencode
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 */
class Rych_Bencode_Decoder
{

    /**
     * Contains the original encoded string.
     *
     * @var     string
     */
    protected $_source;

    /**
     * Flag determining whether or not to return an array or an object.
     *
     * @var     string
     */
    protected $_decodeType;

    /**
     * Cache holding the length of the encoded string.
     *
     * @var     integer
     */
    protected $_sourceLength;

    /**
     * Current offset, which will be advanced as the source is decoded.
     *
     * @var     integer
     */
    protected $_offset = 0;

    /**
     * @param   string  $source
     * @param   string  $decodeType
     * @return  void
     */
    protected function __construct($source, $decodeType)
    {
        $this->_source = $source;
        $this->_sourceLength = strlen($this->_source);

        if (!in_array($decodeType, array(Rych_Bencode::TYPE_ARRAY, Rych_Bencode::TYPE_OBJECT))) {
            $decodeType = Rych_Bencode::TYPE_ARRAY;
        }

        $this->_decodeType = $decodeType;
    }

    /**
     * Takes a bencode encoded string and converts it to an array.
     *
     * @param   string  $source             The bencode encoded string
     * @param   string  $objectDecodeType   Return an array or an object
     * @return  array
     * @throws  Rych_Bencode_Exception
     *
     * @todo    Convert resulting array to object if $objectDecodeType is object
     */
    static public function decode($source, $objectDecodeType = Rych_Bencode::TYPE_ARRAY)
    {
        if (!is_string($source)) {
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Argument expected to be a string; Got ' . gettype($source));
        }

        $decoder = new self($source, $objectDecodeType);
        $decoded = $decoder->_decode();

        if ($decoder->_offset != $decoder->_sourceLength) {
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Found multiple entities outside list or dict definitions');
        }

        return $decoded;
    }

    /**
     * Decode $_source from the current offset.
     *
     * @return  mixed
     */
    protected function _decode()
    {
        switch ($this->_getChar()) {

            case 'i':
                ++$this->_offset;
                return $this->_decodeInteger();
                break;

            case 'l':
                ++$this->_offset;
                return $this->_decodeList();
                break;

            case 'd':
                ++$this->_offset;
                return $this->_decodeDict();
                break;

            default:
                if ($this->_isDigit($this->_getChar())) {
                    return $this->_decodeString();
                }

        }

        require_once 'Rych/Bencode/Exception.php';
        throw new Rych_Bencode_Exception("Unknown entity found at offset {$this->_offset}");
    }

    /**
     * Convert a bencode encoded integer entity into a PHP integer.
     *
     * @return  integer
     * @throws  Rych_Bencode_Exception
     */
    protected function _decodeInteger()
    {
        // Calculate the offset of the end of the integer definition
        $offsetOfE = strpos($this->_source, 'e', $this->_offset);
        if (false === $offsetOfE) {
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Unterminated integer entity');
        }

        // Local offset record
        $currentOffset = $this->_offset;

        // Skip first character if it is a '-' for validation
        if ('-' == $this->_getChar($currentOffset)) {
            ++$currentOffset;
        }

        // Check to see if the entity is empty ("ie" or "i-e")
        if ($offsetOfE === $currentOffset) {
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Empty integer entity');
        }

        // Check each character to make sure it's a digit
        for (; $currentOffset < $offsetOfE; ++$currentOffset) {
            if (!$this->_isDigit($this->_getChar($currentOffset))) {
                require_once 'Rych/Bencode/Exception.php';
                throw new Rych_Bencode_Exception('Non-numeric character found in integer entity');
            }
        }

        // Pull the whole record from the encoded source
        $value = substr($this->_source, $this->_offset, $offsetOfE - $this->_offset);

        // One last check to make sure zero-padded integers don't slip by, as
        // they're not allowed per bencode specification.
        $absoluteValue = trim($value, '-');
        if (1 < strlen($absoluteValue) && '0' == substr($absoluteValue, 0, 1)) {
            // TODO: Could probably just trigger a warning here
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Leading zero found in integer entity');
        }

        // Advance the global offset
        $this->_offset = $offsetOfE + 1;

        // The +0 auto-casts the chunk to either an integer or a float (in cases
        // where an integer would overrun the max limits of integer types)
        return $value + 0;
    }

    /**
     * Convert a bencode encoded list entity into a PHP array.
     *
     * @return  array
     * @throws  Rych_Bencode_Exception
     */
    protected function _decodeList()
    {
        $list = array ();
        $terminated = false;

        // Loop through and decode each item
        while (false !== $this->_getChar()) {

            if ('e' == $this->_getChar()) {
                $terminated = true;
                break;
            }

            $list[] = $this->_decode();
        }

        // Check if we ran out of characters, or found the "e"
        if (!$terminated && false === $this->_getChar()) {
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Unterminated list definition');
        }

        // Advance the global offset
        ++$this->_offset;

        return $list;
    }

    /**
     * Convert a bencode encoded dictionary entity into a PHP associative array.
     *
     * @return  array
     * @throws  Rych_Bencode_Exception
     */
    protected function _decodeDict()
    {
        $dict = array ();
        $terminated = false;

        while (false !== $this->_getChar()) {

            if ('e' == $this->_getChar()) {
                $terminated = true;
                break;
            }

            // A dict key must be a string, and all strings are formatted
            // <length>:<content>, so we can be reasonably sure that if the
            // current offset is not a digit, it's not a string definition.
            if (!$this->_isDigit($this->_getChar())) {
                require_once 'Rych/Bencode/Exception.php';
                throw new Rych_Bencode_Exception('Invalid dictionary key');
            }

            $key = $this->_decodeString();

            // Check for duplicate keys
            if (isset ($dict[$key])) {
                // TODO: This could probably just trigger a warning...
                require_once 'Rych/Bencode/Exception.php';
                throw new Rych_Bencode_Exception('Duplicate dictionary key');
            }

            $dict[$key] = $this->_decode();
        }

        // Check if we ran out of characters before we found the 'e' :-)
        if (!$terminated && false === $this->_getChar()) {
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Unterminated dictionary definition');
        }

        // Advance the global offset
        ++$this->_offset;

        return $dict;
    }

    /**
     * convert a bencode encoded string into a PHP string.
     *
     * @return  string
     * @throws  Rych_Bencode_Exception
     */
    protected function _decodeString()
    {
        // Check for invalid content length declarations
        if ('0' === $this->_getChar() && ':' != $this->_getChar($this->_offset + 1)) {
            // TODO: Trigger a warning instead?
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Found leading zero in string entity length declaration');
        }

        // Find the colon
        // *points to belly* -- FOUND IT! :-D
        $offsetOfColon = strpos($this->_source, ':', $this->_offset);
        if (false === $offsetOfColon) {
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Unterminated string entity');
        }

        // Find the length of the string
        $contentLength = (int) substr($this->_source, $this->_offset, $offsetOfColon);

        // Check if we have the entire string, or if our source is truncated
        if (($contentLength + $offsetOfColon + 1) > $this->_sourceLength) {
            require_once 'Rych/Bencode/Exception.php';
            throw new Rych_Bencode_Exception('Unexpected end of string');
        }

        // Pull the string from the source
        $value = substr($this->_source, $offsetOfColon + 1, $contentLength);

        // Advance the global offset
        $this->_offset = $offsetOfColon + $contentLength + 1;

        return $value;
    }

    /**
     * Retrieve the character at the current offset in $_source.
     *
     * @param   integer $offset Numeric offset to use, if not the global offset
     * @return  string
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
     * @return  boolean
     */
    protected function _isDigit($char)
    {
        return in_array($char,
            array ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'));
    }

}