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

// Definindo nome do arquivo de gravação.
// Formato: Timestamp_aaaammdd_hhmm_src_dst.wav
$filename = implode("_", array(
    time(),
    date("Ymd"),
    date("Hi"),
    $request->getOriginalCallerid(),
    $request->getOriginalExtension()
));
// Definindo userfield com o nome do arquivo para que se possa encontrar a
// gravação a partir do registro no CDR.
$lastuserfield = $asterisk->get_variable('CDR(userfield)');
if($lastuserfield['data'] != "") {
    $asterisk->set_variable("CDR(userfield)", $lastuserfield['data']);
}
else {
    $asterisk->set_variable("CDR(userfield)", $filename);
}


// Variaveis sendo definidas para manutenção da compactibilidade com recurso
// legado do snep.
$asterisk->set_variable("__CALLFILENAME", $filename); // setando a variavel callfilename
$asterisk->set_variable("__TOUCH_MONITOR", $filename); // setando a variavel touch_monitor