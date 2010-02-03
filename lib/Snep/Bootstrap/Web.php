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

    private function startI18N() {
        $config = Zend_Registry::get('config');
        $locale = $config->ambiente->language;
        require_once $config->system->path->base . "/configs/langs/$locale.php";
        Zend_Registry::set("lang", $LANG);
    }

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

    protected function startMenu() {
        Zend_Registry::set("menu", new Snep_Menu("../configs/menu.xml"));
    }

    protected function startModules() {
        parent::startModules();
        $modules = Snep_Modules::getInstance();
        $registered_modules = $modules->getRegisteredModules();
        $menu = Zend_Registry::get('menu');

        /**
         * Adiciona os menus dos modulos no menu do Snep
         */
        foreach ($registered_modules as $module) {
            foreach ($module->getMenuTree() as $key => $menuItem) {
                switch((string) $key) {
                    case 'status':
                    case 'register':
                    case 'reports':
                    case 'configs':
                    case 'routing':
                    case 'billing':
                        $menu->getItemById($key)->addSubmenuItem($menuItem);
                        break;
                    default:
                        $menu->addItem($menuItem);
                }
            }
        }
    }

    public function boot() {
        $this->startAutoLoader();
        $this->startLocale();
        $this->startI18N(); // Inicia o antigo $LANG do Snep para compactibilidade
        $this->startLogger();
        $this->startDatabase();
        $this->startMenu(); // Carrega o menu padrão do Snep
        $this->startModules(); // Inicia os modulos instalados
        $this->startActions(); // Registra as ações do sistema e as ações dos Módulos
    }

}

