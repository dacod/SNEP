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

require_once('agi_base.php');

$action = substr($asterisk->request['agi_extension'],0,3);
$entryid = substr($asterisk->request['agi_extension'],3);
$log = Zend_Registry::get('log');
$log->info("Connection request from " . $asterisk->request['agi_callerid'] . " to agenda entry " . $entryid . ".");

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
