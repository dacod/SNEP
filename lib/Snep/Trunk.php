<?php

/**
 * Classe que abstrai troncos do snep.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Trunk {

    /**
     * Id do tronco no banco de dados.
     * @var int
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

    /**
     * Retorna uma string para representação em impressão desse tronco.
     *
     * @return string
     */
    public function  __toString() {
        return $this->getName();
    }
}
