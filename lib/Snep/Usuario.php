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
 * Usuário Snep
 *
 * Classe que representa usuários do sistema. Geralmente associados a ramais e
 * agentes.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
abstract class Snep_Usuario {

    /**
     * Nome do usuário
     *
     * @var string
     */
    protected $callerid;

    /**
     * Grupo do usuário
     *
     * @var string
     */
    protected $group;

    /**
     * Numero que identifica o usuário no banco de dados e através do sistema.
     *
     * @var int
     */
    protected $numero;

    /**
     * Senha de acesso a interface.
     *
     * @var string
     */
    protected $senha;

    /**
     * Nome de usuário para acesso a interface.
     *
     * @var string
     */
    protected $username;

    /**
     * Construtor da classe
     *
     * @param int $numero
     * @param string $callerid
     * @param string $usuario
     * @param string $senha
     */
    public function __construct($numero, $callerid, $usuario, $senha) {
        $this->setNumero($numero);
        $this->setCallerid($callerid);
        $this->setPassword($senha);

        $this->setGroup('users');
        $this->username = $usuario;
        
    }

    /**
     * Imprime usuario
     *
     * @return string
     */
    public function  __toString() {
        return (string)$this->usuario;
    }

    /**
     * Retorna o callerid do usuário.
     *
     * @return string callerid
     */
    public function getCallerid() {
        return $this->callerid;
    }

    /**
     * Retorna a que grupo de usuários pertence esse ramal.
     *
     * @return string group
     */
    public function getGroup() {
        return $this->group;
    }

    /**
     * Retorna o numero do grupo
     *
     * @return int numero
     */
    public function getNumero() {
        return $this->numero;
    }

    /**
     * Retorna a senha de um ramal
     *
     * @return string password
     */
    public function getPassword() {
        return $this->senha;
    }

    /**
     * Define um nome para o usuário. (callerid)
     *
     * @param string $callerid
     */
    public function setCallerid($callerid) {
        $this->callerid = $callerid;
    }

    /**
     * Define a qual grupo pertence esse usuário.
     *
     * @param string $group name
     */
    public function setGroup($group) {
        $this->group = $group;
    }

    /**
     * Define um numero para o usuário
     *
     * MUITO CUIDADO: Numeros de usuário devem ser únicos para cada usuário.
     * 
     * @param int $number
     */
    public function setNumero($number) {
        $this->numero = $number;
    }

    /**
     * Define uma senha para o usuário
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->senha = $password;
    }

    /**
     * Define um nome de usuário para acesso a interface ou identificação
     * simplificada.
     *
     * Geralmente usa-se o número do usuário.
     *
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }
}
