#!/usr/bin/php-cgi -q
<?php
/**
 *  This file is part of SNEP.
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
