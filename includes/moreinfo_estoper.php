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

global $db;

// Dados recebidos
$dia  = $_POST['dia'];    // Dia
$hini = $_POST['hini'];    // Hora Inicial
$hfim = $_POST['hfim'];    // Hora Final
$oper = $_POST['oper'];   // Operador
$tipo = $_POST['tipo'];   // Tipo (E, S, O)
$disp = $_POST['disp'];   // Disposition : A, N, B


//print_r($_POST) ;exit;

// Redefine o tipo de acordo com o CCustos
// O (originadas) ==>> S (saidas)
$tipo = ($tipo=="O") ? "S" : $tipo ;
// ND (outras) ==>> O (outras)
$tipo = ($tipo=="ND") ? "O" : $tipo ;
// R (recebidas) ==>> E (entradas)
$tipo = ($tipo=="R") ? "E" : $tipo ;

try {
    $SELECT = "SELECT ccustos.nome as nome, ccustos.tipo as tipo, calldate,date_format(calldate,\"%d/%m/%Y %H:%i:%s\") AS key_dia,  date_format(from_unixtime(uniqueid),\"%H:%i:%s\") as dia_uniq, src, dst, disposition, duration, billsec, accountcode, dcontext FROM cdr ";
    $JOIN   = "  left join ccustos on ccustos.codigo = cdr.accountcode " ;

    // ---->>>> Clausula do where:  Operador <<<<----//
    if ($tipo === "S")
       $WHERE = " WHERE (src = '$oper')" ;
    elseif ($tipo === "E")
       $WHERE = " WHERE (dst = '$oper')" ;
    else
       $WHERE = " WHERE (src = '$oper' or dst = '$oper')" ;

    // ---->>>> Clausula do where:  Periodo <<<<----//
    $dia = $dia;//substr($dia,6,4)."-".substr($dia,3,2)."-".substr($dia,0,2);
    $date_clause.=" AND ( calldate >= '$dia 00:00:00' ";
    $date_clause.=" AND calldate <= '$dia 23:59:59' " ;
    $date_clause.=" AND DATE_FORMAT(calldate,'%T') >= '$hini:00'";
    $date_clause.=" AND DATE_FORMAT(calldate,'%T') <= '$hfim:59') ";
    $WHERE  .= $date_clause ;

    // ---->>>> Clausula do where:  Operador <<<<----//
    $WHERE .= " AND ( tipo = '$tipo'  ) " ;

    // ---->>>> Clausula do where:  disposition <<<<----//
    if ($disp == "A") {
       $WHERE .= " AND ( disposition  = 'ANSWERED' ) " ;
    } elseif ($disp == "N") {
       $WHERE .= " AND ( disposition  = 'NO ANSWER' ) " ;
    } elseif ($disp == "B") {
       $WHERE .= " AND ( disposition  = 'BUSY' ) " ;
    }
    // ---->>>> Clausula do Where: Canais Zumbie <<<<---- //
    $WHERE .= " AND ( locate('ZOMBIE',channel) = 0 ) ";

    //---->>>> Clausula where: Prefixos de Login/Logout <<<<---- //
    $prefix_inout = explode(";",$SETUP['ambiente']['prefixo_inout']) ;
    if ( strlen( $prefix_inout ) > 6 ) {
       $COND_PIO = "" ;
       $array_prefixo = explode(";", $prefix_inout) ;
       foreach ($array_prefixo as $valor) {
          $par = explode("/", $valor);
          $pio_in = $par[0];
          $pio_out = isset($par[1])? $par[1]: "";

          $t_pio_in = strlen($pio_in) ;
          $t_pio_out = strlen($pio_out) ;

          $COND_PIO .= " substr(dst,1,$t_pio_in) != '$pio_in' ";
             if (! $pio_out == '') {
                $COND_PIO .= " AND substr(dst,1,$t_pio_out) != '$pio_out' ";
             }
          $COND_PIO .= " AND " ;
       }
       if ($COND_PIO != "") {
           $WHERE .= " AND ( ".substr($COND_PIO, 0, strlen($COND_PIO) - 4). " ) " ;
       }
    }

    // ---->>>> Clausula do where:  Filtro de desccarte <<<<----//
    $dst_exceptions = explode(";",$SETUP['ambiente']['dst_exceptions']) ;
    $TMP_COND = "" ;
    foreach ($dst_exceptions as $valor) {
       $TMP_COND .= " dst != '$valor' " ;
       $TMP_COND .= " AND " ;
    }
    $WHERE .= " AND ( ".substr($TMP_COND, 0, strlen($TMP_COND) - 4). " ) " ;
    $ORDER  = " ORDER BY calldate";
    $sql = $SELECT.$JOIN.$WHERE.$ORDER ;
    $ligacao = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),false) ;
}


$obj = new Formata() ;
$result = "<table align=\"left\" style=\"width:90% ; margin-left: 10%;\"><thead><tr>" ;
$result.= "<td class=\"cen\">".$LANG['seq']."</td>" ;
$result.= "<td class=\"cen\">".$LANG['calldate']."</td>" ;
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

    $result .= " <td class=\"cen\">".($id+1)."</td>";
    $result .= " <td class=\"cen\">". $lig['key_dia'] ." </td> ";
    $result .= " <td>". $obj->fmt_telefone(array("a"=> $lig['src'])) ." </td> ";
    $result .= " <td>". $obj->fmt_telefone(array("a"=> $lig['dst']))  ." </td> ";
    $result .= " <td>". $tipos_disp[$lig['disposition']] ." </td> ";
    $result .= " <td>". $obj->fmt_segundos(array("a" => $lig['duration'], "b"=>'hms', "A")) ."</td>";
    $result .= " <td>". $obj->fmt_segundos(array("a" => $lig['billsec'], "b"=>'hms', "A")) ."</td>";
    $result .= " <td>". $lig['tipo']." - ".$lig['accountcode']."-".$lig['nome'] ." </td> ";
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