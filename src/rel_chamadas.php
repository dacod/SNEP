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

ver_permissao(21);

global $acao, $prefix_inout;

$prefix_inout = $SETUP['ambiente']['prefix_inout'];
$dst_exceptions = $SETUP['ambiente']['dst_exceptions'];

if ($acao == "relatorio" || $acao == "grafico" || $acao == "csv") {
    $my_object = new Formata ;
    monta_relatorio($acao);

} elseif ($acao == "imp") {
    exibe_relatorio() ;

}

// Centros de Custos do Sistema
if (!isset($ccustos) || count($ccustos) == 0) {
    try {
        $sql = "select ccustos.* from ccustos ORDER BY ccustos.codigo" ;
        $row = $db->query($sql)->fetchAll();
    } catch (Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
        exit ;
    }
    unset($val);
    $ccustos = array();
    if (count($row) > 0) {
        foreach ($row as $val)
            $ccustos[$val['codigo']] = $val['tipo']." : ".$val['codigo']." - ".$val['nome'] ;
        asort($ccustos);
    }
}
$_SESSION['ccusto'] = $ccustos;

// Monta nivel de acesso aos relatórios.
$nivel = Snep_Vinculos::getNivelVinculos( $_SESSION['name_user'] );

/* Grupos de Ramais */
$sql = "SELECT * FROM groups" ;
try {
    $row = $db->query($sql)->fetchAll();

} catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;

}

$g = array(''=>'');
foreach ($row as $key => $group) {
    switch($group['name']) {
        case 'admin':
            $g[$group['name']] = 'Administradores';
            break;
        case 'users':
            $g[$group['name']] = 'Usu&aacute;rios';
            break;
        case 'all':
            $g[$group['name']] = 'Todos';
            break;
        default:
            $g[$group['name']] = $group['name'];
    }
}

$titulo = $LANG['menu_reports']." » ".$LANG['menu_rel_callers'];

// Variaveis do formulario.
$dados_iniciais = array("dia_ini" => ( isset( $_SESSION['relchamadas']['dia_ini'] ) ? $_SESSION['relchamadas']['dia_ini'] : "01/".date('m/Y') ),
        "dia_fim"  => ( isset( $_SESSION['relchamadas']['dia_fim'] ) ? $_SESSION['relchamadas']['dia_fim'] : date('d/m/Y') ),
        "hora_ini" => ( isset($_SESSION['relchamadas']['hora_ini'] ) ? $_SESSION['relchamadas']['hora_ini'] : "00:00"),
        "hora_fim" => ( isset($_SESSION['relchamadas']['hora_fim'] ) ? $_SESSION['relchamadas']['hora_fim'] : "23:59")) ;
$smarty->assign ('ccusto', ( isset($_SESSION['relchamadas']['ccustos'] ) ? $_SESSION['relchamadas']['ccustos'] : "x"));
$smarty->assign ('src', ( isset( $_SESSION['relchamadas']['src'] ) ? $_SESSION['relchamadas']['src'] : "") );
$smarty->assign ('dst', ( isset( $_SESSION['relchamadas']['dst'] ) ? $_SESSION['relchamadas']['dst'] : "") );
$smarty->assign ('duration1', ( isset( $_SESSION['relchamadas']['duration1'] ) ? $_SESSION['relchamadas']['duration1'] : "") );
$smarty->assign ('duration2', ( isset( $_SESSION['relchamadas']['duration2'] ) ? $_SESSION['relchamadas']['duration2'] : "") );
$smarty->assign ('status_all', ( isset( $_SESSION['relchamadas']['status_all'] ) ?  "checked=\"checked\"" : "checked=\"checked\"") );
$smarty->assign ('status_ans', ( isset( $_SESSION['relchamadas']['status_ans'] )  ?  "checked=\"checked\"" : "") );
$smarty->assign ('status_noa', ( isset( $_SESSION['relchamadas']['status_noa'] )  ?  "checked=\"checked\"" : "") );
$smarty->assign ('status_bus', ( isset( $_SESSION['relchamadas']['status_bus'] )  ?  "checked=\"checked\"" : "") );
$smarty->assign ('status_fai', ( isset( $_SESSION['relchamadas']['status_fai'] )  ?  "checked=\"checked\"" : "") );
$smarty->assign ('view_tarif', ( isset( $_SESSION['relchamadas']['view_tarif'] )  ?  $_SESSION['relchamadas']['view_tarif'] : "no") );
$smarty->assign ('view_files', ( isset( $_SESSION['relchamadas']['view_files'] )  ?  $_SESSION['relchamadas']['view_files'] : "no") ) ;
$smarty->assign ('graph_type', ( isset( $_SESSION['relchamadas']['graph_type'] ) ? $_SESSION['relchamadas']['graph_type'] : "B")) ;
$smarty->assign ('call_type', ( isset( $_SESSION['relchamadas']['call_type'] ) ? $_SESSION['relchamadas']['call_type'] : "T")) ;
$smarty->assign ('groupsrc', isset( $_SESSION['relchamadas']['groupsrc'] )  ? $_SESSION['relchamadas']['groupsrc'] : "") ;
$smarty->assign ('groupdst', isset( $_SESSION['relchamadas']['groupdst'] )  ? $_SESSION['relchamadas']['groupdst'] : "") ;
$smarty->assign ('ordenar', isset( $_SESSION['relchamadas']['ordenar'] ) ? $_SESSION['relchamadas']['ordenar'] : "");
$smarty->assign ('PROTOTYPE', True) ;
$smarty->assign ('dt_relchamadas', $dados_iniciais) ;
$smarty->assign ('FILTERS', $dst_exceptions) ;
$smarty->assign ('OPCOES_YN', $tipos_yn) ;
$smarty->assign ('USER', $id_user);
$smarty->assign ('tipo_rel', array('1' => $LANG['analitico'], '2' => $LANG['sintetico']));
$smarty->assign ('OPCOES_PROCURA', $tipos_procura);
$smarty->assign ('OPCOES_CHAMADAS', $tipos_chamadas_rel);
$smarty->assign ('OPCOES_GRAFICOS', $tipos_graficos);
$smarty->assign ('NIVEL', $nivel);
$smarty->assign ('OPCOES_USERGROUPS', $g);
$smarty->assign ('CCUSTOS', $ccustos);
display_template("rel_chamadas.tpl", $smarty,$titulo);

/*------------------------------------------------------------------------------
 Funcao monta_relatorio - Monta o relatsorio
------------------------------------------------------------------------------*/
function monta_relatorio($acao) {
    global  $srctype, $ordernar, $dsttype, $LANG, $db, $smarty, $rel_type, $dia_ini, $dia_fim, $hora_fim, $hora_ini, $groupsrc, $groupdst , $status_all, $status_ans, $status_noa, $status_bus, $status_fai, $filter, $contas, $duration1, $duration2 , $src, $dst, $orides, $dst_exceptions, $prefix_inout, $graph_type, $call_type, $SETUP, $tipos_chamadas_rel, $view_files, $view_tarif, $my_object, $acao;

    /* Salvando dados do formulario.                                              */
    $_SESSION['relchamadas']['dia_ini'] = $dia_ini;
    $_SESSION['relchamadas']['dia_fim'] = $dia_fim;
    $_SESSION['relchamadas']['hora_ini'] = $hora_ini;
    $_SESSION['relchamadas']['hora_fim'] = $hora_fim;
    $_SESSION['relchamadas']['src'] = $src;
    $_SESSION['relchamadas']['dst'] = $dst;
    $_SESSION['relchamadas']['ordenar'] = $ordernar;
    $_SESSION['relchamadas']['ccustos'] = $contas;
    $_SESSION['relchamadas']['groupsrc'] = $groupsrc;
    $_SESSION['relchamadas']['groupdst'] = $groupdst;
    $_SESSION['relchamadas']['duration1'] = $duration1;
    $_SESSION['relchamadas']['duration2'] = $duration2;
    $_SESSION['relchamadas']['status_all'] = $status_all;
    $_SESSION['relchamadas']['status_ans'] = $status_ans;
    $_SESSION['relchamadas']['status_noa'] = $status_noa;
    $_SESSION['relchamadas']['status_bus'] = $status_bus;
    $_SESSION['relchamadas']['status_fai'] = $status_fai;
    $_SESSION['relchamadas']['view_files'] = $view_files;
    $_SESSION['relchamadas']['view_tarif'] = $_POST['view_tarif'];
    $_SESSION['relchamadas']['graph_type'] = $graph_type;
    $_SESSION['relchamadas']['call_type'] = $call_type;
    $_SESSION['relchamadas']['rel_type'] = $rel_type;

    /* Busca os ramais pertencentes ao grupo de ramal de origem selecionado */
    $ramaissrc = $ramaisdst = "" ;
    if($groupsrc) {
        $origens = PBX_Usuarios::getByGroup($groupsrc);
        if( count($origens) == 0 ) {
            display_error($LANG['error'] . $LANG['error_nogroup_item'] ,true);
        }
        else {
            $ramalsrc = "";
            foreach ($origens as $ramal) {
                $ramalsrc .= $ramal->getNumero() . ',';
            }
            $ramaissrc = " AND src in (" . trim($ramalsrc, ',') . ") ";
        }
    }

    /* Busca os ramais pertencentes ao grupo de ramal de destino selecionado */
    if($groupdst) {
        $destinos = PBX_Usuarios::getByGroup($groupdst);
        if( count($destinos) == 0 ) {
            display_error($LANG['error'] . $LANG['error_nogroup_item'] ,true);
        }
        else {
            $ramaldst = "";
            foreach ($destinos as $ramal) {
                $ramaldst .= $ramal->getNumero() . ',';
            }
            $ramaisdst = " AND dst in (" . trim($ramaldst, ',') . ") ";
        }
    }
    
    /* Verificando existencia de vinculos no ramal */
    $name = $_SESSION['name_user'];
    $sql = "SELECT id_peer, id_vinculado FROM permissoes_vinculos WHERE id_peer ='$name'";    
    $result = $db->query($sql)->fetchObject();

    $vinculo_table = "";
    $vinculo_where = "";
    if($result) {        
        $vinculo_table = " ,permissoes_vinculos ";
        $vinculo_where = " ( permissoes_vinculos.id_peer='{$result->id_peer}' AND (cdr.src = permissoes_vinculos.id_vinculado OR cdr.dst = permissoes_vinculos.id_vinculado) ) AND ";
    }

    /* Clausula do where: periodos inicial e final                                */
    $dia_inicial = substr($dia_ini,6,4)."-".substr($dia_ini,3,2)."-".substr($dia_ini,0,2);
    $dia_final = substr($dia_fim,6,4)."-".substr($dia_fim,3,2)."-".substr($dia_fim,0,2);
    $date_clause =" ( calldate >= '$dia_inicial'";
    $date_clause .=" AND calldate <= '$dia_final 23:59:59'"; //'
    $date_clause .=" AND DATE_FORMAT(calldate,'%T') >= '$hora_ini:00'";
    $date_clause .=" AND DATE_FORMAT(calldate,'%T') <= '$hora_fim:59') ";
    $CONDICAO = $date_clause;

    $ORIGENS = '';

    // Clausula do where: Origens
    if($src !== "") {
        if(strpos($src, ",")) {
            $SRC = '';
            $arrSrc = explode(",", $src);
            foreach($arrSrc as $srcs) {
                $SRC .= sql_like($srctype, $srcs, 'src');
            }
            $SRC = " AND (". substr($SRC, 3) .")";
        } else {
            $ORIGENS.= str_replace("or", "AND", sql_like($srctype, $src, 'src'));
        }
    }
    
    // Clausula do where: Destinos
    if($dst !== "") {
        if(strpos($dst, ",")) {
            $DST = '';
            $arrDst = explode(",", $dst);
            foreach($arrDst as $dsts) {

                $DST .= sql_like($dsttype, $dsts, 'dst');
            }
            $DST = " AND (". substr($DST, 3) .")";
        }else{
            $ORIGENS .= str_replace("or", "AND", sql_like($dsttype, $dst, 'dst'));
        }
    }

    if(isset($ORIGENS)) {
        $CONDICAO .= $ORIGENS;
    }
    if(isset($DST)) {
        $CONDICAO .= $DST;
    }
    if(isset($SRC)) {
        if(isset($DST)) {
            $CONDICAO .= " OR " . $SRC = substr($SRC, 4);
        }else{
            $CONDICAO .= $SRC;
        }        
    }
    
    /* Compara campos src e dst                                                   */
    $CONDICAO = do_field($CONDICAO,$src,$srctype,'src') ;
    $CONDICAO = do_field($CONDICAO,$dst,$dsttype,'dst') ;

    /* Clausula do where: Duracao da Chamada                                      */
    if ($duration1) {
        $CONDICAO .= " AND duration >= $duration1 ";
    } else {
        $CONDICAO .= " AND duration > 0 " ;
    }
    if ($duration2) {
        $CONDICAO .= " AND duration <= $duration2 " ;
    }

    /* Clausula do where:  Filtro de desccarte                                    */
    $TMP_COND = "" ;
    $dst_exceptions = explode(";", $dst_exceptions) ;
    foreach ($dst_exceptions as $valor) {
        $TMP_COND .= " dst != '$valor' " ;
        $TMP_COND .= " AND " ;
    }
    $CONDICAO .= " AND ( ".substr($TMP_COND, 0, strlen($TMP_COND) - 4). " ) " ;

    /* Clausula do where: // Centro de Custos Selecionado(s)                      */
    if (count($contas) > 0) {
        $TMP_COND = "" ;
        foreach( $contas as $valor ) {
            $TMP_COND .= " accountcode like '".$valor."%'";
            $TMP_COND .= " OR " ;
        }
        $contas = implode(",",$contas);
        if ($TMP_COND != "")
            $CONDICAO .= " AND ( ".substr($TMP_COND, 0, strlen($TMP_COND) - 3). " ) " ;
    }

    /* Clausula do where: Status/Tipo Ligacao                                     */
    if (($status_all) || ($status_ans && $status_noa && $status_bus && $status_fai)) {
        $CONDICAO .= "";
    }else {
        if ($status_ans && $status_noa && $status_bus) {
            $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_noa' ";
            $CONDICAO .= " OR disposition = '$status_bus' ) ";
        } elseif ($status_ans && $status_noa && $status_fai) {
            $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_noa' ";
            $CONDICAO .= " OR disposition = '$status_fai' ) ";
        } elseif ($status_ans && $status_fai && $status_bus) {
            $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_bus' ";
        } elseif ($status_noa && $status_bus && $status_fai) {
            $CONDICAO .= " AND ( disposition = '$status_noa' OR disposition = '$status_bus' ";
            $CONDICAO .= " OR disposition = '$status_fai' ) ";
        } elseif ($status_ans && $status_noa) {
            $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_noa' ) ";
        } elseif ($status_ans && $status_bus) {
            $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_bus' ) ";
        } elseif ($status_ans && $status_fai) {
            $CONDICAO .= " AND ( disposition = '$status_ans' OR disposition = '$status_fai' ) ";
        } elseif ($status_noa && $status_bus) {
            $CONDICAO .= " AND ( disposition = '$status_bus' OR disposition = '$status_noa' ) ";
        } elseif ($status_fai && $status_noa) {
            $CONDICAO .= " AND ( disposition = '$status_fai' OR disposition = '$status_noa' ) ";
        } elseif ($status_bus && $status_fai) {
            $CONDICAO .= " AND ( disposition = '$status_bus' OR disposition = '$status_fai' ) ";
        } elseif ($status_ans ) {
            $CONDICAO .= " AND ( disposition = '$status_ans' ) ";
        } elseif ($status_noa ) {
            $CONDICAO .= " AND ( disposition = '$status_noa' ) ";
        } elseif ($status_bus ) {
            $CONDICAO .= " AND ( disposition = '$status_bus' ) ";
        } elseif ($status_fai ) {
            $CONDICAO .= " AND ( disposition = '$status_fai' ) ";
        }
    }
    /* Clausula do where: Tipo de Chamada (Originada/Recebida/Outra))             */
    if ($call_type == "S") {                                                      // Chamadas Originadas
        $CONDICAO .= " AND (ccustos.tipo = 'S')" ;
    } elseif  ($call_type == "E") {  // Chamadas Recebidas
        $CONDICAO .= " AND (ccustos.tipo = 'E')" ;
    } elseif  ($call_type == "O") {  // Chamadas Outras
        $CONDICAO .= " AND (ccustos.tipo = 'O')" ;
    }

    /* Clausula do where: Prefixos de Login/Logout                                */
    if ( strlen( $prefix_inout ) > 3 ) {
        $COND_PIO = "" ;
        $array_prefixo = explode(";", $prefix_inout) ;
        foreach ($array_prefixo as $valor) {
            $par = explode("/", $valor);
            $pio_in = $par[0];
            $pio_out = $par[1];
            $t_pio_in = strlen($pio_in) ;
            $t_pio_out = strlen($pio_out) ;
            $COND_PIO .= " substr(dst,1,$t_pio_in) != '$pio_in' ";
            if (! $pio_out == '') {
                $COND_PIO .= " AND substr(dst,1,$t_pio_out) != '$pio_out' ";
            }
            $COND_PIO .= " AND " ;
        }
        if ($COND_PIO != "")
            $CONDICAO .= " AND ( ".substr($COND_PIO, 0, strlen($COND_PIO) - 4). " ) " ;
    }
    $CONDICAO .= " AND ( locate('ZOMBIE',channel) = 0 ) ";

    /* Montagem do SELECT de Consulta */
    $SELECT  = "ccustos.codigo,ccustos.tipo, date_format(calldate,\"%d/%m/%Y\") AS key_dia, date_format(calldate,\"%d/%m/%Y %H:%i:%s\") AS dia,  src, dst, disposition, duration, billsec, accountcode, userfield, dcontext, amaflags, uniqueid, calldate " ;
    $tot_tarifado = 0 ;
    
    /* Consulta de sql para verificar quantidade de registros selecionados e
     Montar lista de Totais por tipo de Status                                  */
    try {
        unset($duration, $billsec);
        $sql_ctds = "SELECT ".$SELECT." FROM cdr, ccustos $vinculo_table ";
        $sql_ctds .= " WHERE (cdr.accountcode = ccustos.codigo) AND $vinculo_where " . $CONDICAO ;
        $sql_ctds .= ($ramaissrc === null ? '' : $ramaissrc) . ($ramaisdst === null ? '' : $ramaisdst);
        $sql_ctds .= " ORDER BY calldate, userfield" ;

        if ($acao == "grafico") {
            $tot_fai = $tot_bus = $tot_ans = $tot_noa = $tot_oth = array();

        } else {
            $tot_fai = $tot_bus = $tot_ans = $tot_noa = $tot_bil = $tot_dur = $tot_oth = 0;
            
        }
        
        $flag_ini = True ;                                                        // Flag para controle do 1o. registro lido
        $userfield = "XXXXXXX" ;                                                  // Flag para controle do Userfield
        unset($result);

        foreach ($db->query($sql_ctds) as $row) {
            /* Incializa array se tipo = grafico                                   */
            $key_dia = $row['key_dia'] ;
            if ($acao == "grafico") {
                $tot_dias[$key_dia] = $key_dia ;
                $tot_ans[$key_dia] = (!array_key_exists($key_dia,$tot_ans)) ? 0 : $tot_ans[$key_dia];
                $tot_noa[$key_dia] = (!array_key_exists($key_dia,$tot_noa)) ? 0 : $tot_noa[$key_dia];
                $tot_bus[$key_dia] = (!array_key_exists($key_dia,$tot_bus)) ? 0 : $tot_bus[$key_dia] ;
                $tot_fai[$key_dia] = (!array_key_exists($key_dia,$tot_fai)) ? 0 : $tot_fai[$key_dia] ;
                $tot_oth[$key_dia] = (!array_key_exists($key_dia,$tot_oth)) ? 0 : $tot_oth[$key_dia] ;
            }
            /* Faz verificacoes para contabilizar valores dentro do mesmo userfield
            So vai contabilziar resultados por userfield                        */
            if ( $userfield != $row['userfield'] ) {
                if ($flag_ini) {
                    $result[$row['uniqueid']] = $row ;
                    $userfield = $row['userfield'] ;
                    $flag_ini = False ;
                    continue;
                }
            } else {
                $result[$row['uniqueid']] = $row ;
                continue ;
            }

            /* Varre o array da chamada com mesmo userfield                        */
            foreach ($result as $val) {
                switch ($val['disposition']) {
                    case "ANSWERED":
                        if ($acao == 'grafico')
                            $tot_ans[$key_dia] ++ ;
                        else
                        $tot_ans ++ ;
                        $tot_bil += $val['billsec'] ;
                        $tot_dur += $val['duration'] ;
                        if ($view_tarif === "yes") {
                            $valor = $my_object->fmt_tarifa(array("a"=>$val['dst'],"b"=>$val['billsec'],"c"=>$val['accountcode'],"d"=>$val['calldate']),"A") ;
                            $tot_tarifado += $valor ;
                        }
                        break ;
                    case "NO ANSWER":
                        if ($acao == 'grafico') {
                            $tot_noa[$key_dia] ++ ;
                        } else {
                            $tot_noa ++ ;
                        }
                        break ;
                    case "BUSY" :
                        if ($acao == 'grafico') {
                            $tot_bus[$key_dia] ++;
                        } else {
                            $tot_bus ++ ;
                        }
                        break ;
                    case "FAILED" :
                        if ($acao == 'grafico') {
                            $tot_fai[$key_dia] ++;
                        } else {
                            $tot_fai ++ ;
                        }
                        break ;
                    default :
                        if ($acao == 'grafico') {
                            $tot_oth[$key_dia] ++;
                        } else {
                            $tot_oth ++ ;
                        }
                        break ;
                }                                                                   // Fim do Switch
            }                                                                      // Fim do Foreach do array "result"
            unset($result) ;
            $result[$row['uniqueid']] = $row ;
            $userfield = $row['userfield'] ;
        }                                                                         // Fim do Foreach

        /* Switch a seguir é para pegar um possível último registro               */

        foreach ($result as $val) {
            switch ($val['disposition']) {
                case "ANSWERED":
                    if ($acao == 'grafico') {
                        $tot_ans[$key_dia] ++ ;
                    } else {
                        $tot_ans ++ ;
                        $tot_bil += $val['billsec'] ;
                        $tot_dur += $val['duration'] ;
                        if ($view_tarif === "yes") {
                            $valor = $my_object->fmt_tarifa(array("a"=>$val['dst'],"b"=>$val['billsec'],"c"=>$val['accountcode'],"d"=>$val['calldate']),"A") ;
                            $tot_tarifado += $valor ;
                        }
                    }
                    break ;
                case "NO ANSWER":
                    if ($acao == 'grafico') {
                        $tot_noa[$key_dia] ++ ;
                    } else {
                        $tot_noa ++ ;
                    }
                    break ;
                case "BUSY" :
                    if ($acao == 'grafico') {
                        $tot_bus[$key_dia] ++;
                    } else {
                        $tot_bus ++ ;
                    }
                    break ;
                case "FAILED" :
                    if ($acao == 'grafico') {
                        $tot_fai[$key_dia] ++;
                    } else {
                        $tot_fai ++ ;
                    }
                    break ;
                default :
                    if ($acao == 'grafico') {
                        $tot_oth[$key_dia] ++;
                    } else {
                        $tot_oth ++ ;
                    }
                    break ;
            }                                                                      // Fim do Switch
        }                                                                         // Fim do Foreach do array result para possivel ultimo registro

    } catch (Exception $e) {
        display_error($LANG['error'].$e->getMessage().$sql_chamadas,true) ;
        exit ;
    }

    if ( $acao == "relatorio") {
        if ( ($tot_fai+$tot_bus+$tot_ans+$tot_noa) == 0) {
            display_error($LANG['msg_notdata'],true) ;
            exit ;
        }
        $tot_wait = $tot_dur - $tot_bil ;
        $totais = array("answered"    =>   $tot_ans,
                        "notanswer"   =>   $tot_noa,
                        "busy"        =>   $tot_bus,
                        "fail"        =>   $tot_fai,
                        "billsec"     =>   $tot_bil,
                        "duration"    =>   $tot_dur,
                        "espera"      =>   $tot_wait,
                        "oth"         => $tot_oth,
                        "tot_tarifado"=>   $tot_tarifado );
                     // "tot_tarifado"=>number_format($tot_tarifado,2,",","."));
    }else {
        if ( count($tot_fai) == 0 && count($tot_bus) == 0 &&
                count($tot_ans) == 0 && count($tot_noa) == 0 &&
                count($tot_oth) == 0 ) {
            display_error($LANG['msg_notdata'],true) ;
            exit ;
        }

        $totais = array("ans"  => $tot_ans,  "noa" => $tot_noa,
                "bus"  => $tot_bus,  "fai" => $tot_fai,
                "dias" => $tot_dias, "dur" => $tot_dur,
                "bil"  => $tot_bil);
    }

    /* Define um SQL de Exibicao no Template, agrupado e com ctdor de agrupamentos */
    $sql_chamadas = "SELECT count(userfield) as qtdade,".$SELECT." FROM cdr, ccustos $vinculo_table ";
    $sql_chamadas .= " WHERE (cdr.accountcode = ccustos.codigo) AND $vinculo_where " . $CONDICAO;
    $sql_chamadas .= ($ramaissrc === null ? '' : $ramaissrc) . ($ramaisdst === null ? '' : $ramaisdst);

    switch($ordernar) {
        case "data":
            $ordernar = " calldate ";
            break;
        case "src":
            $ordernar = " src, calldate ";
            break;
        case "dst":
            $ordernar = "  dst, calldate ";
            break;
    }

    $sql_chamadas .= " GROUP BY userfield ORDER BY $ordernar " ;

    $_SESSION['view_files'] = $_POST['view_files'];
    $_SESSION['sql_chamadas'] = $sql_chamadas ;
    $_SESSION['totais'] = $totais ;
    $_SESSION['titulo_2'] = $LANG['periodo'].": ".$dia_ini." (".$hora_ini.") a ". $dia_fim." (".$hora_fim.")";
    $_SESSION['parametros'] = array("tpgraf" => $graph_type,
            "status" => "ALL",
            "rel_type" => $rel_type,
            "titulo" => $LANG['callperiod']."-". $tipos_chamadas_rel[$call_type],
            "view_tarif"=>$view_tarif) ;

    echo "<meta http-equiv='refresh'  content='0; url=./rel_chamadas.php?acao=imp&t=$acao'>\n" ;
}

/*------------------------------------------------------------------------------
 Funcao exibe_relatorio - Exibe o Relatorio
------------------------------------------------------------------------------*/
function exibe_relatorio() {

    global $db, $smarty, $SETUP, $LANG, $tipos_disp, $tp_rel;

    $tp_rel = (isset( $_GET['t'] ) ? $_GET['t'] : '' );

    if ($tp_rel == "grafico") {
        $tot_tmp = $_SESSION['totais'] ;
        /* Ajusta o Array para coordenadas do Grafico        */
        foreach ($tot_tmp as $k => $v) {
            $i = 1 ;
            $tot = 0 ;
            foreach ($v as $k1 => $v1) {
                $totais[$k][$i] = $v1;
                $i ++;
                $tot += $v1 ;
            }
            $totais[$k][0] = number_format($tot / $i,2,',','.');
        }
        //$_SESSION['totais'] = $totais ;

        $_SESSION['totaisgraf'] = $totais ;

        // echo "<meta http-equiv='refresh' content='0;url=../gestao/graf_chamadas.php'>\n" ;
    }

    if ($tp_rel == "csv") {
        try {
            $sql = $_SESSION['sql_chamadas'];
            $row = $db->query($sql)->fetchAll();

        } catch (Exception $e) {
            display_error($LANG['error'].$e->getMessage(),true);
            exit;
        }

        /*  Array que define os campos (ids) e os titulos (values) do CSV.      */
        $titulo = array("dia" => $LANG['csv_data'],
                "src" => $LANG['csv_origem'],
                "dst" => $LANG['csv_destino'],
                "disposition" => $LANG['csv_status'],
                "duration" => $LANG['csv_duracao'],
                "billsec" => "Conversacao",
                "accountcode" => $LANG['csv_ccusto'],
                "origem" => $LANG['csv_city']
        );

        /* Adiciona ou Não um indice ao Array $titulo referente a tarifação.    */

        ( $_SESSION['parametros']['view_tarif']  === "yes" ? $titulo['tarifacao'] = $LANG['csv_tarifacao'] : null);

        /* Chama função monta_csv, passando array $titulo e o resultado da query $row */

        $csv_rel_chamadas = monta_csv($titulo, $row);

    } else {

        /* No caso de ser sintético                                                 */
        if ($_SESSION['relchamadas']['rel_type'] == 'sintetico') {

            /* Tratamento do Centro de custo                                        */
            $cc = $_SESSION['relchamadas']['ccustos'];
            if($cc != '') {
                $valores = '';
                foreach($cc as $value) {
                    $valores .= "'$value'".",";
                }
                $sqlcc = "select nome from ccustos where codigo IN (" . substr($valores, 0 , -1) . ")";
                $ccs = $db->query($sqlcc)->fetchAll(PDO::FETCH_ASSOC);
                $ccusto_sintetic = '';
                foreach($ccs as $id => $value) {
                    $ccusto_sintetic .= $ccs[$id]['nome'].", ";
                }
            }else {
                $ccusto_sintetic = $LANG['any'].",";
            }
            $smarty->assign('sintetic_cc',( $ccusto_sintetic ? substr($ccusto_sintetic, 0, -1) : ''));

            /* Tratamento de destino                                                */
            $sint_destino = $_SESSION['relchamadas']['dst'];
            $sint_groupdst = $_SESSION['relchamadas']['groupdst'];

            if($sint_destino != '' && $sint_groupdst == '') {
                $sint_dest = $sint_destino;
            }

            if ($sint_groupdst != '' && $sint_destino == '') {
                $sqldst = "select name from peers where peers.group = '$sint_groupdst' "  ;
                $sint_dst = $db->query($sqldst)->fetchAll(PDO::FETCH_ASSOC);
                $sint_dest = '';
                foreach($sint_dst as $id => $value) {
                    $sint_dest .= $sint_dst[$id]['name'].", ";
                }
            }
            $smarty->assign('sinteticdst',$sint_dest);

            // Tratamento de origem
            $sint_origem = $_SESSION['relchamadas']['src'];
            $sint_groupsrc = $_SESSION['relchamadas']['groupsrc'];
            if($sint_origem != '' && $sint_groupsrc == '') {
                $src_sintetic = trim($sint_origem);
            }
            if ($sint_groupsrc != '' && $sint_origem == '') {
                $sqlsrc = "select name from peers where peers.group = '$sint_groupdst' "  ;
                $sint_src = $db->query($sqlsrc)->fetchAll(PDO::FETCH_ASSOC);
                $src_sintetic = '';
                foreach($sint_src as $id => $value) {
                    $src_sintetic .= $sint_src[$id]['name'].", ";
                }
            }
            $smarty->assign('sinteticdst',$src_sintetic);

            /*/* Tratamento Status                                                  */
            $st_all = $_SESSION['relchamadas']['status_all'];
            $st_ans = $_SESSION['relchamadas']['status_ans'];
            $st_noa = $_SESSION['relchamadas']['status_noa'];
            $st_bus = $_SESSION['relchamadas']['status_bus'];
            $st_fai = $_SESSION['relchamadas']['status_fai'];
            $status_sintetic = '';

            if($st_all) {
                $status_sintetic = $LANG['all'];
            }else {
                if($st_ans) {
                    $status_sintetic .= " ". $LANG['answered'];
                }
                if($st_noa) {
                    $status_sintetic .= " " .$LANG['notanswered'];
                }
                if($st_bus) {
                    $status_sintetic .= " " .$LANG['busys'];
                }
                if($st_fai) {
                    $status_sintetic .= " " .$LANG['fail'];
                }
            }
            $smarty->assign('sintetic_status',$status_sintetic);
            // Tratamento
        }

        $totais = $_SESSION['totais'] ;

        try {
            $sql = $_SESSION['sql_chamadas'] ;
            $row = $db->query($sql)->fetchAll();

        } catch (Exception $e) {
            display_error($LANG['error'].$e->getMessage(),true) ;
            exit ;
        }
    }

    // Cria Objeto para formtacao de dados
    $my_object = new Formata ;
    $smarty->register_object("formata",$my_object) ;

    // Paginacao
    $tot_pages = ceil(count($row) / $SETUP['ambiente']['linelimit']) ;
    for ($i = 1 ; $i <= $tot_pages ; $i ++ )
        $paginas[$i] = $i;

    // Paginacao do select.
    $num = $tot_pages/10;
    $inicio = 1;
    $arrNum = array();
    while($inicio <= $tot_pages) {
        $jump[round($inicio)] = round($inicio);
        $inicio += $num;
    }

    $tmp =  ver_permissao(82,"", True);

    $smarty->assign ('EXCLUIR_ICON', $tmp);
    $smarty->assign ('CCUSTOS', $_SESSION['ccusto']);
    $smarty->assign ('DADOS', $row) ;
    $smarty->assign ('rel_type', $_SESSION['relchamadas']['rel_type']);
    $smarty->assign ('PAGINAS', $paginas) ;
    $smarty->assign ('JUMP', $jump) ;
    $smarty->assign ('INI',1);
    $smarty->assign ('PROTOTYPE', True);
    $smarty->assign ('ARQCVS', ( isset($csv_rel_chamadas) ? $csv_rel_chamadas : '') );
    $smarty->assign ('TOT', $tot_pages);
    $smarty->assign ('TIPOS_DISP', $tipos_disp);
    $smarty->assign ('TOTAIS', $totais);
    $smarty->assign ('TP_GRAPH', $_SESSION['parametros']['tpgraf'] );
    $smarty->assign ('TPREL', $tp_rel);
    $smarty->assign ('VIEW_FILES', $_SESSION['view_files']);
    $smarty->assign ('VIEW_TARIF', $_SESSION['parametros']['view_tarif']);
    $titulo = $LANG['menu_reports']." » ". $LANG['menu_rel_callers']."<br />" ;
    $titulo.= $_SESSION['titulo_2'];
    display_template("rel_chamadas_view.tpl", $smarty,$titulo) ;
?>
<script language="javascript" type="text/javascript">
    /*------------------------------------------------------------------------------
     * Funcao para remover arquivo de gravacao
     *------------------------------------------------------------------------------*/
    function rem_arq(arquivo,mensagem) {
        if (confirm(mensagem+': ' +arquivo+' ?')) {
            endereco='../pbx/remover_arquivo.php?arquivo='+arquivo;
            parent.location.href=endereco;
            //parent.location.href='../src/manutencao.php';
            return true;
        } else {
            return false;
        }
    }
    /*------------------------------------------------------------------------------
     * Compacta varios arquivos de gravacao em um arquivo zip
     *------------------------------------------------------------------------------*/
    function compactCheckeds() {
        var form=$('tabela');
        var i=form.getElements('checkbox');
        var dados = '';
        i.each(function(item) {
            if (item.checked) {
                dados += item.value + ",";
            }
        });

        var url = 'compacta_arquivos.php';
        var params = 'arquivos='+ dados;
        var retorno = new Ajax.Request (
        url, {
            method: 'post',
            parameters: params,
            onComplete: alerta
        }
    );
    }
    /*------------------------------------------------------------------------------
     * Exibe alerta apos a compactacao
     *------------------------------------------------------------------------------*/
    function alerta( arq) {
        var arq = arq.responseText;
        if(arq == 0) {
            alert("<?php echo $LANG['no_compact_files'] ?> ");
        }else{
            alert("<?php echo $LANG['compact_files'] ?> " + arq );
        }
    }
    /*------------------------------------------------------------------------------
     * Mais informacoes sobre a chamada
     *------------------------------------------------------------------------------*/
    function moreinfo(userfield, id, view) {

        if($('eg'+id).style.display == "") {
            $('eg'+id).hide();

            $('more'+id).removeClassName('menos');
            $('more'+id).addClassName('mais');
        }else{
            $('more'+id).removeClassName('mais');
            $('more'+id).addClassName('menos');

            var url = '../includes/moreinfo.php';
            var params = 'userfield='+userfield+'&view_tarif='+view;

            $('selected').value = id;
            $('eg'+id).show();
            //$('more'+id).hide();

            var retorno = new Ajax.Request (
            url, {
                method: 'post',
                parameters: params,
                onComplete: registros
            }
        );
        }
    }

    function registros(resp) {
        var id = $('selected').value;
        var html = resp.responseText;

        $('reg'+id).innerHTML = html;
    }
</script>
    <?php
    exit ;
}
?>
