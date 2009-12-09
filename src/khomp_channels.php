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

require_once("../includes/verifica.php");
require_once("../configs/config.php");
error_reporting(0);

function getChannels($board) {
    echo "<option></option>";

    $khompInfo = new PBX_Khomp_Info();
    $boardInfo = $khompInfo->boardInfo($board);
    if( $khompInfo->hasWorkingBoards() ) {
        foreach( range(0, $boardInfo['channels'] -1 ) as $channel ) {
            $selected = (isset($_GET['selected']) && $_GET['selected'] == $channel)?"selected=\"true\"":"";
            $number = $channel + 1;
            echo "<option value=\"{$channel}\" " . $selected . ">$number</option>";
        }
    }
}

switch($_GET['value']) {
    case 'links':
        getLinks($_GET['board']);
    break;
    case 'channels':
        getChannels($_GET['board'], $_GET['link']);
    break;
    default:
      die(json_encode(array("error"=>"tipo de retorno desconhecido, ou invalido")));
}
