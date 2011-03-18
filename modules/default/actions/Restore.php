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
 * Restaura a Origem/Destino de uma ligação.
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Restore extends PBX_Rule_Action {

    /**
     * @var Internacionalização
     */
    private $i18n;

    /**
     * Construtor
     * @param array $config configurações da ação
     */
    public function __construct() {
        $this->i18n = Zend_Registry::get("i18n");
    }

    /**
     * Define as configurações da ação
     * @param array $config
     */
    public function setConfig($config) {
        parent::setConfig($config);
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Restaurar Requisição");
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
        return $this->i18n->translate("Restaura a origem ou destino da ligação.");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $i18n = $this->i18n;
        $origem  = (isset($this->config['origem']))?"<value>{$this->config['origem']}</value>":"";
        $destino = (isset($this->config['destino']))?"<value>{$this->config['destino']}</value>":"";
        return <<<XML
<params>
    <boolean>
        <id>origem</id>
        <label>{$i18n->translate("Restaurar Origem")}</label>
        <default>false</default>
        $origem
    </boolean>
    <boolean>
        <id>destino</id>
        <label>{$i18n->translate("Restaurar Destino")}</label>
        <default>false</default>
        $destino
    </boolean>
</params>
XML;
    }

    /**
     * Executa a ação.
     * @param Asterisk_AGI $asterisk
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');
        $i18n = $this->i18n;
        if(isset($this->config['origem']) && $this->config['origem']) {
            $log->info(sprintf($i18n->translate("Restaurando origem para %s"), $request->getOriginalCallerid()));
            $request->origem = $request->getOriginalCallerid();
            $asterisk->set_callerid($request->origem);
        }
        if(isset($this->config['destino']) && $this->config['destino']) {
            $log->info(sprintf($i18n->translate("Restaurando destino para %s"), $request->getOriginalExtension()));
            $request->destino = $request->getOriginalExtension();
        }
    }
}
