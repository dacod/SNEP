#!/usr/bin/php-cgi -q
<?php

/**
 * @file Script agi que faz a resolução do canal (interface) de um ramal do snep
 */

// Importando as configurações para AGI's
require_once("./agi_base.php");

if($argc != 2) {
    $asterisk->verbose("Este scripts aceita somente um ramal como parametro");
    exit(1);
}

$sigame = "";
// Procurando no banco pelo canal do peer
try {
    $ramal = PBX_Usuarios::get($argv[1]);

    if($ramal->getSigame() != "") {
        $ramal2 = $ramal->getFalowme();
        $sigame = $ramal2->getInterface()->getCanal();
    }

} catch (Exception $e) {
    $asterisk->verbose("[$requestid] Erro na resolucao de ramal: " . $e->getMessage(), 1);
    exit(1);
}

$asterisk->set_variable("DND", $ramal->isDNDActive()?"1":"0");
$asterisk->set_variable("SIGAME", "\"$sigame\"");
