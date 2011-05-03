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
 * Plugin para Regras de Negócio
 *
 * Classe que abstrai funções e determina as funções obrigatórias para plugins
 * de regras de negócio.
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 * @see PBX_Rule_Plugin_Broker
 *
 * @category  Snep
 * @package   PBX_Rule
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
abstract class PBX_Rule_Plugin {

    /**
     * Regra de negócio que executará esse plugin
     *
     * @var PBX_Rule
     */
    protected $rule = null;

    /**
     * Interface de comunicação com o Asterisk
     *
     * Este objeto só estara disponível no momento em que as ações específicas
     * do plugin forem invocadas.
     *
     * @var Asterisk_AGI
     */
    protected $asterisk = null;

    /**
     * Retorna a regra que executa esse plugin
     *
     * @return PBX_Rule
     */
    public function getRule() {
        return $this->rule;
    }

    /**
     * Define qual a regra que executará as ações desse plugin
     *
     * @param PBX_Rule $rule
     */
    public function setRule( PBX_Rule $rule ) {
        $this->rule = $rule;
    }

    /**
     * Retorna o objeto asterisk
     *
     * @return Asterisk_AGI
     */
    public function getAsteriskInterface() {
        return $this->asterisk;
    }

    /**
     * Define uma interface de comunicação com asterisk para uso com as ações do
     * plugin.
     *
     * @param Asterisk_AGI $asterisk
     */
    public function setAsteriskInterface(Asterisk_AGI $asterisk) {
        $this->asterisk = $asterisk;
    }

    /**
     * Chamado no início do processo de execução da regra
     */
    public function startup() {}

    /**
     * Chamado antes da execução de cada ação da regra
     *
     * @param int $index Índice da ação que está sendo executada essa chamada
     */
    public function preExecute($index) {}

    /**
     * Chamado ao final da execução de cada ação da regra
     *
     * @param int $index Índice da ação que está sendo executada essa chamada
     */
    public function postExecute($index) {}

    /**
     * Chamado ao final da execução da regra
     */
    public function shutdown() {}

}

