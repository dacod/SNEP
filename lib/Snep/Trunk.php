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
 * Classe que abstrai troncos do snep.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Trunk extends Snep_Channel {

    /**
     * DTMF Dial Mode
     *
     * Define que a forma de enviar digitos pelo tronco deve ser de forma
     * "analógica". Abre-se o canal primeiro depois faz-se o envio de dtmf.
     *
     * @var boolean
     */
    private $dtmfDialMode = false;

    /**
     * Numero a se discar de forma "digital". Util para automatizar discagens
     * para DISA's
     *
     * @var string
     */
    private $dtmfDialNumber = "";

    /**
     * Id do tronco no banco de dados.
     * @var integer
     */
    private $id;

    /**
     * Nome do tronco
     *
     * @var String
     */
    private $nome;

    /**
     * Interface de comunicação fisica com o tronco.
     *
     * @var Interface objeto que herda a classe Interface
     */
    private $interface;

    /**
     * Define se o tronco quer permitir ou não o mapeamento de ramais a partir
     * do callerid ou outro método. Se não a intenção é que a ligação entre
     * normalmente como advinda de um tronco.
     *
     * @var boolean
     */
    private $extensionMapping = false;

    public function __construct($nome, PBX_Asterisk_Interface $interface) {
        $this->nome = $nome;
        $this->interface = $interface;
    }

    /**
     * Define intenção do tronco de mapear ramais pelo callerid.
     *
     * @param boolean $order
     */
    public function setExtensionMapping( $order ) {
        $this->extensionMapping = $order;
    }

    /**
     * Verifica se o tronco quer permitir ou não o mapeamento de extensões pelo
     * callerid.
     *
     * @return boolean extensionMapping
     */
    public function allowExtensionMapping() {
        return $this->extensionMapping;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->nome;
    }

    public function getInterface() {
        $this->interface->setOwner($this);
        return $this->interface;
    }

    public function setId($id) {
        $this->id = $id;
    }
    
    public function setNome($novo_nome) {
        $this->nome = $novo_nome;
    }
    
    public function setInterface($interface) {
        $this->interface = $interface;
    }

    public function getDtmfDialMode() {
        return $this->dtmfDialMode;
    }

    public function setDtmfDialMode($dtmfDialMode) {
        $this->dtmfDialMode = $dtmfDialMode;
    }

    public function getDtmfDialNumber() {
        return $this->dtmfDialNumber;
    }

    public function setDtmfDialNumber($dtmfDialNumber) {
        $this->dtmfDialNumber = $dtmfDialNumber;
    }

    /**
     * Retorna uma string para representação em impressão desse tronco.
     *
     * @return string
     */
    public function  __toString() {
        return $this->getName();
    }
}
