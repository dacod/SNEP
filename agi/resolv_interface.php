#!/usr/bin/php-cgi -q
<?php

/**
 * @file Script agi que faz a resolução do canal (interface) de um ramal do snep
 */

// Importando as configura��es para AGI's
require_once("./agi_base.php");

if($argc != 3) {
    $asterisk->verbose("Este scripts espera dois parametro, ramal e variavel");
    exit(1);
}

// Procurando no banco pelo canal do peer
try {
    $ramal = PBX_Usuarios::get($argv[1]);
} catch (Exception $e) {
    $asterisk->verbose("[$requestid] Erro na resolucao de interface: " . $e->getMessage(), 1);
    exit(1);
}

$asterisk->set_variable($argv[2], $ramal->getInterface()->getCanal());
