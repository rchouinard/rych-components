<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych_Bencode
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2009 Ryan Chouinard ({@link http://ryanchouinard.com})
 * @license     New BSD License
 * @version     $Id$
 */

/**
 * @see         bootstrap.php
 */
require_once realpath(dirname(__FILE__) . '/../../bootstrap.php');

/**
 * @see         PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @see         Rych_Bencode
 */
require_once 'Rych/Bencode/Decoder.php';

/**
 * @category    Rych
 * @package     Rych_Bencode
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 */
class Rych_Bencode_DecoderTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return  void
     */
    public function testCanDecodeString()
    {
        $this->assertEquals(
            'string',
            Rych_Bencode_Decoder::decode('6:string')
        );
    }

    /**
     * @return  void
     */
    public function testUnterminatedStringThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('6:stri');
    }

    /**
     * @return  void
     */
    public function testZeroPaddedLengthInStringDefThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('03:foo');
    }

    /**
     * @return  void
     */
    public function testMissingColonInStringDefThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('3foo');
    }

    /**
     * @return  void
     */
    public function testCanDecodeInteger()
    {
        // Positive integer
        $this->assertEquals(
            '42',
            Rych_Bencode_Decoder::decode('i42e')
        );

        // Negative integer
        $this->assertEquals(
            '-42',
            Rych_Bencode_Decoder::decode('i-42e')
        );

        // Zero
        $this->assertEquals(
            '0',
            Rych_Bencode_Decoder::decode('i0e')
        );
    }

    /**
     * @return  void
     */
    public function testEmptyIntegerThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('ie');
    }

    /**
     * @return  void
     */
    public function testNonDigitCharInIntegerDefThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('iae');
    }

    /**
     * @return  void
     */
    public function testLeadingZeroInIntegerDefThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('i042e');
    }

    /**
     * @return  void
     */
    public function testUnterminatedIntegerThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('i42');
    }

    /**
     * @return  void
     */
    public function testCanDecodeList()
    {
        $this->assertEquals(
            array ('foo', 'bar'),
            Rych_Bencode_Decoder::decode('l3:foo3:bare')
        );
    }

    /**
     * @return  void
     */
    public function testUnterminatedListThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('l3:foo3:bar');
    }

    /**
     * @return  void
     */
    public function testDecodeDict()
    {
        $this->assertEquals(
            array ('foo' => 'bar'),
            Rych_Bencode_Decoder::decode('d3:foo3:bare')
        );
    }

    /**
     * @return  void
     */
    public function testUnterminatedDictThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('d3:foo3:bar');
    }

    /**
     * @return  void
     */
    public function testDuplicateDictKeyThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('d3:foo3:bar3:foo3:bare');
    }

    /**
     * @return  void
     */
    public function testNonStringDictKeyThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('di42e3:bare');
    }

    /**
     * @return  void
     */
    public function testUnknownEntityThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('a3:fooe');
    }

    /**
     * @return  void
     */
    public function testDecodeNonStringThrowsException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode(array ());
    }

    public function testDecodeMultipleTypesOutsideOfListOrDictShouldThrowException()
    {
        $this->setExpectedException('Rych_Bencode_Exception');
        Rych_Bencode_Decoder::decode('3:foo3:bar');
    }

}