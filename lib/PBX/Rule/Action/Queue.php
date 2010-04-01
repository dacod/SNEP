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
 * Enviar para Fila
 *
 * Ação que envia a ligação para uma fila de atendimento.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_Queue extends PBX_Rule_Action {

    /**
     * @var Internacionalização
     */
    private $i18n;

    /**
     * Construtor
     * @param array $config configurações da ação
     */
    public function __construct() {
        $path = Zend_Registry::get('config');
        // Especificando caminho para arquivo de tradução
        $this->i18n = new Zend_Translate('gettext', $path->system->path->base . "/lang/actions/" . get_class($this) . "/" . "pt_BR.mo" , 'pt_BR');
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     *
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Enviar para Fila");
    }

    /**
     * Retorna o numero da versão da classe.
     *
     * @return Versão da classe
     */
    public function getVersion() {
        return "1.0";
    }

    /**
     * Seta as configurações da ação.
     *
     * @param array $config configurações da ação
     */
    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * Retorna uma breve descrição de funcionamento da ação.
     * @return Descrição de funcionamento ou objetivo
     */
    public function getDesc() {
        return $this->i18n->translate("Envia a ligação para uma fila");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $queue = (isset($this->config['queue']))?"<value>{$this->config['queue']}</value>":"";
        $timeout = (isset($this->config['timeout']))?"<value>{$this->config['timeout']}</value>":"";

        return <<<XML
<params>
    <queue>
        <id>queue</id>
        $queue
    </queue>

    <int>
        <id>dial_limit_warn</id>
        <default>180</default>
        <label>{$i18n->translate("Timeout na fila")}</label>
        <size>4</size>
        <unit>{$i18n->translate("segundos")}</unit>
        $timeout
    </int>
</params>
XML;
    }

    /**
     * Executa a ação. É chamado dentro de uma instancia usando AGI.
     *
     * @param Asterisk_AGI $asterisk
     * @param Asterisk_AGI_Request $request
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');

        $asterisk->answer();
        $result = $asterisk->exec('Queue', array($this->config['queue'],'t','','',$this->config['timeout']));
        if($result['result'] == -1) {
            $log->debug("Resultado do queue -1, Ligacao atendida ou cancelada.");
            throw new PBX_Rule_Action_Exception_StopExecution();
        }
    }
}
