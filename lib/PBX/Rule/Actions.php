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

/**
 * Ações do Snep
 *
 * Classe que facilita e abstrai o controle das ações instaladas no sistema.
 *
 * @category  Snep
 * @package   PBX_Rule
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Rule_Actions {

    private $actions = array();

    private static $instance;

    /**
     * Retorna instancia dessa classe
     *
     * @return PBX_Rule_Actions
     */
    public static function getInstance() {
        if( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor
     */
    private function __construct() {}
    private function  __clone() {}

    public function registerAction( $action ) {
        if( in_array($action, $this->actions) ) {
            throw new Exception("Action already registered");
        }
        else {
            $this->actions[] = $action;
        }
    }

    /**
     * Retorna um array com todas as ações instaladas no sistema.
     *
     * @return array $actions ações instaladas no sistema
     */
    public function getInstalledActions() {
        return $this->actions;
    }
}
