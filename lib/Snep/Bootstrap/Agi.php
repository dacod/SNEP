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
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Bootstrap_Agi extends Snep_Bootstrap {

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

    public function boot() {
        // Iniciando ambiente para ideal funcionamento da Lib
        $this->startAutoLoader();
        $this->startLocale();

        // Coletando informações enviadas pelo asterisk
        $this->startAsterisk();

        // Iniciando logs do sistema
        $this->startLogger();

        // Iniciando objeto para comunicação com banco de dados
        $this->startDatabase();

        // Atualizando request para facilitar trabalho das ações
        $this->updateRequest();

        // Iniciando modulos e Ações das regras de negócio
        $this->startModules();
        $this->startActions();
    }

}
