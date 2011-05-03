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
 * Interface IAX2
 *
 * Representação de uma Interface IAX2 do asterisk dentro da estrutura do snep.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_Interface_IAX2 extends PBX_Asterisk_Interface {
    /**
     * Construtor da classe
     * @param array $config Array de configurações da interface
     */
    public function __construct($config) {
        $this->tech = 'IAX2';
        /*
         * Forçar registro por parte do asterisk, isso fará com que ele se
         * comporte como um cliente IAX2. Uso padrão em troncos.
         */
        $this->config['force_registry'] = false;

        $this->config = $config;
    }

    /**
     * Devolve o canal que identifica essa interface no asterisk.
     *
     * Usado para discagem e pesquisa. Para interfaces IAX2 o canal é geralmente:
     * IAX2/numero_interface
     *
     * ex: para o ramal 1000
     * IAX2/1000
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
