#!/usr/bin/php-cgi -q
<?php

require_once('./agi_base.php');

if(!isset($argv[1]) || !is_numeric($argv[1])) {
    $log->crit("Argumento invalido para primeiro argumento , $argv[1]. Espera-se um ramal");
}

if(isset($argv[2])) {
    $variable = $argv[2];
}
else {
    $variable = "PICKUPGROUP";
}

try {
    $ramal = PBX_Usuarios::get($argv[1]);
}
catch(PBX_Exception_NotFound $ex) {
    $log->info("Ramal {$argv[1]} não encontrado.");
    $asterisk->set_variable($variable, '-1');
}

$asterisk->set_variable($variable, $ramal->getPickupGroup());