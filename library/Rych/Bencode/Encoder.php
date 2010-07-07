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
 * @category    Rych
 * @package     Rych_Bencode
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @license     New BSD License
 */
class Rych_Bencode_Encoder
{

    /**
     * @var     mixed
     */
    protected $_data;

    /**
     * @param   array   $data
     * @return  void
     */
    protected function __construct($data)
    {
        $this->_data = $data;
    }

    /**
     * @param   mixed   $data
     * @return  string
     * @throws  Rych_Bencode_Exception
     */
    static public function encode($data)
    {
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else {
                $data = (array) $data;
            }
        }

        $encoder = new self($data);
        return $encoder->_encode();
    }

    /**
     * @return  string
     */
    protected function _encode($data = null)
    {
        $data = is_null($data) ? $this->_data : $data;

        if (is_array ($data) && (isset ($data[0]) || empty ($data))) {
            return $this->_encodeList($data);
        } else if (is_array($data)) {
            return $this->_encodeDict($data);
        } else if (is_numeric($data)) {
            $data = sprintf('%.0f', round($data, 0));
            return $this->_encodeInteger($data);
        } else {
            return $this->_encodeString($data);
        }
    }

    /**
     * @return  string
     */
    protected function _encodeInteger($data = null)
    {
        $data = is_null($data) ? $this->_data : $data;
        return sprintf('i%se', $data);
    }

    /**
     * @return  string
     */
    protected function _encodeString($data = null)
    {
        $data = is_null($data) ? $this->_data : $data;
        return sprintf('%d:%s', strlen($data), $data);
    }

    /**
     * @return  string
     */
    protected function _encodeList($data = null)
    {
        $data = is_null($data) ? $this->_data : $data;

        $list = '';
        foreach ($data as $item) {
            $list .= self::encode($item);
        }
        return "l{$list}e";
    }

    /**
     * @return  string
     */
    protected function _encodeDict($data = null)
    {
        $data = is_null($data) ? $this->_data : $data;
        ksort($data);

        $dict = '';
        foreach ($data as $key => $value) {

            $key = $this->_encodeString($key);
            $value = $this->_encode($value);

            $dict .= "{$key}{$value}";
        }

        return "d{$dict}e";
    }

}