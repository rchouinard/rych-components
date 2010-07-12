<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych_Ip2Location
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2010 Ryan Chouinard
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
 * @property    string  $ipAddress
 * @property    integer $ipNumber
 * @property    string  $countryShort
 * @property    string  $countryLong
 * @property    string  $region
 * @property    string  $city
 * @property    string  $isp
 * @property    float   $latitude
 * @property    float   $longitude
 * @property    string  $domain
 * @property    string  $zipCode
 * @property    string  $timeZone
 * @property    string  $netSpeed
 * @property    string  $iddCode
 * @property    string  $areaCode
 * @property    string  $weatherCode
 * @property    string  $weatherName
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
            throw new Rych_Ip2Location_Exception(
                "Cannot modify read-only property '{$name}'"
            );
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