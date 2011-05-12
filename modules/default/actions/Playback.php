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
 * Tocar Audio
 *
 * Ação de regra de negócio para tocar audio no canal que a executar.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Playback extends PBX_Rule_Action {

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
     * Retorna o nome da Ação. Geralmente o nome da classe.
     *
     * @return Name da Ação
     */
    public function getName() {
        return $this->i18n->translate("Play Audio");
    }

    /**
     * Retorna o numero da versão da classe.
     *
     * @return Versão da classe
     */
    public function getVersion() {
        return SNEP_VERSION;
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
        return $this->i18n->translate("Play an audio file.");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $trs = Zend_Registry::get("i18n");
        $file = (isset($this->config['file']))?"<value>{$this->config['file']}</value>":"";

        return <<<XML
<params>
    <audio>
        <label>{$trs->translate("File")}</label>
        <id>file</id>
        $file
    </audio>
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

        $log->info("Answering and playing audio file: " . $this->config['file']);
        $asterisk->answer();
        $asterisk->stream_file($this->config['file']);
    }
}
