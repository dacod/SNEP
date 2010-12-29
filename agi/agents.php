#!/usr/bin/php -q
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
 * Gerencia a entrada e remoção de agentes nas filas a que faz parte.
 */
require_once('agi_base.php');

// Are the right args?
if(!($argv[1] == "login" && is_numeric($argv[2])) && !($argv[1] == "logoff") ) {
    $log->crit("Wrong parameter should be login/logoff and a numeric agentid.", $asterisk);
    exit(1);
}

// More beauty references :)
$operation = $argv[1];
$agent     = isset($argv[2]) ? $argv[2] : null;
$callerid  = $asterisk->request['agi_callerid'];

$channel = $asterisk->request['agi_channel'];
$channel = strpos($channel, '-') ? substr($channel, 0, strpos($channel, '-')) : $channel;

$asterisk->verbose("Operation $operation for agent $agent using callerid $callerid",1);

if($operation == "login") {
    $asterisk->answer();
    $exten = PBX_Usuarios::get($agent);
    for($tries = 3; $tries > 0; $tries--) {
        $asterisk->exec("Read", array("AGETNTPASS","agent-pass", strlen($exten->getPassword()), "", "", 5));
        $pass = $asterisk->get_variable("AGETNTPASS");

        if($pass['data'] === $exten->getPassword()) {
            $tries = 0;
            $data = array(
                "date" => new Zend_Db_Expr("NOW()"),
                "agent" => $agent,
                "event" => 1 // Assume-se que o id padrão para event login seja 1
            );
            $db->insert('agent_availability', $data);

            $db->update('peers', array("canal"=>"Agent/$channel"), "name='$agent'");

            $asterisk->exec("UserEvent", array("Agentlogin", "Agent: Agent/$agent"));
            $asterisk->stream_file("agent-loginok");
        }
        else {
            $asterisk->stream_file("incorrect-password");
        }
    }
}
else { // do logoff
    $data = array(
        "date" => new Zend_Db_Expr("NOW()"),
        "agent" => $asterisk->requestObj->callerid,
        "event" => 2 // Assume-se que o id padrão para event logoff seja 2
    );
    $db->insert('agent_availability', $data);

    $channel = $channel;
    $db->update('peers', array("canal"=>"Agent/"), "canal like 'Agent/$channel'");
    $asterisk->answer();
    $asterisk->stream_file("agent-loggedoff");
}
