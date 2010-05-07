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
 * Classe para facilitar o controle de centro de custos ao longo do sistema
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class Snep_CentroCustos {

    private $ccustos = array();

    private static $instance;

    /**
     * Retorna instancia dessa classe
     *
     * @return Snep_CentroCustos
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

    public function register( $ccusto ) {
        if( in_array($ccusto, $this->ccustos) ) {
            throw new Exception("Centro de Custo already registered");
        }
        else {
            $this->ccustos[] = $ccusto;
        }
    }

    /**
     * Retorna um array com todos os ccustos registrados.
     *
     * @return array
     */
    public function getCCustos() {
        return $this->ccustos;
    }

    /**
     * Verifica se o nome de uma classe foi registrada no sistema.
     *
     * @param string $ccusto
     * @return boolean
     */
    public function isRegistered($ccusto) {
        if( in_array($ccusto, $this->getCCustos()) ) {
            return true;
        }
        else {
            return false;
        }
    }
}
