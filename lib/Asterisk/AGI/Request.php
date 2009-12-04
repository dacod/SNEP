<?php
/**
 * Requisições AGI.
 *
 * Essa classe implementa metodos e facilidades relacionadas a requisições
 * feitas pelo Asterisk via Gateway Interface (AGI).
 *
 * @author Henrique Grolli Bassotto
 */
class Asterisk_AGI_Request {

    /**
     * @var $agi_request, array original com requisição do asterisk.
     */
    private $agi_request;

    /**
     * Informações do request para trabalho interno.
     *
     * @var array mixed
     */
    protected $request;

    /**
     * Facilita a busca de variáveis que vieram em requisição (channel,
     * exten, etc).
     *
     * @param string $varname
     * @return mixed valor da propriedade
     */
    public function __get($varname) {
        return $this->request[$varname];
    }

    public function __set($varname, $value) {
        $this->request[$varname] = $value;
    }

    /**
     * Construtor da requisição.
     *
     * @param int $origem
     * @param string $destino
     * @param string $contexto
     */
    public function __construct($agi_request) {
        $this->agi_request = $agi_request;

        // copiando requisição e removendo a string 'agi_' das chaves.
        foreach ($agi_request as $key => $value) {
            $this->request[substr($key, 4)] = $value;
        }
    }

    /**
     * Retorna o callerid original da chamada.
     *
     * @return string originalCallerid
     */
    public function getOriginalCallerid() {
        return $this->agi_request['agi_callerid'];
    }

    /**
     * Retorna o número original da chamada.
     *
     * @return strig orignialExtension
     */
    public function getOriginalExtension() {
        return $this->agi_request['agi_extension'];
    }
}
