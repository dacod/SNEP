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
 * ActionLooop Loop em Ações
 *
 * Ação que faz possível um loop finito na execução de ações nas regras de
 * negócio.
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_ActionLoop extends PBX_Rule_Action {

    /**
     * Contagem de vezes em que essa ação foi chamada.
     *
     * @var int loop count
     */
    private $count;
    
    /**
     * @var Internacionalização
     */
    private $i18n;

    /**
     * Construtor
     * @param array $config configurações da ação
     */
    public function __construct() {
        $this->count = 0;
        $this->i18n = Zend_Registry::get("i18n");
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Loop");
    }

    /**
     * Retorna o numero da versão da classe.
     * @return Versão da classe
     */
    public function getVersion() {
        return "1.0";
    }

    /**
     * Retorna uma breve descrição de funcionamento da ação.
     * @return Descrição de funcionamento ou objetivo
     */
    public function getDesc() {
        return $this->i18n->translate("Faz um loop na execução de ações.");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $i18n  = $this->i18n;
        $loopcount  = (isset($this->config['loopcount']))?"<value>{$this->config['loopcount']}</value>":"";
        $actionindex = (isset($this->config['actionindex']))?"<value>{$this->config['actionindex']}</value>":"";

        $lbl_loopcount = $i18n->translate("Repetir:");
        $lbl_actionindex = $i18n->translate("Indice da ação:");

        $unit = $i18n->translate("vezes");
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
     * Executa a ação.
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
