<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych_Gravatar
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2009 Ryan Chouinard ({@link http://ryanchouinard.com})
 * @license     New BSD License
 * @version     $Id$
 */

require_once realpath(dirname(__FILE__) . '/../bootstrap.php');

/**
 * @see         PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @see         Rych_Gravatar
 */
require_once 'Rych/Gravatar.php';

/**
 * @category    Rych
 * @package     Rych_Gravatar
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 * @group       Rych_Bencode
 */
class Rych_GravatarTest extends PHPUnit_Framework_TestCase
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
     * @return  void
     */
    public function testDefaultsProduceValidUri()
    {
        $gravatar = new Rych_Gravatar;
        list($uri, $queryString) = explode('?', $gravatar->getUri('test@example.com'));
        parse_str($queryString, $query);

        $this->assertRegExp(
            '#^http://www.gravatar.com/avatar/[0-9a-f]{32}.jpg$#i',
            $uri
        );

        $this->assertEquals(80, $query['s']);
        $this->assertEquals('g', $query['r']);
    }

    /**
     * @return  void
     */
    public function testChangingDefaultsChangesQueryString()
    {
        $gravatar = new Rych_Gravatar;
        $gravatar
            ->setDefault(Rych_Gravatar::DEFAULT_WAVATAR)
            ->setRating(Rych_Gravatar::RATING_PG)
            ->setSize(120);
        list($uri, $queryString) = explode('?', $gravatar->getUri('test@example.com'));
        parse_str($queryString, $query);

        $this->assertRegExp(
            '#^http://www.gravatar.com/avatar/[0-9a-f]{32}.jpg$#i',
            $uri
        );

        $this->assertEquals(Rych_Gravatar::DEFAULT_WAVATAR, $query['d']);
        $this->assertEquals(120, $query['s']);
        $this->assertEquals(Rych_Gravatar::RATING_PG, $query['r']);
    }

}