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
 * Desviar para Contexto
 *
 * Direciona a ligação para um contexto do dialplan.
 *
 * Nota: Essa ação irá quebrar a execução das regras desviando totalmente o
 * controle da ligação para o contexto determinado, sem possibilidade de retorno.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_GoContext extends PBX_Rule_Action {

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
        return $this->i18n->translate("Desviar para Contexto");
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
        return $this->i18n->translate("Envia a ligação para um contexto");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $context = (isset($this->config['context']))?"<value>{$this->config['context']}</value>":"";

        return <<<XML
<params>
    <string>
        <id>context</id>
        <default>default</default>
        $context
    </string>
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

        $asterisk->goto($this->config['context'],$request->destino);
    }
}
