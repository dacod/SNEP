#!/usr/bin/php -q
<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file Script agi que faz a ativacao/desativacao do sigame
 */

// Importando as configura��es para AGI's
require_once("agi_base.php");


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
