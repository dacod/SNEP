<?php

/**
 * Classe que abstrai ramais do snep.
 *
 * @see Snep_Usuario
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Ramal extends Snep_Usuario {
    
    /**
     * Do Not Disturb
     *
     * @var boolean dnd
     */
    private $dnd;

    /**
     * Email para o qual as mensagens do voicemail serão enviadas.
     *
     * @var string email
     */
    private $email;

    /**
     * Ramal para siga-me
     *
     * @var Snep_Ramal Siga-me
     */
    private $folowme;

    /**
     * Interface de comunicação fisica com o ramal.
     *
     * @var Interface objeto que herda a classe Interface
     */
    private $interface;

    /**
     * Trava do ramal.
     *
     * @var boolean locked
     */
    private $locked;


    /**
     * Caixa de mensagem do ramal (se houver).
     *
     * @var integer mailbox
     */
    private $mailbox;

    /**
     * Grupo de captura
     *
     * @var string
     */
    protected $pickupgroup;

    public function __construct($numero, $senha, $callerid, $interface) {
        parent::__construct($numero, $callerid, $numero, $senha);

        if(!$interface instanceof PBX_Asterisk_Interface) {
            throw new Exception("Tipo errado Snep_Ramal::__construct() espera uma instancia da classe abstrata PBX_Asterisk_Interface");
        }

        $this->setInterface($interface);
        $this->unlock();
        $this->setFalowMe(null);
    }

    /**
     * Formato imprimivel do ramal
     *
     * @return string
     */
    public function  __toString() {
        return (string)$this->numero;
    }

    /**
     * Desativa DND
     */
    public function DNDDisable() {
        $this->dnd = false;
    }

    /**
     * Ativa DND
     */
    public function DNDEnable() {
        $this->dnd = true;
    }

    /**
     * Email do ramal para voicemail.
     *
     * @return string email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Retorna o ramal para siga-me, se habilitado.
     *
     * @return string falowme
     */
    public function getFalowMe() {
        return $this->falowme;
    }

    /**
     * Retorna a interface física do ramal.
     *
     * @return PBX_Asterisk_Interface interface
     */
    public function getInterface() {
        $this->interface->setOwner($this);
        return $this->interface;
    }

    /**
     * Retorna a caixa de mensagem do ramal
     *
     * @return integer mailbox
     */
    public function getMailBox() {
        return $this->mailbox;
    }

    /**
     * Retorna a que grupo de captura pertence esse ramal.
     *
     * @return string pickupgroup
     */
    public function getPickupGroup() {
        return $this->pickupgroup;
    }

    /**
     * Informa se o ramal tem ou não VoiceMail configurado.
     *
     * @return boolean hasVoicemail
     */
    public function hasVoiceMail() {
        return ($this->mailbox == null) ? false : true;
    }

    /**
     * Verifica se DND está ou não ativado
     *
     * @return boolean dnd
     */
    public function isDNDActive() {
        return $this->dnd;
    }

    /**
     * Verifica se o ramal está ou não bloqueado.
     *
     * @return boolean locked
     */
    public function isLocked() {
        return $this->locked;
    }

    /**
     * Coloca o ramal em estado de trava. O sistema pode usar esse estado para
     * pedir senha antes de efetuar qualquer ação com esse ramal. (autenticação)
     */
    public function lock() {
        $this->locked = true;
    }
    
    /**
     * Define um email para o ramal usar com voicemail.
     *
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Define siga-me para ramal.
     *
     * @param string $ramal siga-me
     */
    public function setFalowMe($ramal) {
        $this->falowme = $ramal;
    }


    /**
     * Define a interface física do ramal
     *
     * @param PBX_Asterisk_Interface $interface
     */
    public function setInterface($interface) {
        $this->interface = $interface;
    }

    /**
     * Define uma caixa de mensagens para o ramal.
     *
     * @param integer $mailbox
     */
    public function setMailBox($mailbox) {
        $this->mailbox = $mailbox;
    }

    /**
     * Define a qual grupo de captura pertence esse usuário.
     *
     * @param string $group name
     */
    public function setPickupGroup($group) {
        $this->pickupgroup = $group;
    }

    /**
     * Destrava o ramal
     */
    public function unlock() {
        $this->locked = false;
    }
}
