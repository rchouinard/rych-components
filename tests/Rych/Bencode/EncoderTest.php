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

require_once realpath(dirname(__FILE__) . '/../../bootstrap.php');

/**
 * @see         PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @see         Rych_Bencode
 */
require_once 'Rych/Bencode/Encoder.php';

/**
 * @category    Rych
 * @package     Rych_Bencode
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 * @group       Rych_Bencode
 */
class Rych_Bencode_EncoderTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return  void
     */
    public function testEncodeString()
    {
        $this->assertEquals(
            '6:string',
            Rych_Bencode_Encoder::encode('string')
        );
    }

    /**
     * @return  void
     */
    public function testEncodeInteger()
    {
        $this->assertEquals(
            'i42e',
            Rych_Bencode_Encoder::encode(42)
        );
        $this->assertEquals(
            'i-42e',
            Rych_Bencode_Encoder::encode(-42)
        );
        $this->assertEquals(
            'i0e',
            Rych_Bencode_Encoder::encode(0)
        );
    }

    /**
     * @return  void
     */
    public function testEncodeList()
    {
        $this->assertEquals(
            'l3:foo3:bare',
            Rych_Bencode_Encoder::encode(array ('foo', 'bar'))
        );
    }

    /**
     * @return  void
     */
    public function testEncodeDict()
    {
        $this->assertEquals(
            'd3:foo3:bare',
            Rych_Bencode_Encoder::encode(array ('foo' => 'bar'))
        );
    }

    /**
     * @return  void
     */
    public function testCanEncodeObjectWithoutToArray()
    {
        $object = new stdClass;
        $object->string = 'foo';
        $object->integer = 42;

        $this->assertEquals(
            'd7:integeri42e6:string3:fooe',
            Rych_Bencode_Encoder::encode($object)
        );
    }

}