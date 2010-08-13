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
 * Executa uma aplicação no Asterisk
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_ExecuteApp extends PBX_Rule_Action {

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     * @return Nome da Ação
     */
    public function getName() {
        return "Executar Aplicação";
    }

    /**
     * Retorna o numero da versão da classe.
     * @return Versão da classe
     */
    public function getVersion() {
        return Zend_Registry::get("snep_version");
    }

    /**
     * Retorna uma breve descrição de funcionamento da ação.
     * @return Descrição de funcionamento ou objetivo
     */
    public function getDesc() {
        return "Executa uma aplicação do Asterisk";
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $application  = (isset($this->config['application']))?"<value>{$this->config['application']}</value>":"";
        if( isset($this->config['parameters']) ) {
            $parameters = str_replace(array("<",">"), array("&lt;", "&gt;"), $this->config['parameters']);
            $parameters = "<value>$parameters</value>";
        }
        else {
            $parameters = "";
        }

        return <<<XML
<params>
    <string>
        <label>Aplicação</label>
        <id>application</id>
        $application
    </string>
    <string>
        <label>Parâmetros</label>
        <id>parameters</id>
        $parameters
    </string>
</params>
XML;
    }

    /**
     * Executa a ação.
     * @param Asterisk_AGI $asterisk
     * @param PBX_Asterisk_AGI_Request $request
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');

        $application = $this->config['application'];
        $parameters  = $this->config['parameters'];

        $log->info("Executing application: $application($parameters)");
        $return = $asterisk->exec($application, $parameters);
        if($return['result'] == "-2") {
            $log->err("Falha ao executar aplicacao $application. Retorno: {$return['data']}");
        }
    }
}
