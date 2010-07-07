<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych_Ip2Location
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2010 Ryan Chouinard ({@link http://ryanchouinard.com})
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
 * @see         Rych_Ip2Location
 */
require_once 'Rych/Ip2Location.php';

/**
 * @category    Rych
 * @package     Rych_Ip2Location
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 */
class Rych_Ip2LocationTest extends PHPUnit_Framework_TestCase
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

    public function testInvalidFileThrowsException()
    {
        $this->setExpectedException('Rych_Ip2Location_Exception');
        $ip2loc = new Rych_Ip2Location('fake-file.bin');
    }

    /**
     * @return  void
     */
    public function testCanLoadDbFile()
    {
        $ip2loc = new Rych_Ip2Location;
        $ip2loc->setDbFile(LIBRARY_ROOT . '/tests/_files/DB1-DEMO.bin');
        $this->assertEquals('2008.08.07', $ip2loc->getVersion());
    }

    /**
     * @return  void
     */
    public function testConstructorCanLoadDbFile()
    {
        $ip2loc = new Rych_Ip2Location(LIBRARY_ROOT . '/tests/_files/DB1-DEMO.bin');
        $this->assertEquals('2008.08.07', $ip2loc->getVersion());
    }

    /**
     * @return  void
     */
    public function testCanSetDefaultDbFile()
    {
        Rych_Ip2Location::setDefaultDbFile(LIBRARY_ROOT . '/tests/_files/DB1-DEMO.bin');
        $ip2loc = new Rych_Ip2Location;
        $this->assertEquals('2008.08.07', $ip2loc->getVersion());
    }

    public function testFeatureDetection()
    {
        $ip2loc = new Rych_Ip2Location;
        $ip2loc->setDbFile(LIBRARY_ROOT . '/tests/_files/DB1-DEMO.bin');
        $this->assertEquals(true, $ip2loc->hasFeature(Rych_Ip2Location::FEATURE_COUNTRY));
        $this->assertEquals(false, $ip2loc->hasFeature(Rych_Ip2Location::FEATURE_LATLON));
    }

    /**
     * @return  void
     */
    public function DISABLEtestFullLookup()
    {
        try {
            $ip2loc = new Rych_Ip2Location(LIBRARY_ROOT . '/tests/_files/DB18-DEMO.bin');
        } catch (Rych_Ip2Location_Exception $e) {
            $this->markTestSkipped('Required sample database file not found; ' .
                                   'DB18 must be in ' .
                                   realpath(LIBRARY_ROOT . '/tests/_files'));
        }

        // Fun fact: the IP belongs to ip2location.com :-)
        $record = $ip2loc->lookup('70.86.96.219');

        $this->assertTrue($record instanceof Rych_Ip2Location_Record);
        $this->assertEquals('70.86.96.219', $record->ipAddress);
        $this->assertEquals(1180066011, $record->ipNumber);
        $this->assertEquals('US', $record->countryShort);
        $this->assertEquals('UNITED STATES', $record->countryLong);
        $this->assertEquals('TEXAS', $record->region);
        $this->assertEquals('DALLAS', $record->city);
        $this->assertEquals('THEPLANET.COM INTERNET SERVICES INC', $record->isp);
        $this->assertEquals('32.79610062', number_format($record->latitude, 8));
        $this->assertEquals('-96.80239868', number_format($record->longitude, 8));
        $this->assertEquals('THEPLANET.COM', $record->domain);
        $this->assertEquals('75207', $record->zipCode);
        $this->assertEquals('-06:00', $record->timeZone);
        $this->assertEquals('COMP', $record->netSpeed);
        $this->assertEquals('1', $record->iddCode);
        $this->assertEquals('214/972', $record->areaCode);
        $this->assertEquals('USTX0327', $record->weatherCode);
        $this->assertEquals('DALLAS', $record->weatherName);
    }

    /**
     * @return  void
     */
    public function testPartialLookup()
    {
        $ip2loc = new Rych_Ip2Location(LIBRARY_ROOT . '/tests/_files/DB1-DEMO.bin');
        // Fun fact: the IP belongs to ip2location.com :-)
        $record = $ip2loc->lookup('70.86.96.219');

        $this->assertTrue($record instanceof Rych_Ip2Location_Record);
        $this->assertEquals('70.86.96.219', $record->ipAddress);
        $this->assertEquals(1180066011, $record->ipNumber);
        $this->assertEquals('US', $record->countryShort);
        $this->assertEquals('UNITED STATES', $record->countryLong);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->region);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->city);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->isp);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->latitude);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->longitude);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->domain);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->zipCode);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->timeZone);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->netSpeed);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->iddCode);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->areaCode);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->weatherCode);
        $this->assertEquals(Rych_Ip2Location::UNSUPPORTED, $record->weatherName);
    }

}