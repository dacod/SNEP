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
 * Set a cost center to the call
 *
 * Marks the call with a Cost Center for CDR management.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class CostCenter extends PBX_Rule_Action {

    private $i18n;

    public function __construct() {
        $this->i18n = Zend_Registry::get("i18n");
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->i18n->translate("Define Cost Center");
    }

    /**
     * @return string
     */
    public function getVersion() {
        return SNEP_VERSION;
    }

    /**
     * @return string
     */
    public function getDesc() {
        return $this->i18n->translate("Marks the call with a cost center.");
    }

    /**
     * @return string XML
     */
    public function getConfig() {
        $ccustos = (isset($this->config['ccustos']))?"<value>{$this->config['ccustos']}</value>":"";

        return <<<XML
<params>
    <ccustos>
        <id>ccustos</id>
        $ccustos
    </ccustos>
</params>
XML;
    }

    /**
     * Execute the action
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');

        $log->info("Definindo centro de custos para {$this->config['ccustos']}.");
        $asterisk->set_variable('CDR(accountcode)', $this->config['ccustos']);
    }
}
