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

require_once "Zend/Registry.php";
require_once "Zend/Log.php";
require_once "Zend/Config/Ini.php";

/**
 * Bootstrap para o Snep
 *
 * Classe que prepara o ambiente para a execução do Snep PBX
 *
 * @category  Snep
 * @package   Snep_Bootstrap
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
abstract class Snep_Bootstrap {

    protected $configFile;

    protected $config;

    protected $autoloader;

    public function __construct( $configFile = null ) {
        Zend_Registry::set('log', new Zend_Log());
        if( $configFile === null ) {
            $this->setConfigFile("/etc/snep.conf");
        }
        else {
            $this->setConfigFile($configFile);
        }
        Zend_Registry::set('snep_version', file_get_contents($this->config->system->path->base . "/configs/snep_version"));
        Zend_Registry::set('vendor', $this->config->ambiente->emp_nome);
    }

    protected function startLocale() {
        require_once "Zend/Translate.php";
        // silenciando strict até arrumar zend_locale
        date_default_timezone_set("America/Sao_Paulo");

        $i18n = new Zend_Translate('gettext', $this->config->system->path->base . '/lang/pt_BR.mo', 'pt_BR');
        Zend_Registry::set('i18n', $i18n);

        $translation_files = $this->config->system->path->base . "/lang/";
        foreach( scandir($translation_files) as $filename ) {
            // Todos os arquivos .php devem ser classes de descrição de modulos
            if( preg_match("/.*\.mo$/", $filename) ) {
                $translation_id = basename($filename, '.mo');
                if($translation_id != "pt_BR") {
                    $i18n->addTranslation($translation_files . "/$filename", $translation_id);
                }
            }
        }

        require_once "Zend/Locale.php";

        if(Zend_Locale::isLocale($this->config->system->locale)) {
            $locale = $this->config->system->locale;
        }
        else {
            $locale = "pt_BR";
        }

        Zend_Registry::set('Zend_Locale', new Zend_Locale($locale));
        Zend_Locale::setDefault($locale);
        Zend_Locale_Format::setOptions(array("locale"=> $locale));
        $i18n->setLocale($locale);
        Zend_Registry::set("Zend_Translate", $i18n);

        $zend_validate_translator = new Zend_Translate_Adapter_Array(
            $this->config->system->path->base . "/lang/Zend_Validate/$locale/Zend_Validate.php",
            $locale
        );
        Zend_Validate_Abstract::setDefaultTranslator($zend_validate_translator);
    }

    public function getConfigFile() {
        return $this->configFile;
    }

    protected function setConfigFile($configFile) {
        if( file_exists($configFile) ) {
            $this->configFile = $configFile;
            $config = new Zend_Config_Ini($configFile, null, true);
            Zend_Registry::set('configFile', $configFile);
            Zend_Registry::set('config', $config);

            // Verificando a existencia dos caminhos do sistema
            if( !isset($config->system->path->asterisk) ) {
                $config->system->path->asterisk = new Zend_Config(array(), true);
            }
            if( !isset($config->system->path->asterisk->conf) ) {
                $config->system->path->asterisk->conf = "/etc/asterisk";
            }
            if( !isset($config->system->path->asterisk->sounds) ) {
                $config->system->path->asterisk->sounds = "/var/lib/asterisk/sounds";
            }
            if( !isset($config->system->path->asterisk->moh) ) {
                $config->system->path->asterisk->moh = "/var/lib/asterisk/moh";
            }
            if( !isset($config->system->path->hylafax) ) {
                $config->system->path->hylafax = "/var/spool/hylafax";
            }

            $config->setReadOnly();

            $this->config = $config;
        }
        else {
            throw new Exception("Fatal Error: configuration file not found: $configFile");
        }
    }

    protected function startAutoLoader() {
        require_once "Zend/Loader/Autoloader.php";
        $this->autoloader = Zend_Loader_Autoloader::getInstance();
        $this->registerNameSpaces();
    }

    protected function startDatabase() {
        $config = $this->config->ambiente->db->toArray();
        $config["driver_options"] = array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
        $db = Zend_Db::factory('Pdo_Mysql', $config);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
        try {
            $db->getConnection();
        }
        catch(Zend_Db_Adapter_Exception $ex ) {
            echo "Falha ao inicializar banco de dados: " . $ex->getMessage();
            exit(1);
        }
    }

    protected function startModules() {
        $modules_dir = $this->config->system->path->base . "/modules/";

        require_once "Snep/Modules.php";
        $modules = Snep_Modules::getInstance();

        foreach( scandir($modules_dir) as $filename ) {
            // Todos os arquivos .php devem ser classes de descrição de modulos
            if( preg_match("/.*\.php$/", $filename) ) {
                require_once $modules_dir . "/" . $filename;
                $classname = basename($filename, '.php');
                if(class_exists($classname)) {
                    $module = new $classname();
                    $modules->registerModule($module);
                }
            }
        }
    }

    protected function startActions() {
        $config = Zend_Registry::get('config');

        $actions_dir = $config->system->path->base . "/lib/PBX/Rule/Action";

        $actions = PBX_Rule_Actions::getInstance();

        foreach( scandir($actions_dir) as $filename ) {
            // Todos os arquivos .php devem ser classes de Ações
            if( preg_match("/.*\.php$/", $filename) ) {
                // Tentar instanciar e Adicionar no array
                $classname = 'PBX_Rule_Action_' . basename($filename, '.php');
                if(class_exists($classname)) {
                    $actions->registerAction($classname);
                }
            }
        }

        foreach (Snep_Modules::getInstance()->getRegisteredModules() as $module) {
            if($module->getModuleId() != null) {
                $actions_dir = $config->system->path->base . "/modules/" . $module->getModuleDir() . "/actions";
            }
            else {
                $actions_dir = $config->system->path->base . "/" . $module->getModuleDir() . "/actions";
            }
            if( file_exists($actions_dir) ) {
                foreach( scandir($actions_dir) as $filename ) {
                    // Todos os arquivos .php devem ser classes de Ações
                    if( preg_match("/.*\.php$/", $filename) ) {
                        // Tentar instanciar e Adicionar no array
                        require_once $actions_dir . "/" . $filename;
                        $classname = basename($filename, '.php');
                        if(class_exists($classname)) {
                            $actions->registerAction($classname);
                        }
                    }
                }
            }
        }
    }

    protected function registerCCustos() {
        $db = Zend_Registry::get("db");
        $ccustos = Snep_CentroCustos::getInstance();

        $select = $db->select()
                  ->from('ccustos')
                  ->order("codigo");

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();

        foreach($result as $ccusto) {
            $ccustos->register(array("codigo" => $ccusto['codigo'], "nome" => $ccusto['nome']));
        }
    }

    protected function registerQueues() {
        $db = Zend_Registry::get("db");
        $queues = Snep_Queues::getInstance();

        $select = $db->select()->from('queues');

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();

        foreach($result as $queue) {
            $queues->register($queue['name']);
        }
    }

    /**
     * Registra no autoloader os namespaces necessários para o ambiente Snep
     */
    protected function registerNameSpaces() {
        $autoloader = $this->autoloader;
        if( $autoloader !== null ) {
            $autoloader->registerNamespace('Snep_');
            $autoloader->registerNamespace('PBX_');
            $autoloader->registerNamespace('Asterisk_');
        }
    }

}

