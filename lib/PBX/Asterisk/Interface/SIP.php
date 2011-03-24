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

require_once "PBX/Asterisk/Interface.php";

/**
 * Interface SIP
 *
 * Representação de uma Interface SIP do asterisk dentro da estrutura do snep.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_Interface_SIP extends PBX_Asterisk_Interface {
    /**
     * Construtor da classe
     * @param array $config Array de configurações da interface
     */
    public function __construct($config) {
        $this->tech = 'SIP';
        /*
         * Forçar registro por parte do asterisk, isso fará com que ele se
         * comporte como um cliente sip. Uso padrão em troncos.
         */
        $this->config['force_registry'] = false;

        $this->config = $config;
    }

    /**
     * Devolve o canal que identifica essa interface no asterisk.
     *
     * Usado para discagem e pesquisa. Para interfaces sip o canal é geralmente:
     * SIP/numero_interface
     *
     * ex: para o ramal 1000
     * SIP/1000
     *
     * @return string Canal
     */
    public function getCanal() {
        return $this->getTech() . "/" . $this->config['username'];
    }

    /**
     * Devolve a configuração de hostname da interface.
     *
     * @return string host
     */
    public function getHost() {
        return $this->config['host'];
    }

    /**
     * Devolve o usuário ao qual a interface faz ou aceita login.
     *
     * @return string username
     */
    public function getUsername() {
        return $this->config['username'];
    }
}
