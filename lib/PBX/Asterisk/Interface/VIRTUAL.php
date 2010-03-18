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
 * Interface Virtual/Manual
 *
 * Tipo de interface onde se coloca diretamente o canal que será discado.
 * Isso permite entradas manuais de canais para sistemas não previstos, ou
 * não suportados oficialmente pelo snep. Como DVG por exemplo.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_Interface_VIRTUAL extends PBX_Asterisk_Interface {

    /**
     * Construtor da classe
     * @param array $config Array de configurações da interface
     */
    public function __construct($config) {
        $this->tech = 'VIRTUAL';
        $this->config = $config;
    }

    /**
     * Devolve o canal que identifica essa interface no asterisk.
     *
     * @return string Canal
     */
    public function getCanal() {
        return $this->config['channel'];
    }

    /**
     * Método que retorna expressão de identificação do canal no asterisk
     * para que se possa identificar ligações entrantes da interface.
     *
     * @return Expressão para identificação de chamadas
     */
    public function getIncomingChannel() {
        return $this->config['channel_regex'];
    }
}
