<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
 * Pede a senha do usuário ou outra senha.
 *
 * Caso uma senha manual não tenha sido definida a senha será pedida somente se
 * o objeto da requisição for da classe Snep_Usuario.
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_Cadeado extends PBX_Rule_Action {

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
        if(isset($config['tipo']) && $config['tipo'] == "ramal") {
            unset($config['senha']);
        }

        $this->ask_peer = ( isset($config['ask_peer']) && $config['ask_peer'] == 'true') ? true:false;

        parent::setConfig($config);
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Cadeado");
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
        return $this->i18n->translate("Trava a execução de acoes posteriores por senha.");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $i18n  = $this->i18n;
        $tipo  = (isset($this->config['tipo']))?"<value>{$this->config['tipo']}</value>":"";
        $senha = (isset($this->config['senha']))?"<value>{$this->config['senha']}</value>":"";
        $ask_peer = isset($this->config['ask_peer']) ? "<value>{$this->config['ask_peer']}</value>":"";

        $lbl_radio = $i18n->translate("Usar:");
        $lbl_ramal = $i18n->translate("Senha do Ramal");
        $lbl_static = $i18n->translate("Senha Estática (digite a seguir):");
        $lbl_senha = $i18n->translate("Senha Estática:");
        $lbl_desc = $i18n->translate("Em branco para usar padrão do ramal.");
        $lbl_ask_peer = $i18n->translate("Requisitar e substituir ramal de origem");
        return <<<XML
<params>
    <boolean>
        <id>ask_peer</id>
        <default>false</default>
        <label>$lbl_ask_peer</label>
        $ask_peer
    </boolean>
    <string>
        <label>$lbl_senha</label>
        <id>senha</id>
        <size>10</size>
        <description>$lbl_desc</description>
        $senha
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

        if(isset($this->ask_peer) && $this->ask_peer === true) {
            $asterisk->answer();
            $asterisk->exec("READ","RAMAL|agent-user|10|||4");
            $ramal = $asterisk->get_variable("RAMAL");
            try {
                $ramal = PBX_Usuarios::get($ramal['data']);
            }
            catch( PBX_Exception_NotFound $ex ) {
                throw new PBX_Exception_AuthFail("Ramal invalido");
            }

            $request->setSrcObj($ramal);
            $request->origem = $ramal->getNumero();
            $asterisk->set_variable("CALLERID(all)", $ramal->getNumero());
        }

        $senha = "";
        if((!isset($this->config['senha']) || isset($this->config['senha']) && $this->config['senha'] == "") && $request->getSrcObj() instanceof Snep_Usuario) {
            $senha = $request->getSrcObj()->getPassword();
        }
        else if(isset($this->config['senha']) && $this->config['senha'] != ""){
            $senha = $this->config['senha'];
        }
        else {
            $log->warn("Impossivel determinar qual senha usar para a regra");
            return;
        }

        $auth = $asterisk->exec('AUTHENTICATE', array($senha,'',strlen((string)$senha)));
        if($auth['result'] == -1) {
            throw new PBX_Exception_AuthFail();
        }
    }
}
