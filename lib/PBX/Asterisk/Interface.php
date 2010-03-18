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
 * Classe que representa a interface fisica no asterisk, os objetos guardam
 * as configurações relativas a essa interface para que possa ser
 * espelhada do banco para os arquivos de configuração.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
abstract class PBX_Asterisk_Interface {
    /**
     * Configuração da interface, é basicamente um array com as informações
     * da interface.
     */
    protected $config;

    /**
     * Id da interface. Originalmente usado para relação entre interfaces
     * e seus dados no banco de dados. Pode ser usado para controle interno
     * em outros casos.
     *
     * @var int id da interface
     */
    public $id;

    /**
     * Dono da interface.
     *
     * @var Object
     */
    protected $owner;

    /**
     * Tecnologia de canais dessa interface.
     * DEVE SER O MESMO NOME DO ARQUIVO E DA CLASSE
     * @var string
     */
    protected $tech;

    /**
     * Assim as configurações podem ser lidas de uma forma mais simples
     *
     * @param configuração a ser devolvida
     * @return string com o valor da configuração
     */
    public function __get($chave_config) {
        return $this->config[$chave_config];
    }

    /**
     * Assim as configurações podem ser definidas de uma forma mais simples
     *
     * @param configuração a ser definida
     * @param valor da configuração
     */
    public function __set($chave_config, $valor) {
        $this->config[$chave_config] = $valor;
    }

    /**
     * Método que retorna string de identificação do canal no asterisk.
     *
     * Um dos métodos mais importantes, garante que seja possível alcançar essa
     * interface através do asterisk possibilitando o chaveamento das ligações.
     */
    public abstract function getCanal();

    /**
     * Retorna todas as configurações da interface.
     *
     * @return array com as configurações da interface
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Método que retorna expressão de identificação do canal no asterisk
     * para que se possa identificar ligações entrantes da interface.
     *
     * Em troncos IP esse será o mesmo que o canal de saída (getCanal).
     *
     * @return Expressão para identificação de chamadas
     */
    public function getIncomingChannel() {
        return $this->getCanal();
    }

    /**
     * Interface owner
     *
     * @return Object
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * Retorna a tecnologia de canais que essa interface usa
     * DEVE SER O MESMO NOME DO ARQUIVO E DA CLASSE
     * @return string tecnologia de canais
     */
    public function getTech() {
        return $this->tech;
    }

    /**
     * Define dono da interface;
     *
     * @param Object owner
     */
    public function setOwner($owner) {
        $this->owner = $owner;
    }
}
