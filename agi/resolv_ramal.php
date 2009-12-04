#!/usr/bin/php-cgi -q
<?php

/**
 * @file Script agi que faz a resolução do ramal do snep baseado na interface
 */

// Importando as configurações para AGI's
require_once("./agi_base.php");

if($argc != 3) {
    $asterisk->verbose("Este scripts espera dois parametro");
    exit(1);
}

// Procurando no banco pelo canal do peer
try {
    $peer = PBX_Interfaces::getChannelOwner($argv[1]);
} catch (Exception $e) {
    $asterisk->verbose("[$requestid] Erro na resolucao de ramal: " . $e->getMessage(), 1);
    exit(1);
}

$asterisk->set_variable($argv[2], $peer->getName());
