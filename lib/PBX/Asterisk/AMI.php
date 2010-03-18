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
 * Singleton Asterisk Manager class
 *
 * Classe criada para fornecer uma opção de conexão única para sistemas complexos
 * que conectem a somente uma máquina asterisk.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_AMI extends Asterisk_AMI {
    /**
     * Instância da classe
     *
     * @var Asterisk_AMI
     */
    private static $instance;

    /**
     * Método que retorna a instancia da classe PBX_Asterisk_AMI. Se esta não estiver
     * disponível é instanciada.
     *
     * Conexão automática usando as variáveis para conexão com o asterisk
     * fornecidas na configuração do snep.
     *
     * @return Asterisk_AMI
     */
    public static function getInstance() {
        if(!isset(self::$instance)) {
            $config = Zend_Registry::get('config');
            $args = array(
                "server"   => $config->ambiente->ip_sock,
                "username" => $config->ambiente->user_sock,
                "secret"   => $config->ambiente->pass_sock
            );
            $asterisk = new Asterisk_AMI(null, $args);

            $asterisk->connect();

            self::$instance = $asterisk;
        }

        return self::$instance;
    }
}
