#!/usr/bin/php-cgi -q
<?php

/**
 * @file Script agi que faz a resolu��o do ramal do snep baseado na interface
 */

// Importando as configura��es para AGI's
require_once("./agi_base.php");

if($argc < 2) {
    $asterisk->verbose("Este scripts espera um nome de variavel como parametro");
    exit(1);
}

if($argc == 3) {
    $raw_channel = $argv[2];
}
else {
    $raw_channel = $asterisk->request['agi_channel'];
}

$channel = substr($raw_channel, 0, strpos($raw_channel, '-'));

$asterisk->set_variable($argv[1], $channel);
