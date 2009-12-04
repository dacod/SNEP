<?php

/**
 * Exceção padrão para falha em integridade no banco de dados. Se alguma dessas
 * falhas ocorrer é necessário tomar medidas urgentes para recuperação do banco
 * e criação de regras mais efetivas de controle.
 *
 * @category  Snep
 * @package   PBX_Exception
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Exception_DatabaseIntegrity extends Exception {}

?>
