#!/usr/bin/php-cgi -q
<?php

/**
 * @file Script agi que faz a ativacao/desativacao do sigame
 */

// Importando as configura��es para AGI's
require_once("./agi_base.php");


if(isset($argv[1]) && is_numeric($argv[1])) {
    $funcao = "enable";
    $ramal = $argv[1];
}
else {
    $funcao = "disable";
}

try {
    if($funcao == "enable") {
        // Ativando o serviço
        $sql = "UPDATE `peers` SET sigame='$ramal' WHERE name='{$asterisk->request['agi_callerid']}'";
        $db->query($sql);

        // Gerando entrada no log
        $sql = "INSERT INTO `services_log` VALUES(NOW(), '{$asterisk->request['agi_callerid']}', 'SIGAME', True, 'Sigame ativado, desviando para: $ramal')";
        $db->query($sql);

        $asterisk->stream_file("activated");
    }
    else {
        // Desativando o serviço
        $sql = "UPDATE `peers` SET sigame=NULL WHERE name='{$asterisk->request['agi_callerid']}'";
        $db->query($sql);
        
        // Gerando entrada no log
        $sql = "INSERT INTO `services_log` VALUES(NOW(), '{$asterisk->request['agi_callerid']}', 'SIGAME', False, 'Sigame desativado')";
        $db->query($sql);
    }
}
catch(Exception $ex) {
    $asterisk->verbose($ex->getMessage());
    exit(1);
}
