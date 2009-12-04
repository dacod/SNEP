#!/usr/bin/php-cgi -q
<?php

/**
 * @file Script agi que faz a ativacao/desativacao do nao perturbe
 */

// Importando as configurações para AGI's
require_once("./agi_base.php");

if($argc < 2 && ($argv[1] != "enable" OR $argv[1] != "disable")) {
    $asterisk->verbose("ERRO: Esse script espera um parametro: enable/disable");
    exit(1);
}

$funcao = $argv[1];

try {
    if($funcao == "enable") {
        $sql = "UPDATE `peers` SET dnd=1 WHERE name='{$asterisk->request['agi_callerid']}'";
        $db->query($sql);
        
        // Gerando entrada no log
        $sql = "INSERT INTO `services_log` VALUES(NOW(), '{$asterisk->request['agi_callerid']}', 'DND', True, 'Nao perturbe ativado')";
        $db->query($sql);
    }
    else {
        $sql = "UPDATE `peers` SET dnd=0 WHERE name='{$asterisk->request['agi_callerid']}'";
        $db->query($sql);
        
        // Gerando entrada no log
        $sql = "INSERT INTO `services_log` VALUES(NOW(), '{$asterisk->request['agi_callerid']}', 'DND', False, 'Nao perturbe desativado')";
        $db->query($sql);
    }
}
catch(Exception $ex) {
    $asterisk->verbose($ex->getMessage());
    exit(1);
}
