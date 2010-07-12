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
 * @see         Rych_Ip2Location_Record
 */
require_once 'Rych/Ip2Location/Record.php';

/**
 * @category    Rych
 * @package     Rych_Ip2Location
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 */
class Rych_Ip2Location
{

    const UNSUPPORTED       = 'unsupported';
    const MAX_IPV4_RANGE    = 4294967295;

    const FEATURE_COUNTRY   =    1;
    const FEATURE_REGION    =    2;
    const FEATURE_CITY      =    4;
    const FEATURE_LATLON    =    8;
    const FEATURE_ZIPCODE   =   16;
    const FEATURE_TIMEZONE  =   32;
    const FEATURE_ISP       =   64;
    const FEATURE_DOMAIN    =  128;
    const FEATURE_NETSPEED  =  256;
    const FEATURE_AREACODE  =  512;
    const FEATURE_WEATHER   = 1024;

    /**
     * Bitwise map of features supported by database types.
     *
     * For example, DB3 supports COUNTRY, REGION, and CITY, so the third
     * position holds 7 (1 + 2 + 4).
     *
     * @var     array
     */
    protected $_dbFeatures = array (
          1,  65,   7,  71,  15,  79,  199,  207,   31,
        223,  63, 255, 303, 511, 575, 1023, 1327, 2047
    );

    /**
     * Map of feature data positions.
     *
     * There are 18 database types, so each feature has 18 possible positions.
     * For example, the region data for DB3 is stored at position 3.
     *
     * @var     array
     */
    protected $_dbPositions = array (
        'country'       => array (2,  2,  2,  2,  2,  2,  2,  2,  2,  2,  2,  2,  2,  2,  2,  2,  2,  2),
        'region'        => array (0,  0,  3,  3,  3,  3,  3,  3,  3,  3,  3,  3,  3,  3,  3,  3,  3,  3),
        'city'          => array (0,  0,  4,  4,  4,  4,  4,  4,  4,  4,  4,  4,  4,  4,  4,  4,  4,  4),
        'isp'           => array (0,  3,  0,  5,  0,  7,  5,  7,  0,  8,  0,  9,  0,  9,  0,  9,  0,  9),
        'latitude'      => array (0,  0,  0,  0,  5,  5,  0,  5,  5,  5,  5,  5,  5,  5,  5,  5,  5,  5),
        'longitude'     => array (0,  0,  0,  0,  6,  6,  0,  6,  6,  6,  6,  6,  6,  6,  6,  6,  6,  6),
        'domain'        => array (0,  0,  0,  0,  0,  0,  6,  8,  0,  9,  0, 10,  0, 10,  0, 10,  0, 10),
        'zipcode'       => array (0,  0,  0,  0,  0,  0,  0,  0,  7,  7,  7,  7,  0,  7,  7,  7,  0,  7),
        'timezone'      => array (0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  8,  8,  7,  8,  8,  8,  7,  8),
        'netspeed'      => array (0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  8, 11,  0, 11,  8, 11),
        'iddcode'       => array (0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  9, 12,  0, 12),
        'areacode'      => array (0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0, 10, 13,  0, 13),
        'weathercode'   => array (0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  9, 14),
        'weathername'   => array (0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0,  0, 10, 15)
    );

    /**
     * Path to the default database file.
     *
     * @var     string
     */
    protected static $_defaultDbFile;

    /**
     * Path to the database file.
     *
     * @var     string
     */
    protected $_dbFile;

    /**
     * Database file handle.
     *
     * @var     resource
     */
    protected $_dbHandle;

    /**
     * Array of top-level data from the database file.
     *
     * @var     array
     */
    protected $_data = array (
        'dbType'    => null,
        'dbColumns' => null,
        'dbYear'    => null,
        'dbMonth'   => null,
        'dbDay'     => null,
        'dbRecords' => null,
        'dbAddr'    => null,
        'ipVersion' => null
    );

    /**
     * Construct a new Ip2Location instance.
     *
     * @param   string  $dbFile
     * @return  void
     */
    public function __construct($dbFile = null)
    {
        if ($dbFile) {
            $this->setDbFile($dbFile);
        } else if (self::$_defaultDbFile) {
            $this->setDbFile(self::$_defaultDbFile);
        }
    }

    /**
     * Safely destroy the Ip2Location instance.
     *
     * @return  void
     */
    public function __destruct()
    {
        if ($this->_dbHandle) {
            fclose($this->_dbHandle);
            $this->_dbHandle = null;
        }
    }

    /**
     * Return the version date of the current database file.
     *
     * @return  string
     */
    public function getVersion()
    {
        return date(
            'Y.m.d',
            mktime(0, 0, 0, $this->dbMonth, $this->dbDay, 2000 + $this->dbYear)
        );
    }

    /**
     * Set a global default database file for all future Ip2Location instances.
     *
     * @static
     * @param   string  $dbFile
     * @return  void
     */
    public static function setDefaultDbFile($dbFile)
    {
        self::$_defaultDbFile = $dbFile;
    }

    /**
     * Set a database file for the current Ip2Location instance.
     *
     * @throws  Rych_Ip2Location_Exception
     * @param   string  $dbFile
     * @return  Rych_Ip2Location
     */
    public function setDbFile($dbFile)
    {
        $dbFile = realpath($dbFile);
        if (!file_exists($dbFile) || !is_readable($dbFile)) {
            require_once 'Rych/Ip2Location/Exception.php';
            throw new Rych_Ip2Location_Exception(
                'Specified file does not exist or cannot be read'
            );
        }

        $dbHandle = fopen($dbFile, 'rb');
        if (!$dbHandle) {
            require_once 'Rych/Ip2Location/Exception.php';
            throw new Rych_Ip2Location_Exception(
                'Failed opening IP2Location database'
            );
        }

        $this->_dbFile   = $dbFile;
        $this->_dbHandle = $dbHandle;

        $this->_data['dbType']      = $this->_read8(1);
        $this->_data['dbColumns']   = $this->_read8(2);
        $this->_data['dbYear']      = $this->_read8(3);
        $this->_data['dbMonth']     = $this->_read8(4);
        $this->_data['dbDay']       = $this->_read8(5);
        $this->_data['dbRecords']   = $this->_read32(6);
        $this->_data['dbAddr']      = $this->_read32(10);
        $this->_data['ipVersion']   = $this->_read32(14);

        if (!in_array($this->dbType, range(1, 18)) || !in_array($this->dbColumns, range(2, 15))) {
            require_once 'Rych/Ip2Location/Exception.php';
            throw new Rych_Ip2Location_Exception(
                'File does not appear to be a valid IP2Location database.'
            );
        }

        if (0 != $this->ipVersion) {
            require_once 'Rych/Ip2Location/Exception.php';
            throw new Rych_Ip2Location_Exception(
                'This library currently supports only IPv4'
            );
        }

        return $this;
    }

    /**
     * Check if the current database supports a given feature.
     *
     * Works by performing a bitwise AND against the passed-in feature and the
     * $_dbFeatures map. If the result of the operation is grater than 0, the
     * feature is supported.
     *
     * @param   integer $feature
     * @return  boolean
     */
    public function hasFeature($feature)
    {
        $features = $this->_dbFeatures[$this->dbType - 1];
        return (bool) ((int) $feature & (int) $features);
    }

    /**
     * Proxy to getRecord() method.
     *
     * @param   string  $ipaddr
     * @return  Rych_Ip2Location_Record
     */
    public function lookup($ipAddr)
    {
        return $this->getRecord($ipAddr);
    }

    /**
     * Retrieve an instance of Rych_Ip2Location_Record with details of the
     * requested IP address.
     *
     * @throws  Rych_Ip2Location_Exception
     * @param   string  $ipaddr
     * @return  Rych_Ip2Location_Record
     */
    public function getRecord($ipAddr)
    {
        $ipAddr = gethostbyname($ipAddr);
        if (!$this->_isValidIp($ipAddr)) {
            require_once 'Rych/Ip2Location/Exception.php';
            throw new Rych_Ip2Location_Exception('Invalid address given');
        }

        $realIpNo = ip2long($ipAddr);
        $realIpNo = ($realIpNo < 0) ? $realIpNo + pow(2, 32) : $realIpNo;

        $recordData = array (
            'ipAddress'     => $ipAddr,
            'ipNumber'      => $realIpNo,
            'countryShort'  => self::UNSUPPORTED,
            'countryLong'   => self::UNSUPPORTED,
            'region'        => self::UNSUPPORTED,
            'city'          => self::UNSUPPORTED,
            'isp'           => self::UNSUPPORTED,
            'latitude'      => self::UNSUPPORTED,
            'longitude'     => self::UNSUPPORTED,
            'domain'        => self::UNSUPPORTED,
            'zipCode'       => self::UNSUPPORTED,
            'timeZone'      => self::UNSUPPORTED,
            'netSpeed'      => self::UNSUPPORTED,
            'iddCode'       => self::UNSUPPORTED,
            'areaCode'      => self::UNSUPPORTED,
            'weatherCode'   => self::UNSUPPORTED,
            'weatherName'   => self::UNSUPPORTED
        );

        $low  = 0;
        $high = $this->dbRecords;
        $ipNo = ($realIpNo == self::MAX_IPV4_RANGE) ? $realIpNo - 1 : $realIpNo;
        while ($low <= $high) {
            $mid = (int) (($low + $high) / 2);
            $ipFrom = $this->_read32($this->dbAddr + $mid       * $this->dbColumns * 4);
            $ipTo   = $this->_read32($this->dbAddr + ($mid + 1) * $this->dbColumns * 4);

            $ipFrom = ($ipFrom < 0) ? $ipFrom + pow(2, 32) : $ipFrom;
            $ipTo   = ($ipTo   < 0) ? $ipTo   + pow(2, 32) : $ipTo;

            if (($ipNo >= $ipFrom) && ($ipNo < $ipTo)) {

                if ($this->hasFeature(self::FEATURE_COUNTRY)) {
                    $recordData['countryShort'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('country') - 1)
                        )
                    );
                    $recordData['countryLong']  = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('country') - 1)
                        ) + 3
                    );
                }
                if ($this->hasFeature(self::FEATURE_REGION)) {
                    $recordData['region'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('region') - 1)
                        )
                    );
                }
                if ($this->hasFeature(self::FEATURE_CITY)) {
                    $recordData['city'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('city') - 1)
                        )
                    );
                }
                if ($this->hasFeature(self::FEATURE_ISP)) {
                    $recordData['isp'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('isp') - 1)
                        )
                    );
                }
                if ($this->hasFeature(self::FEATURE_LATLON)) {
                    $recordData['latitude'] = $this->_readFloat(
                        $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                        * ($this->_getDbPosition('latitude') - 1)
                    );
                    $recordData['longitude'] = $this->_readFloat(
                        $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                        * ($this->_getDbPosition('longitude') - 1)
                    );
                }
                if ($this->hasFeature(self::FEATURE_DOMAIN)) {
                    $recordData['domain'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('domain') - 1)
                        )
                    );
                }
                if ($this->hasFeature(self::FEATURE_ZIPCODE)) {
                    $recordData['zipCode'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('zipcode') - 1)
                        )
                    );
                }
                if ($this->hasFeature(self::FEATURE_TIMEZONE)) {
                    $recordData['timeZone'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('timezone') - 1)
                        )
                    );
                }
                if ($this->hasFeature(self::FEATURE_NETSPEED)) {
                    $recordData['netSpeed'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('netspeed') - 1)
                        )
                    );
                }
                if ($this->hasFeature(self::FEATURE_AREACODE)) {
                    $recordData['iddCode'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('iddcode') - 1)
                        )
                    );
                    $recordData['areaCode'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('areacode') - 1)
                        )
                    );
                }
                if ($this->hasFeature(self::FEATURE_WEATHER)) {
                    $recordData['weatherCode'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('weathercode') - 1)
                        )
                    );
                    $recordData['weatherName'] = $this->_readStr(
                        $this->_read32(
                            $this->dbAddr + ($mid * $this->dbColumns * 4) + 4
                            * ($this->_getDbPosition('weathername') - 1)
                        )
                    );
                }

                break;

            } else {

                if ($ipNo < $ipFrom) {
                    $high = $mid - 1;
                } else {
                    $low = $mid + 1;
                }

            }
        }

        return new Rych_Ip2Location_Record($recordData);
    }

    /**
     * Find the position in the database where the requested feature is stored.
     *
     * Uses the $_dbPositions member to perform the lookup.
     *
     * @param   string  $column
     * @return  integer
     */
    protected function _getDbPosition($feature)
    {
        return $this->_dbPositions[$feature][$this->dbType - 1];
    }

    /**
     * Unpack the given binary data using the supplied format.
     *
     * @param   string  $format
     * @param   string  $data
     * @return  array
     */
    protected function _unpack($format, $data)
    {
        $array = unpack($format, $data);

        list ($endianTest) = array_values(unpack('L1L', pack('V', 1)));
        if (1 == $endianTest) {
            return $array;
        }

        $values = array_values($array);
        $i = 0;
        foreach (explode('/', $format) as $value) {
            $repeater = (int) substr($value, 1);
            if ($repeater == 0) {
                $repeater = 1;
            }
            if ($value{1} == '*') {
                $repeater = count($array) - $i;
            }
            if ($value{0} != 'd') {
                $i += $repeater;
                continue;
            }
            $j = $i + $repeater;
            for ($a = $i; $a < $j; ++$a) {
               $p = pack('d', $values[$i]);
               $p = strrev($p);
               list ($values[$i]) = array_values(unpack('d1d', $p));
               ++$i;
            }
        }
        $a = 0;
        foreach (array_keys($array) as $key) {
            $array[$key] = $values[$a];
            ++$a;
        }
        return $array;
    }

    /**
     * @param   integer $pos
     * @return  string
     */
    protected function _read8($pos)
    {
        fseek($this->_dbHandle, $pos - 1, SEEK_SET);
        $data = fread($this->_dbHandle, 1);
        $adata = $this->_unpack('C', $data);
        return $adata[1];
    }

    /**
     * @param   integer $pos
     * @return  integer
     */
    protected function _read32($pos)
    {
        fseek($this->_dbHandle, $pos - 1, SEEK_SET);
        $data = fread($this->_dbHandle, 4);
        $adata = $this->_unpack('V', $data);
        if ($adata[1] < 0) {
            $adata[1] += 4294967296;
        }
        return (int) $adata[1];
    }

    /**
     * @param   integer $pos
     * @return  string
     */
    protected function _read128($pos)
    {
        fseek($this->_dbHandle, $pos - 1, SEEK_SET);
        $data = fread($this->_dbHandle, 16);

        $array = preg_split('//', $data, -1, PREG_SPLIT_NO_EMPTY);
        if (count($array) != 16) {
            return 0;
        }

        $ip96_127 = $this->_unpack(
            'V',
            $array[0] . $array[1] . $array[2] . $array[3]
        );
        $ip64_95 = $this->_unpack(
            'V',
            $array[4] . $array[5] . $array[6] . $array[7]
        );
        $ip32_63 = $this->_unpack(
            'V',
            $array[8] . $array[9] . $array[10] . $array[11]
        );
        $ip1_31 = $this->_unpack(
            'V',
            $array[12] . $array[13] . $array[14] . $array[15]
        );

        if ($ip96_127[1] < 0) {
            $ip96_127[1] += 4294967296;
        }
        if ($ip64_95[1] < 0) {
            $ip64_95[1] += 4294967296;
        }
        if ($ip32_63[1] < 0) {
            $ip32_63[1] += 4294967296;
        }
        if ($ip1_31[1] < 0) {
            $ip1_31[1] += 4294967296;
        }

        $bcresult = bcadd(
            bcadd(
                bcmul($ip1_31[1], bcpow(4294967296, 3)),
                bcmul($ip32_63[1], bcpow(4294967296, 2))
            ),
            bcadd(
                bcmul($ip64_95[1], 4294967296),
                $ip96_127[1]
            )
        );
        return $bcresult;
    }

    /**
     * @param   integer $pos
     * @return  string
     */
    protected function _readStr($pos)
    {
        fseek($this->_dbHandle, $pos, SEEK_SET);
        $size = fread($this->_dbHandle, 1);
        $adata = $this->_unpack('C', $size);
        $data = fread($this->_dbHandle, $adata[1]);
        return $data;
    }

    /**
     * @param   integer $pos
     * @return  string
     */
    protected function _readFloat($pos)
    {
        fseek($this->_dbHandle, $pos - 1, SEEK_SET);
        $data = fread($this->_dbHandle, 4);
        $adata = $this->_unpack('f', $data);
        return $adata[1];
    }

    /**
     * @param   string  $ipAddr
     * @return  boolean
     */
    protected function _isValidIp($ipAddr)
    {
        $validIp = true;
        if (!preg_match('/^[\d\.]+$/', $ipAddr)) {
            $validIp = false;
        } else if (preg_match('/^\.|\.\.|\.$/', $ipAddr)) {
            $validIp = false;
        }

        $ipParts = preg_split('/\./', $ipAddr);
        if ((1 > count($ipParts)) || (4 < count($ipParts))) {
            $validIp = false;
        }

        if ($validIp) {
            foreach ($ipParts as $ipPart) {
                if ($ipPart < 0 or $ipPart > 255) {
                    $validIp = false;
                    break;
                }
            }
        }

        return $validIp;
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

}