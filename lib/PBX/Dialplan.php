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
 * Classe dos objetos que controlam o plano de discagem do Snep.
 *
 * O papel desses objetos é selecionar dentre as regras de negócio a que melhor
 * se enquadra para ser executada no ambiente passado ao objeto. Essa analise é
 * feita usando as informações que cada regra provê como sendo as condições para
 * que ela possa ser executada.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Dialplan {

    /**
     * Requisição de execução.
     *
     * Define o ambiente ao qual se encontra a central e ao qual as regras
     * deverão ser confrontadas.
     *
     * @var Asterisk_AGI_Request Request
     */
    protected $request;

    /**
     * Regra válida encontrada no ultimo parse.
     *
     * @var PBX_Rule
     */
    protected $foundRule;

    /**
     * Retorna a regra que casou no ultimo parse executado.
     *
     * @return PBX_Rule
     */
    public function getLastRule() {
        if(!is_object($this->foundRule)) {
            throw new PBX_Exception_NotFound("No rule found or parse not made");
        }
        return $this->foundRule;
    }

    /**
     * Executa a análise das regras para encontrar a que melhor se enquadra na
     * requisição.
     */
    public function parse() {
        $execution_time = date("H:i");
        $this->foundRule = null;

        $rules = PBX_Rules::getAll();
        if(count($rules) > 0) {
            foreach ($rules as $rule) {
                $rule->setRequest($this->request);
                if($rule->isActive() && $rule->isValidDst($this->request->destino) && $rule->isValidSrc($this->request->origem) && $rule->isValidTime($execution_time)) {
                    $this->foundRule = $rule;
                    break; // paramos na primeira regra totalmente válida
                }
            }
            if(!is_object($this->foundRule)) { // Caso nenhuma regra tenha sido encontrada
                throw new PBX_Exception_NotFound("No rule found for this request");
            }
        }
        else {
            throw new PBX_Exception_NotFound("No rules in database");
        }
    }

    /**
     * Define o objeto de requisição a que o dialplan se submete.
     *
     * @param Asterisk_AGI_Request $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

}
