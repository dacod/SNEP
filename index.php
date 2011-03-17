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
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

// Add standard library to the include path
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/lib',
    get_include_path(),
)));

// Initializing Snep Config
require_once "Snep/Config.php";
Snep_Config::setConfigFile(APPLICATION_PATH . '/includes/setup.conf');

$config = Snep_Config::getConfig();

defined('SNEP_VENDOR') || define('SNEP_VENDOR', $config->ambiente->emp_nome);
defined('SNEP_VERSION') || define('SNEP_VERSION', file_get_contents(APPLICATION_PATH . "/configs/snep_version"));

// Define application environment
$snep_env = Snep_Config::getConfig()->system->debug ? "development" : "production";
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : $snep_env));

if (APPLICATION_ENV === "development") {
    require_once "Zend/Debug.php";
}

// Adds the modules directory to the snep module system
require_once "Snep/Modules.php";
Snep_Modules::getInstance()->addPath(APPLICATION_PATH . "/modules");

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Config/Ini.php';

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/application.ini');

// Adding standard lib autoloader capabilities to keep old code running
$application->setAutoloaderNamespaces(array("Asterisk_", "PBX_", "Snep_"));

// Keeping old links to avoid rework in too much stuff.
require_once "Zend/Registry.php";
Zend_Registry::set("config", $config);
Zend_Registry::set("db", Snep_Db::getInstance());

/* Fight! */
$application->bootstrap()->run();
