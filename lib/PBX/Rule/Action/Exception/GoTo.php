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
 * Exceção de Ações onde é requerido um desvio no fluxo normal (sequencial) de
 * execução de ações.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_Exception_GoTo extends Exception {

    /**
     * Indice para desviar o fluxo da execução
     *
     * @var int index
     */
    private $index;

    /**
     * Construtor da exceção
     *
     * @param indice $index
     * @param codigo $code
     */
    public function __construct($index, $code = 0) {
        parent::__construct("Desviar para $index", $code);

        $this->index = $index;
    }

    /**
     * Retorna o indice para se desviar o fluxo de execução das ações.
     *
     * @return int index
     */
    public function getIndex() {
        return $this->index;
    }
}
