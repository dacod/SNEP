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

require_once("agi_base.php");

if($argc != 2) {
    $asterisk->verbose("This agi script requires one extension as parameter.");
    exit(1);
}

$sigame = "";
try {
    $ramal = PBX_Usuarios::get($argv[1]);

    if($ramal->getFollowMe() != "") {
        $ramal2 = PBX_Usuarios::get($ramal->getFollowMe());
        $sigame = $ramal2->getInterface()->getCanal();
    }

} catch (Exception $e) {
    $asterisk->verbose("[$requestid] Failure to resolv extension: " . $e->getMessage(), 1);
    exit(1);
}

$asterisk->set_variable("DND", $ramal->isDNDActive()?"1":"0");
$asterisk->set_variable("SIGAME", "\"$sigame\"");
