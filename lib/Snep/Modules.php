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

/**
 * Controle de módulos instalados no sistema.
 *
 *
 * @category  Snep
 * @package   Snep_Module
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Modules {

    /**
     * Módulos registrados no sistema.
     *
     * @var array
     */
    private $registeredModules = array();

    private static $instance;

    private function __construct() { /*Singleton*/ }
    private function __clone() { /*Singleton*/ }

    /**
     * Retorna a instancia dessa classe com os módulos registrados
     *
     * @return Snep_Modules instance
     */
    public static function getInstance() {
        if( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Retorna todos os modulos registrados no sistema
     *
     * @return Snep_Module_Descriptor[]
     */
    public function getRegisteredModules() {
        return $this->registeredModules;
    }

    /**
     * Registra um modulo no sistema
     *
     * @param Snep_Module_Descriptor $module
     */
    public function registerModule( Snep_Module_Descriptor $module ) {
        if( in_array($module, $this->registeredModules) ) {
            throw new Snep_Module_Exception_AlreadyRegistered();
        }
        else {
            $this->registeredModules[] = $module;
        }
    }

}
