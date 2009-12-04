<?php
/**
 * Exceção de Ações onde é requerido um desvio no fluxo normal (sequencial) de
 * execução de ações.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_Exception_GoTo extends Exception {

    /**
     * Indice para desviar o fluxo da execução
     *
     * @var int index
     */
    private $index;

    /**
     * Construtor da exceção
     *
     * @param indice $index
     * @param codigo $code
     */
    public function __construct($index, $code = 0) {
        parent::__construct("Desviar para $index", $code);

        $this->index = $index;
    }

    /**
     * Retorna o indice para se desviar o fluxo de execução das ações.
     *
     * @return int index
     */
    public function getIndex() {
        return $this->index;
    }
}
?>
