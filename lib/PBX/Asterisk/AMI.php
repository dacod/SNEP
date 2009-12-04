<?php

/**
 * Singleton Asterisk Manager class
 *
 * Classe criada para fornecer uma opção de conexão única para sistemas complexos
 * que conectem a somente uma máquina asterisk.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2009 OpenS Tecnologia
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
