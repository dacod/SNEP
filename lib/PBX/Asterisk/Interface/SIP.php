<?php
/**
 * Interface SIP
 *
 * Representação de uma Interface SIP do asterisk dentro da estrutura do snep.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2009 OpenS Tecnologia
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
?>