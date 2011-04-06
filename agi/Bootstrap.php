<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as
 *  published by the Free Software Foundation, either version 3 of
 *  the License, or (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 */
// Required for signal handling below
declare(ticks = 1);
/* Ignoring closing events, this should make the script works like
 * DeadAgi in newer asterisks.
 */
pcntl_signal(SIGHUP, SIG_IGN);
pcntl_signal(SIGTERM, SIG_IGN);
pcntl_signal(SIGINT, SIG_IGN); // DO NOT FORK! Or the zombies will attack.

set_time_limit(0);

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . "/../"));

// Add standard library to the include path
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/lib',
    get_include_path(),
)));

require_once "Snep/Config.php";
require_once "Snep/Logger.php";
require_once "Snep/Locale.php";
require_once "Snep/Modules.php";
require_once "PBX/Asterisk/AGI.php";
require_once "PBX/Asterisk/Log/Writer.php";
require_once 'Zend/Log/Writer/Stream.php';

/**
 * AGI Bootstrap
 *
 * This makes a bootstrap preparing the agi environment ready for AGI scripts.
 *
 * Its designed to allow the agi scripts to work in a script fashion, so it will
 * not block execution and will allow the rest of the code to execute normaly.
 *
 * @category  Snep
 * @package   AGI
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Bootstrap {

    public function __construct() {
        try {
            Snep_Config::setConfigFile(APPLICATION_PATH . '/includes/setup.conf');
        } catch (Exception $ex) {
            fwrite(STDERR, sprintf($ex->getMessage()));
            exit(1);
        }

        $this->startLogger();

        defined('SNEP_VERSION') || define('SNEP_VERSION', file_get_contents(APPLICATION_PATH . "/configs/snep_version"));
        
        $this->startLocale();
        $this->startModules();
        $this->updateRequest();

        require_once "Zend/Loader/Autoloader.php";
        Zend_Loader_Autoloader::getInstance()->registerNamespace(array("Snep_", "PBX_", "Asterisk_"));
    }

    protected function startLocale() {
        // Preciso melhorar isso
        Snep_Locale::getInstance();
        Zend_Registry::set("i18n", $locale->getZendTranslate());
    }

    protected function startModules() {
        Snep_Modules::getInstance()->addPath(Snep_Config::getConfig()->system->path->base . "/modules");
    }

    protected function updateRequest() {
        require_once "PBX/Asterisk/AGI/Request.php";
        $asterisk = PBX_Asterisk_AGI::getInstance();
        $asterisk->requestObj = new PBX_Asterisk_AGI_Request($asterisk->request);
    }

    protected function startLogger() {
        $asterisk = PBX_Asterisk_AGI::getInstance();
        $config = Snep_Config::getConfig();
        $log = Snep_Logger::getInstance();
        Zend_Registry::set("log", $log);

        // Log em arquivo
        $writer = new Zend_Log_Writer_Stream($config->system->path->log . '/agi.log');
        $format = sprintf("%%timestamp%% - %s -> %s - %%priorityName%% (%%priority%%):%%message%%", $asterisk->request['agi_callerid'], $asterisk->request['agi_extension']) . PHP_EOL;
        $formatter = new Zend_Log_Formatter_Simple($format);
        $writer->setFormatter($formatter);
        $log->addWriter($writer);

        // Log no console do Asterisk
        $console_writer = new PBX_Asterisk_Log_Writer($asterisk);
        $format = sprintf("%s -> %s %%priorityName%% (%%priority%%):%%message%%", $asterisk->request['agi_callerid'], $asterisk->request['agi_extension']) . PHP_EOL;
        $console_formatter = new Zend_Log_Formatter_Simple($format . PHP_EOL);
        $console_writer->setFormatter($console_formatter);
        $log->addWriter($console_writer);

        if (!$config->system->debug) {
            $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::NOTICE));
            $console_writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::INFO));
        }
    }

}
