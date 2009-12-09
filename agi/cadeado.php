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

require_once('./agi_base.php');

$ramal = PBX_Usuarios::get($request->callerid);

if(!$ramal->isLocked()) {
    $db->update('peers', array("authenticate" => true));
    $asterisk->answer();
    $asterisk->stream_file('activated');
}
else if($ramal->isLocked()){
    $auth = $asterisk->exec('AUTHENTICATE', array($ramal->getPassword(),'',strlen((string)$ramal->getPassword())));
    if($auth['result'] == -1) {
        $log->info("Senha errada para desativar ramal $ramal");
    }
    else {
        $db->update('peers', array("authenticate" => false));
        $asterisk->answer();
        $asterisk->stream_file('de-activated');
    }
}