<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
 * Classe para facilitar o controle de filas no sistema
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class Snep_Queues {

    private $queues = array();

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

    public function register( $queue ) {
        if( in_array($queue, $this->queues) ) {
            throw new Exception("Queue already registered");
        }
        else {
            $this->queues[] = $queue;
        }
    }

    /**
     * Retorna um array com todas as filas.
     *
     * @return array
     */
    public function getQueues() {
        return $this->queues;
    }

    /**
     * Verifica se uma fila está registrada.
     *
     * @param string $queue
     * @return boolean
     */
    public function isRegistered($queue) {
        if( in_array($queue, $this->getCCustos()) ) {
            return true;
        }
        else {
            return false;
        }
    }
}
