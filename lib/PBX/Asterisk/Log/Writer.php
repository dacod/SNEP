<?php

/**
 * Description of Writer
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_Log_Writer extends Zend_Log_Writer_Abstract {

    /**
     * @var Asterisk_AGI $asterisk interface de comunicação com o asterisk
     */
    protected $asterisk;

    /**
     * Construtor
     *
     * @param Asterisk_AGI $asterisk interface de comunicação com o asterisk
     */
    public function __construct($asterisk) {
        $this->asterisk = $asterisk;

        $this->_formatter = new Zend_Log_Formatter_Simple();
    }

    /**
     * Escreve uma mensagem no CLI do asterisk
     *
     * @param  array  $event  log data event
     * @return void
     */
    protected function _write($event) {
        $line = $this->_formatter->format($event);

        $line = trim($line, "\n"); // Removendo quebras de linha a mais

        $this->asterisk->verbose($line);
    }
}

?>