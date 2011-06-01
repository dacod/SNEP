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
 * Used to make loop in actions.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class ActionLoop extends PBX_Rule_Action {

    /**
     * Number of times we passed through this action
     *
     * @var int loop count
     */
    private $count;
    
    /**
     * @var Internacionalization
     */
    private $i18n;

    /**
     * Construtor
     */
    public function __construct() {
        $this->count = 0;
        $this->i18n = Zend_Registry::get("i18n");
    }

    /**
     * Action name
     * @return action name
     */
    public function getName() {
        return $this->i18n->translate("Loop");
    }

    /**
     * Version number
     * @return action version
     */
    public function getVersion() {
        return SNEP_VERSION;
    }

    /**
     * Action description
     * @return Action description
     */
    public function getDesc() {
        return $this->i18n->translate("Does a loop in actions.");
    }

    /**
     * @return String XML
     */
    public function getConfig() {
        $i18n  = $this->i18n;
        $loopcount  = (isset($this->config['loopcount']))?"<value>{$this->config['loopcount']}</value>":"";
        $actionindex = (isset($this->config['actionindex']))?"<value>{$this->config['actionindex']}</value>":"";

        $lbl_loopcount = $i18n->translate("Repeat:");
        $lbl_actionindex = $i18n->translate("Action index:");

        $unit = $i18n->translate("times");
        return <<<XML
<params>
    <int>
        <label>$lbl_loopcount</label>
        <id>loopcount</id>
        <default>5</default>
        <unit>$unit</unit>
        $loopcount
    </int>
    <int>
        <label>$lbl_actionindex</label>
        <id>actionindex</id>
        <default>0</default>
        $actionindex
    </int>
</params>
XML;
    }

    /**
     * Execute the action
     * 
     * @param Asterisk_AGI $asterisk
     * @param Asterisk_AGI_Request $request
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');
        $this->count++;

        if($this->count < $this->config['loopcount']) {
            throw new PBX_Rule_Action_Exception_GoTo($this->config['actionindex']);
        }
    }
}
