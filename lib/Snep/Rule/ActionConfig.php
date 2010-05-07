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
        $form = new Zend_Form();
        $form->setIsArray(true);
        $form->setElementsBelongTo($this->getActionId());
        $form->removeDecorator('form');
        $i18n = Zend_Registry::get('i18n');
        // Para cada elemento do XML
        foreach( $this->xml as $element ) {
            switch( $element->getName() ) {
                case 'string':
                    $parsed_element = $this->parseString($element);
                    break;
                case 'int':
                    $parsed_element = $this->parseInt($element);
                    break;
                case 'ramal':
                    $parsed_element = $this->parseRamal($element);
                    break;
                case 'tronco':
                    $parsed_element = $this->parseTronco($element);
                    break;
                case 'radio':
                    $parsed_element = $this->parseRadio($element);
                    break;
                case 'ccustos':
                    $parsed_element = $this->parseCCustos($element);
                    break;
                case 'boolean':
                    $parsed_element = $this->parseBoolean($element);
                    break;
                case 'queue':
                    $parsed_element = $this->parseQueue($element);
                    break;
                default:
                    $parsed_element = $this->parseString($element);
            }
            $parsed_element->getDecorator('errors')->setOption('placement','PREPEND');
            $form->addElement($parsed_element);
        }
        $this->form = $form;
        return $form;
    }

}