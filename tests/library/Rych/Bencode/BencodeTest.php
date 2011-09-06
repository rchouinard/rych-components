<?php
/**
 * Ryan Chouinard's Components
 *
 * @category   Rych
 * @package    Rych_Bencode
 * @subpackage UnitTests
 * @author     Ryan Chouinard <rchouinard@gmail.com>
 * @copyright  Copyright (c) 2010 Ryan Chouinard
 * @license    New BSD License
 */

/**
 * @namespace
 */
namespace Rych\Bencode\Test;

require_once 'Rych\Bencode\Bencode.php';
use Rych\Bencode\Bencode;

/**
 * Ryan Chouinard's Components
 *
 * @category   Rych
 * @package    Rych_Bencode
 * @subpackage UnitTests
 * @author     Ryan Chouinard <rchouinard@gmail.com>
 * @license    New BSD License
 */
class BencodeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return array
     */
    public function providerForDecoderTest()
    {
        return array (
            // Integers
            array ('i0e', 0),
            array ('i-0e', 0),
            array ('i15e', 15),
            array ('i-15e', -15),

            // Strings
            array ('0:', ''),
            array ('15:aaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaa'),
            array ('15:               ', '               '),

            // Lists
            array ('le', array ()),
            array ('l3:ham4:eggse', array ('ham', 'eggs')),
            array ('llee', array (array ())),
            array ('ll3:oneel3:twoee', array (array ('one'), array ('two'))),

            // Dictionaries
            array ('d3:foo3:bar3:bar3:baze', array ('foo' => 'bar', 'bar' => 'baz')),
            array ('d3:fooi50ee', array ('foo' => 50)),
            array ('d3:fool3:bar3:bazee', array ('foo' => array ('bar', 'baz'))),
            array ('d3:food3:bar3:bazee', array ('foo' => array ('bar' => 'baz'))),

            // Dictionaries as objects
            array ('d3:foo3:bar3:bar3:baze', (object) array ('foo' => 'bar', 'bar' => 'baz'), Bencode::TYPE_OBJECT),
            array ('d3:fooi50ee', (object) array ('foo' => 50), Bencode::TYPE_OBJECT),
            array ('d3:fool3:bar3:bazee', (object) array ('foo' => array ('bar', 'baz')), Bencode::TYPE_OBJECT),
            array ('d3:food3:bar3:bazee', (object) array ('foo' => (object) array ('bar' => 'baz')), Bencode::TYPE_OBJECT)
        );
    }

    /**
     * @return array
     */
    public function providerForDecoderExceptionTest()
    {
        return array (
            // Unknown entity
            array ('unknown', 'Unknown entity'),

            // Integers
            array ('ie', 'Invalid integer entity'),
            array ('iae', 'Invalid integer entity'),
            array ('i3.14e', 'Invalid integer entity'),
            array ('i', 'Unterminated integer entity'),

            // Strings
            array ('5aaaaa', 'Invalid string entity'),
            array ('5:aaa', 'Unexpected end of string entity'),

            // Lists
            array ('l3:foo', 'Unterminated list entity'),

            // Dictionaries
            array ('d3:foo3:bar', 'Unterminated dictionary entity'),
            array ('d3:fooe', 'Unknown entity'),
            array ('di5e3:fooe', 'Invalid dictionary key'),
            array ('d3:foo3:bar3:foo3:baz', 'Invalid dictionary key')
        );
    }

    /**
     * @return array
     */
    public function providerForEncoderTest()
    {
        return array (
            // Integers
            array (0, 'i0e'),
            array ('05', 'i5e'),
            array (-15, 'i-15e'),
            array (3.14, 'i3e'),

            // Strings
            array ('', '0:'),
            array ('The snozberries taste like snozberries', '38:The snozberries taste like snozberries'),

            // Lists
            array (array (), 'le'),
            array (array ('foo', 'bar'), 'l3:foo3:bare'),
            array (array (15), 'li15ee'),

            // Dictionaries
            array (array ('foo' => 'bar'), 'd3:foo3:bare'),
            array (array (5 => 'foo'), 'd1:53:fooe'),
            array (array ('foo' => 5), 'd3:fooi5ee')
        );
    }

    /**
     * @test
     * @dataProvider providerForDecoderTest
     * @param string $encodedValue
     * @param mixed $decodedValue
     * @return void
     */
    public function decoderProperlyHandlesEntities($encodedValue, $decodedValue, $decodeType = Bencode::TYPE_ARRAY)
    {
        $this->assertEquals($decodedValue, Bencode::decode($encodedValue, $decodeType));
    }

    /**
     * @test
     * @dataProvider providerForDecoderExceptionTest
     * @param string $encodedValue
     * @param integer $value
     * @return void
     */
    public function decoderProperlyHandlesInvalidEntities($encodedValue, $message)
    {
        $this->setExpectedException('RuntimeException', $message);
        Bencode::decode($encodedValue);
    }

    /**
     * @test
     * @dataProvider providerForEncoderTest
     * @param mixed $decodedValue
     * @param string $encodedValue
     * @return void
     */
    public function encoderProperlyHandlesInput($decodedValue, $encodedValue)
    {
        $this->assertEquals($encodedValue, Bencode::encode($decodedValue));
    }

}