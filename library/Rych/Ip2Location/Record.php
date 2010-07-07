<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych_Ip2Location
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2009 Ryan Chouinard ({@link http://ryanchouinard.com})
 * @license     New BSD License
 * @version     $Id$
 */

/**
 * @see         Rych_Ip2Location
 */
require_once 'Rych/Ip2Location.php';

/**
 * @category    Rych
 * @package     Rych_Ip2Location
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 *
 * @property    $ipAddress
 * @property    $ipNumber
 * @property    $countryShort
 * @property    $countryLong
 * @property    $region
 * @property    $city
 * @property    $isp
 * @property    $latitude
 * @property    $longitude
 * @property    $domain
 * @property    $zipCode
 * @property    $timeZone
 * @property    $netSpeed
 * @property    $iddCode
 * @property    $areaCode
 * @property    $weatherCode
 * @property    $weatherName
 */
class Rych_Ip2Location_Record
{

    protected $_data = array (
        'ipAddress'     => null,
        'ipNumber'      => null,
        'countryShort'  => null,
        'countryLong'   => null,
        'region'        => null,
        'city'          => null,
        'isp'           => null,
        'latitude'      => null,
        'longitude'     => null,
        'domain'        => null,
        'zipCode'       => null,
        'timeZone'      => null,
        'netSpeed'      => null,
        'iddCode'       => null,
        'areaCode'      => null,
        'weatherCode'   => null,
        'weatherName'   => null
    );

    /**
     * @param   array   $data
     * @return  void
     */
    public function __construct(array $data)
    {
        foreach ($data as $name => $value) {
            if (array_key_exists($name, $this->_data)) {
                $this->_data[$name] = $value;
            }
        }
    }

    /**
     * @return  array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * @param   string  $name
     * @return  mixed
     */
    public function __get($name)
    {
        $value = null;
        if (array_key_exists($name, $this->_data)) {
            $value = $this->_data[$name];
        }
        return $value;
    }

    /**
     * @throws  Rych_Ip2Location_Exception
     * @param   string  $name
     * @param   mixed   $value
     * @return  void
     */
    public function __set($name, $value)
    {
        // IF statement is really just to keep ZSE from complaining about
        // unused arguments :-)
        if (isset ($value)) {
            require_once 'Rych/Ip2Location/Exception.php';
            throw new Rych_Ip2Location_Exception("Cannot modify read-only property '{$name}'");
        }
    }

    /**
     * @param   string  $name
     * @return  boolean
     */
    public function __isset($name)
    {
        return isset ($this->_data[$name]);
    }

    /**
     * @param   string  $name
     * @return  void
     */
    public function __unset($name)
    {
        $this->{$name} = null;
    }

}