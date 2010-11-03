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
 * Plugin para limitar tempo de ramais e troncos diario/mensal/anual
 *
 * @category  Snep
 * @package   Snep_Rule_Plugin
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Rule_Plugin_TimeLimit extends PBX_Rule_Plugin {

    /**
     * @var Snep_Rule_Plugin_TimeLimit_OldController
     */
    protected $extensionController;
    /**
     * @var Snep_Rule_Plugin_TimeLimit_OldController
     */
    protected $trunkController;

    /**
     * Verifica se um tronco tem permissão de efetuar ligação baseado no seu
     * tempo.
     *
     * @param int $id
     * @return boolean
     */
    protected function trunkIsAllowed($id) {
        $log = Zend_Registry::get('log');
        $db = Zend_Registry::get('db');

        $controller = new Snep_Rule_Plugin_TimeLimit_OldController($id, "T", $db);
        $this->trunkController = $controller;
        return $controller->status == "allow" ? true : false;
    }

    /**
     * Verifica se um ramal tem permissão para efetuar ligações baseado no seu
     * saldo de tempo.
     *
     * @param int $id
     * @return boolean
     */
    protected function extensionIsAllowed($id) {
        $log = Zend_Registry::get('log');
        $db = Zend_Registry::get('db');

        $controller = new Snep_Rule_Plugin_TimeLimit_OldController($id, "R", $db);
        $this->extensionController = $controller;
        return $controller->status == "allow" ? true : false;
    }

    /**
     * Verificamos antes de cada ação se um tronco será usado e se o
     * ramal/tronco tem permissão de fazer essa ligação
     *
     * @param int $index
     */
    public function preExecute($index) {
        $action = $this->rule->getAction($index);

        if ($action instanceof PBX_Rule_Action_DiscarTronco) {
            $log = Zend_Registry::get('log');
            $config = $action->getConfigArray();
            $allowed = $this->trunkIsAllowed($config['tronco']);

            $requester = $this->asterisk->requestObj->getSrcObj();
            if ($requester instanceof Snep_Exten) {
                $allowed = $allowed && $this->extensionIsAllowed($requester->getNumero());
            }

            if (!$allowed) {
                throw new PBX_Rule_Action_Exception_GoTo($index + 1);
            }
        }
    }

    /**
     * A cada ação verificamos se alguma atualização nos tempos deve ser
     * computada e se o bloqueio deve ser feito.
     *
     * @param integer $index
     */
    public function postExecute($index) {
        $action = $this->rule->getAction($index);

        // Somente as  ligações feitas através de troncos são contabilizadas.
        if ($action instanceof PBX_Rule_Action_DiscarTronco) {
            $log = Zend_Registry::get('log');
            $asterisk = $this->asterisk;

            // Tempo que será contabilizado
            $answered_time = $asterisk->get_variable("ANSWEREDTIME");
            $answered_time = (int) $answered_time['data'];

            if ($answered_time > 0) {
                $db = Zend_Registry::get("db");
                $this->trunkController->update($answered_time, $db);
                if ($this->extensionController !== null) {
                    $this->extensionController->update($answered_time, $db);
                }
            }
        }
    }

}
