<?php
/* ----------------------------------------------------------------------------
 * Programa: khomp_channels.php - Lista de canais khomp
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Henrique Grolli Bassotto <henrique@opens.com.br>
 *---------------------------------------------------------------------------*/
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
