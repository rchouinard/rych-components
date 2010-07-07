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
require_once realpath(dirname(__FILE__) . '/../bootstrap.php');

/**
 * @see         PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @see         Rych_Bencode
 */
require_once 'Rych/Bencode.php';

/**
 * @category    Rych
 * @package     Rych_Bencode
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 */
class Rych_BencodeTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return  void
     */
    public function setUp()
    {
    }

    /**
     * @return  void
     */
    public function tearDown()
    {
    }

    /**
     * @param   array   $values
     * @return  void
     */
    protected function _testBencode($values)
    {
        $encoded = Rych_Bencode::encode($values);
        $this->assertEquals($values, Rych_Bencode::decode($encoded));
    }

    /**
     * @return  void
     */
    public function testDecoderProxy()
    {
        $this->assertEquals('string', Rych_Bencode::decode('6:string'));
    }

    /**
     * @return  void
     */
    public function testEncoderProxy()
    {
        $this->assertEquals('6:string', Rych_Bencode::encode('string'));
    }

    /**
     * @return  void
     */
    public function testEncodeDecodeString()
    {
        $this->_testBencode('string');
    }

    /**
     * @return  void
     */
    public function testEncodeDecodeInteger()
    {
        $this->_testBencode(200);
    }

    /**
     * @return  void
     */
    public function testEncodeDecodeList()
    {
        $this->_testBencode(array ('foo'));
    }

    /**
     * @return  void
     */
    public function testEncodeDecodeDict()
    {
        $this->_testBencode(array ('foo' => 'bar'));
    }

}