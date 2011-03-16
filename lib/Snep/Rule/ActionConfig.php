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

require_once "PBX/Rule/ActionConfig.php";

/**
 * Modificação do ActionConfig da lib PBX para uso com a interface Rica das
 * regras de negócio do snep.
 *
 * @category  Snep
 * @package   Snep_Rule
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Rule_ActionConfig extends PBX_Rule_ActionConfig {

    protected $actionId = "";

    public function getActionId() {
        return $this->actionId;
    }

    public function setActionId($action_id) {
        $this->actionId = $action_id;
    }

    public function __construct($xml) {
        parent::__construct($xml);
    }
    
    /**
     * Faz o parse do XML e gera o formulário.
     */
    protected function parseForm() {
        $form = parent::parseForm();
        $form->setElementsBelongTo($this->getActionId());
        
        return $form;
    }

}