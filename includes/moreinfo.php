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


require_once("classes.php");
require_once("../configs/config.php");

$userfield = $_POST['userfield'];
$view_tarifa = $_POST['view_tarif'];

global $db;

set_time_limit(0);
$start = utime();

try {
    $SELECT = "SELECT ccustos.nome as nome,calldate,date_format(calldate,\"%d/%m/%Y %H:%i:%s\") AS key_dia,  date_format(from_unixtime(uniqueid),\"%H:%i:%s\") as dia_uniq, src, dst, disposition, duration, billsec, accountcode, dcontext FROM cdr ";
    $JOIN   = "  left join ccustos on ccustos.codigo = cdr.accountcode " ;
    $WHERE  = " WHERE userfield='$userfield' " ;
    // ---->>>> Clausula do where:  Filtro de desccarte <<<<----//
    $TMP_COND = "" ;
    $dst_exceptions = explode(";",$SETUP['ambiente']['dst_exceptions']) ;
    foreach ($dst_exceptions as $valor) {
       $TMP_COND .= " dst != '$valor' " ;
       $TMP_COND .= " AND " ;
    }
    $WHERE .= " AND ( ".substr($TMP_COND, 0, strlen($TMP_COND) - 4). " ) " ;
    $ORDER  = " ORDER BY calldate DESC";
    $sql = $SELECT.$JOIN.$WHERE.$ORDER ;
    $ligacao = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e)
{
    display_error($LANG['error'].$e->getMessage(),false) ;
}


$obj = new Formata() ;
$result = "<table style=\"width:97% ; margin-left: 3%;\"><thead><tr>" ;
$result.= "<td class=\"cen\">".$LANG['seq']."</td>" ;
// $result.= "<td class=\"cen\">".$LANG['calldate']."</td>" ;
$result.= "<td class=\"esq\">".$LANG['origin']."</td>" ;
$result.= "<td class=\"esq\">".$LANG['destination']."</td>" ;
$result.= "<td class=\"esq\">".$LANG['callstatus']."</td>" ;
$result.= "<td class=\"esq\">".$LANG['duration']."</td>" ;
$result.= "<td class=\"esq\">".$LANG['billsec']."</td>" ;
$result.= "<td class=\"esq\">".$LANG['menu_ccustos']."</td>" ;
$result.= "<td class=\"esq\">".$LANG['context']."</td>" ;
$result.= "<td class=\"esq\">".$LANG['city']."-".$LANG['state']."</td>" ;
if ($view_tarifa == "yes") {
   $result .= "<td>".$LANG['value']."</td>" ;
}

$result.="</tr></thead>" ;


foreach($ligacao as $id => $lig) {
    $result .= " <tr style=\"background-color: #ccc\">";

    $result .= " <td class=\"cen\"> $id </td>";
//     $result .= " <td class=\"cen\">". $lig['key_dia'] ." -> ".$lig['dia_uniq']." </td> ";
    $result .= " <td>". $obj->fmt_telefone(array("a"=> $lig['src'])) ." </td> ";
    $result .= " <td>". $obj->fmt_telefone(array("a"=> $lig['dst']))  ." </td> ";
    $result .= " <td>". $tipos_disp[$lig['disposition']] ." </td> ";
    $result .= " <td>". $obj->fmt_segundos(array("a" => $lig['duration'], "b"=>'hms', "A")) ."</td>";
    $result .= " <td>". $obj->fmt_segundos(array("a" => $lig['billsec'], "b"=>'hms', "A")) ."</td>";
    $result .= " <td>". $lig['accountcode']."-".$lig['nome'] ." </td> ";
     $result .= " <td>". $lig['dcontext'] ."</td>";
    if (strlen($lig['src']) > 7 && strlen($lig['dst']) < 5 )
       $cid = $lig['src'] ;
    else
       $cid = $lig['dst'] ;
    $result .= " <td> ".$obj->fmt_cidade(array("a"=>$cid))." </td>" ;
    if ($view_tarifa == "yes") {
       $result .= "<td style=\"text-align:right\">" ;
       if ($lig['disposition'] == "ANSWERED" ) {
          $result .= $obj->fmt_tarifa(array("a"=>$lig['dst'], "b"=>$lig['billsec'], "c"=>$lig['accountcode'],"d"=>$lig['calldate'])) ;
       } else {
           $result.= "N.A." ;
       }
       $result .= "</td>";
    }
    $result .= " </tr>";
}
$result.="</table>";

unset($obj);
unset($ligacoes);
$end = utime();  /* Marca o final do script */
$runtime = sprintf("%01.2f",$end - $start)."s";

// $result.= $runtime ; // tempo de execução/exibição da consulta


echo $result;
?>