<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */

// Define path to application directory
defined('APPLICATION_PATH') ||
    define('APPLICATION_PATH', realpath(dirname(__FILE__)));

$config = parse_ini_file(APPLICATION_PATH . '/includes/setup.conf');
$snep_env = $config['debug'] ? "development" : "production";

// Define application environment
defined('APPLICATION_ENV') ||
    define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : $snep_env));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/lib'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Config/Ini.php';

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/includes/application.ini');

// Autoloader é necessário para o ambiente do snep.
$application->setAutoloaderNamespaces(array("Asterisk_", "PBX_", "Snep_"));

$application->bootstrap()->run();