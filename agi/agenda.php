#!/usr/bin/php-cgi -q
<?php

require_once("./agi_base.php");

$requestid = $asterisk->request['agi_callerid'] . " -> " . $asterisk->request['agi_extension'] . " | " . $asterisk->request['agi_uniqueid'];
$action = substr($asterisk->request['agi_extension'],0,3);
$entryid = substr($asterisk->request['agi_extension'],3);
$asterisk->verbose("[$requestid] Connection request from " . $asterisk->request['agi_callerid'] . " to agenda entry " . $entryid . ".", 1);

try {
    $sql = "SELECT phone_1, cell_1 FROM contacts_names WHERE id='$entryid'";
    $result = $db->query($sql)->fetchAll();
    if(count($result) == 1 && ($action == "*12" && $result[0]['phone_1'] != "") OR ($action == "*13" && $result[0]['cell_1'] != "")) {
        if($action == "*12") {
            $asterisk->set_extension($result[0]['phone_1']);
        }
        else {
            $asterisk->set_extension($result[0]['cell_1']);
        }
    }
    else {
        $asterisk->verbose("[$requestid] No valid entry found!", 1);
    }
}
catch (Exception $ex) {
    $asterisk->verbose("[$requestid] Agenda error: " . $ex->getMessage(), 1);
}
