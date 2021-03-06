<?php
/**
 * Ryan Chouinard's Components
 *
 * @category    Rych
 * @package     Rych
 * @subpackage  UnitTests
 * @author      Ryan Chouinard <rchouinard@gmail.com>
 * @copyright   Copyright (c) 2010 Ryan Chouinard ({@link http://ryanchouinard.com})
 * @license     New BSD License
 * @version     $Id$
 */

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('date.timezone', 'America/New_York');

defined('LIBRARY_ROOT') ||
    define('LIBRARY_ROOT', realpath(dirname(__FILE__) . '/../'));

set_include_path(implode(PATH_SEPARATOR, array (
    realpath(LIBRARY_ROOT . '/tests/library'),
    realpath(LIBRARY_ROOT . '/library'),
    get_include_path()
)));