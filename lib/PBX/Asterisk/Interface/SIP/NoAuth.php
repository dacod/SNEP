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
 * Interface SIP
 *
 * Representação de uma Interface SIP do asterisk dentro da estrutura do snep.
 *
 * Interface de saída sem autenticação
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_Interface_SIP_NoAuth extends PBX_Asterisk_Interface_SIP {
    /**
     * Construtor da classe
     * @param array $config Array de configurações da interface
     */
    public function __construct($config) {
        $this->tech = 'SIP';
        $this->config = $config;
    }

    /**
     * Eliminando uso de getCanal nessa interface
     */
    public function getCanal() {
        throw new Exception(get_class($this) . ' dont support channel returning, use $obj->getTech()/$your_exten@$obj->getHost() to dial');
    }

    /**
     * Devolve a configuração de host da interface.
     *
     * @return string host
     */
    public function getHost() {
        return $this->config['host'];
    }
}
