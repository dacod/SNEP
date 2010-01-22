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
 * Classe que prepara o ambiente para a execução do Snep PBX na Web
 *
 * @category  Snep
 * @package   Snep_Bootstrap
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Bootstrap_Web extends Snep_Bootstrap {

    private function startLogger() {
        $log = Zend_Registry::get('log');
        
        $writer = new Zend_Log_Writer_Stream($this->config->system->path->log . '/ui.log');
        // Filtramos a 'sujeira' dos logs se não estamos em debug mode.
        if(!$this->config->system->debug) {
            $filter = new Zend_Log_Filter_Priority(Zend_Log::WARN);
            $writer->addFilter($filter);
        }
        $log->addWriter($writer);
    }

    public function boot() {
        $this->startAutoLoader();
        $this->startLocale();
        $this->startLogger();
        $this->startDatabase();
        $this->startModules();
        $this->startActions();
    }

}

