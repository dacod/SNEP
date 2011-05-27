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

require_once "Snep/Bootstrap.php";

/**
 * Bootstrap para o Snep
 *
 * Classe que prepara o ambiente para a execução do Snep PBX em AGI
 *
 * @category  Snep
 * @package   Snep_Bootstrap
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Bootstrap_Agi extends Snep_Bootstrap {

    private $rulePlugins;

    public function getRulePlugins() {
        return $this->rulePlugins->getPlugins();
    }

    private function startAsterisk() {
        $agiconfig['debug'] = false;
        $agiconfig['error_handler'] = false;
        $asterisk = new Asterisk_AGI( null, $agiconfig );
        Zend_Registry::set("asterisk", $asterisk);
    }

    private function updateRequest() {
        $asterisk = Zend_Registry::get('asterisk');
        $request = new PBX_Asterisk_AGI_Request($asterisk->request);
        $asterisk->requestObj = $request;
    }

    private function startLogger() {
        $log = Zend_Registry::get('log');
        $asterisk = Zend_Registry::get('asterisk');
        $request = $asterisk->request;

        // Log em arquivo
        $writer = new Zend_Log_Writer_Stream($this->config->system->path->log . '/agi.log');
        $format = "%timestamp% - {$request['agi_callerid']}- -> {$request['agi_extension']} %priorityName% (%priority%):%message%";
        $formatter = new Zend_Log_Formatter_Simple($format . PHP_EOL);
        $writer->setFormatter($formatter);
        $log->addWriter($writer);

        // Log no console do Asterisk
        $console_writer = new PBX_Asterisk_Log_Writer($asterisk);
        $format = "{$asterisk->request['agi_callerid']} -> {$asterisk->request['agi_extension']} %priorityName% (%priority%):%message%";
        $console_formatter = new Zend_Log_Formatter_Simple($format . PHP_EOL);
        $console_writer->setFormatter($console_formatter);
        $log->addWriter($console_writer);

        if(!$this->config->system->debug) {
            $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::NOTICE));
            $console_writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::INFO));
        }
    }

    protected function startRulePlugins() {
        $config = Zend_Registry::get('config');
        $log = Zend_Registry::get('log');
        $this->rulePlugins = new PBX_Rule_Plugin_Broker();

        // Registrando plugin de tempo limite
        $this->rulePlugins->registerPlugin(new Snep_Rule_Plugin_TimeLimit());

        foreach (Snep_Modules::getInstance()->getRegisteredModules() as $module) {
            $plugins_dir = $config->system->path->base . "/" . $module->getModuleDir() . "/rule_plugins";
            if( file_exists($plugins_dir) ) {
                $plugins = "";
                foreach( scandir($plugins_dir) as $filename ) {
                    // Todos os arquivos .php devem ser classes de Plugins
                    if( preg_match("/.*\.php$/", $filename) ) {
                        // Tentar instanciar e Adicionar no array
                        require_once $plugins_dir . "/" . $filename;
                        $classname = basename($filename, '.php');
                        $plugins .= " " . $classname;
                        if(class_exists($classname)) {
                            $this->rulePlugins->registerPlugin(new $classname);
                        }
                    }
                }
                $log->debug("Plugins de regras: " . trim($plugins));
            }
        }
    }

    public function boot() {
        set_time_limit(0);
        // Iniciando ambiente para ideal funcionamento da Lib
        $this->startAutoLoader();
        $this->startLocale();

        // Coletando informações enviadas pelo asterisk
        $this->startAsterisk();

        // Iniciando logs do sistema
        $this->startLogger();

        // Iniciando objeto para comunicação com banco de dados
        Snep_Db::getInstance();

        $this->registerCCustos();
        $this->registerQueues();

        // Atualizando request para facilitar trabalho das ações
        $this->updateRequest();

        // Iniciando modulos e Actions das regras de negócio
        $this->startModules();
        $this->startActions();

        // Coletando plugins para regras de negócio
        $this->startRulePlugins();
    }

}
