#!/usr/bin/php-cgi -q
<?php
/**
 * Gerencia a entrada e remoção de agentes nas filas a que faz parte.
 */
require_once("./agi_base.php");

// Parsing args
if($argc > 3 || $argc < 2)
    fatal_error("Wrong parameter count should be login/logoff and a numeric agentid.", $asterisk);

// Are the write args?
if($argv[1] != "login" && $argv[1] != "logoff" || !is_numeric($argv[2]))
    fatal_error("Wrong parameter should be login/logoff and a numeric agentid.", $asterisk);

// More beauty references :)
$operation = $argv[1];
$agent     = $argv[2];
$callerid  = $asterisk->request['agi_callerid'];

$asterisk->verbose("Operation $operation for agent $agent using callerid $callerid",1);

// For both, login and logoff we need a correct agent and the queues he belongs
try {
    $sql = "SELECT * FROM `queues_agent` WHERE `agent_id`='$agent'";
    $queues = $db->query($sql)->fetchAll();
} catch (Exception $ex) {
    fatal_error("getting agent info: " . $ex->getMessage());
}

// if the agent belongs to any queue
if(count($queues) == 0) {
    $asterisk->verbose("Agent $agent doesn't belong to any queue.");
    exit(0); // normal clearing
}

// begin disriminating operation
if($operation == "login") { // do login
    $insertedinto = "";
    foreach($queues as $rule) {
        // The inserction command, inserted as dynamic member so it can be removed trought CLI if errors
        $asterisk->exec("AddQueueMember", $rule['queue'] . "|Agent/$agent");
        $insertedinto .= "," . $rule['queue'];
	$stampData = mktime(date('H'), date('i'), date('s'), date("m")  , date("d"), date("Y"));
        $strData = date("Y-m-d H:i:s", $stampData);

	$sql = "INSERT INTO lista_abandono (time,data,fila,canal,evento,date) 
		VALUES ('$stampData', '$stampData', '{$rule['queue']}','Agent/$agent','AGENTCALLBACKLOGIN','$strData') ";

        $db->beginTransaction() ;
        $db->exec($sql) ;
        $db->commit();
    }
    $asterisk->verbose("Agent $agent inserted into queues: " . trim($insertedinto, ','));
}
else { // do logoff
    $removedfrom = "";
    foreach($queues as $rule) {
        // The inserction command, inserted as dynamic member so it can be removed trought CLI if errors
        $asterisk->exec("RemoveQueueMember", $rule['queue'] . "|Agent/$agent");
        $removedfrom .= "," . $rule['queue'];
        $stampData = mktime(date('H'), date('i'), date('s'), date("m")  , date("d"), date("Y"));
        $strData = date("Y-m-d H:i:s", $stampData);

        $sql = "INSERT INTO lista_abandono (time,data,fila,canal,evento,date)
	        VALUES ('$stampData', '$stampData', '{$rule['queue']}', 'Agent/$agent', 'AGENTCALLBACKLOGOFF' , '$strData') ";

        $db->beginTransaction() ;
        $db->exec($sql) ;
        $db->commit();
    }
    $asterisk->verbose("Agent $agent removed from queues: " . trim($removedfrom, ','));
}
