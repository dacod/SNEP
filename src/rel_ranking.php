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

ver_permissao(29);

global $acao, $prefix_inout;
$prefix_inout = $SETUP['ambiente']['prefix_inout'];

if ($acao == "relatorio" || $acao == "csv") {
    monta_relatorio($acao);
} elseif ($acao == "imp") {
    exibe_relatorio();
}

$nivel = Snep_Vinculos::getNivelVinculos($_SESSION['name_user']);

$dados_iniciais = array("dia_ini" => ( isset($_SESSION['relrank']['dia_ini']) ? $_SESSION['relrank']['dia_ini'] : "01/" . date('m/Y') ),
    "dia_fim" => ( isset($_SESSION['relrank']['dia_fim']) ? $_SESSION['relrank']['dia_fim'] : "01/" . date('m/Y') ),
    "hora_ini" => ( isset($_SESSION['relrank']['hora_ini']) ? $_SESSION['relrank']['hora_ini'] : "00:00"),
    "hora_fim" => ( isset($_SESSION['relrank']['hora_fim']) ? $_SESSION['relrank']['hora_fim'] : "23:59")
);

for ($i = 1; $i <= 30; $i++) {
    $viewtop[$i] = $i;
}
$titulo = $LANG['menu_reports'] . " » " . $LANG['menu_callranking'];

$smarty->assign('rank_num', ( isset($_SESSION['relrank']['rank_num']) ? $_SESSION['relrank']['rank_num'] : ''));
$smarty->assign('rank_type', ( isset($_SESSION['relrank']['rank_type']) ? $_SESSION['relrank']['rank_type'] : 'qtdade'));
$smarty->assign('viewtop', ( isset($_SESSION['relrank']['viewtop']) ? $_SESSION['relrank']['viewtop'] : '10'));
$smarty->assign('OPCOES_YN', $tipos_yn);
$smarty->assign('dt_ranking', $dados_iniciais);
$smarty->assign('VIEWTOP', $viewtop);
$smarty->assign('NIVEL', $nivel);
$smarty->assign('OPCOES_RANK', array("qtdade" => $LANG['rank_qtdade'], "tempo" => $LANG['rank_time']));
display_template("rel_ranking.tpl", $smarty, $titulo);

/* -----------------------------------------------------------------------------
 * Funcao Relatorio - Monta o relatorio na rela
 * ---------------------------------------------------------------------------- */

function monta_relatorio() {

    global $LANG, $SETUP, $db, $smarty, $dia_ini, $dia_fim, $hora_ini, $hora_fim, $prefix_inout, $dst_exceptions, $filter, $tipos_chamadas, $rank_type, $viewtop, $rank_num, $acao;

    $_SESSION['relrank']['dia_ini'] = $dia_ini;
    $_SESSION['relrank']['dia_fim'] = $dia_fim;
    $_SESSION['relrank']['hora_ini'] = $hora_ini;
    $_SESSION['relrank']['hora_fim'] = $hora_fim;
    $_SESSION['relrank']['rank_type'] = $rank_type;
    $_SESSION['relrank']['rank_num'] = $rank_num;
    $_SESSION['relrank']['viewtop'] = $viewtop;

    //---->>>> Primeira clausula do where: periodos inicial e final <<<<----//
    $dia_inicial = substr($dia_ini, 6, 4) . "-" . substr($dia_ini, 3, 2) . "-" . substr($dia_ini, 0, 2);
    $dia_final = substr($dia_fim, 6, 4) . "-" . substr($dia_fim, 3, 2) . "-" . substr($dia_fim, 0, 2);
    $date_clause = " ( calldate >= '$dia_inicial'";
    $date_clause.=" AND calldate <= '$dia_final 23:59:59'"; //'
    $date_clause.=" AND DATE_FORMAT(calldate,'%T') >= '$hora_ini:00'";
    $date_clause.=" AND DATE_FORMAT(calldate,'%T') <= '$hora_fim:59') ";
    $TIT_DATE = $LANG['periodo'] . ": " . $dia_ini . " (" . $hora_ini . ") a " . $dia_fim . " (" . $hora_fim . ")";
    $CONDICAO = " WHERE $date_clause";

    //$CONDICAO .= sql_vinculos($src,$dst,$orides,$srctype,$dsttype) ;
    //---->>>> Prefixos de Login/Logout de agentes
    if (strlen($prefix_inout) > 6) {
        $COND_PIO = "";
        $array_prefixo = explode(";", $prefix_inout);
        foreach ($array_prefixo as $valor) {

            $par = explode("/", $valor);
            $pio_in = $par[0];
            $pio_out = $par[1];
            $t_pio_in = strlen($pio_in);
            $t_pio_out = strlen($pio_out);
            $COND_PIO .= " substr(dst,1,$t_pio_in) != '$pio_in' ";
            if (!$pio_out == '') {
                $COND_PIO .= " AND substr(dst,1,$t_pio_out) != '$pio_out' ";
            }
            $COND_PIO .= " AND ";
        }
        if ($COND_PIO != "") {
            $CONDICAO .= " AND ( " . substr($COND_PIO, 0, strlen($COND_PIO) - 4) . " ) ";
        }
    }
    //---->>>> Filtro de Descarte

    $TMP_COND = "";
    $dst_exceptions = $SETUP['ambiente']['dst_exceptions'];
    $dst_exceptions = explode(";", $dst_exceptions);
    foreach ($dst_exceptions as $valor) {
        $TMP_COND .= " dst != '$valor' ";
        $TMP_COND .= " AND ";
    }
    $CONDICAO .= " AND ( " . substr($TMP_COND, 0, strlen($TMP_COND) - 4) . " ) ";

    /* Verificando existencia de vinculos no ramal */
    $name = $_SESSION['name_user'];
    $sql = "SELECT id_peer, id_vinculado FROM permissoes_vinculos WHERE id_peer ='$name'";
    $result = $db->query($sql)->fetchObject();

    $vinculo_table = "";
    $vinculo_where = "";
    if ($result) {
        $vinculo_table = " ,permissoes_vinculos ";
        $vinculo_where = "  AND ( permissoes_vinculos.id_peer='{$result->id_peer}' AND (cdr.src = permissoes_vinculos.id_vinculado ) )  ";
    }

    $CONDICAO .= " AND ( locate('ZOMBIE',channel) = 0 ) AND userfield != '' ";
    //---->>>> Pegar somente ramais cadastros na tabela peers
    //$CONDICAO .= " AND src IN (SELECT name from peers) " ;
    // Monta SQL da selecao
    $sql = "SELECT cdr.src, cdr.dst, cdr.disposition, cdr.duration, cdr.billsec, cdr.userfield ";
    $sql .= " FROM cdr $vinculo_table " . $CONDICAO . $vinculo_where . " ORDER BY cdr.calldate, cdr.userfield, cdr.amaflags";


    try {

        $flag = $disposition = "";
        $destino = "";
        $quebra = False;
        unset($duration, $billsec);
        $duration = 0;

        $dados = $db->query($sql)->fetchAll();

        foreach ($dados as $row) {

            // Trata das Chamadas - Quantidades
            if ($flag == $row['userfield']) {
                $disposition = $row['disposition'];
                $src = $row['src'];
                $dst = $row['dst'];
                $quebra = False;
                continue;
            } else {

                $destino = $row['dst'];
                if (!isset($disposition) || $disposition == "") { // Primeira vez
                    $flag = $row['userfield'];
                    $disposition = $row['disposition'];
                    $src = $row['src'];
                    $dst = $row['dst'];
                    $quebra = False;
                    continue;
                }
                $quebra = True;
            }

            // Inicializa todos indices do array
            if (!isset($dados[$src][$dst])) {
                $dados[$src][$dst]["QA"] = 0;
                $dados[$src][$dst]["QN"] = 0;
                $dados[$src][$dst]["QT"] = 0;
                $dados[$src][$dst]["TA"] = 0;
                $dados[$src][$dst]["TN"] = 0;
                $dados[$src][$dst]["TT"] = 0;
                $totais_q[$src] = 0;
                $totais_t[$src] = 0;
            }

            switch ($disposition) {
                case "ANSWERED":
                    $dados[$src][$dst]["QA"]++;
                    $dados[$src][$dst]["TA"] += $duration;
                    break;
                default:
                    $dados[$src][$dst]["QN"]++;
                    $dados[$src][$dst]["TN"] += $duration;
                    break;
            } // Fim do switch
            $dados[$src][$dst]["QT"]++;
            $dados[$src][$dst]["TT"] += $duration;
            $totais_q[$src]++;
            $totais_t[$src] += $duration;

            $disposition = $row['disposition'];
            $src = $row['src'];
            $dst = $row['dst'];
            $duration = $row['duration'];
            unset($quebra);
        } // Fim do Foreach que varre o SELECT do CDR

        if (!isset($dados[$src][$dst])) {
            $dados[$src][$dst]["QA"] = 0;
            $dados[$src][$dst]["QN"] = 0;
            $dados[$src][$dst]["QT"] = 0;
            $dados[$src][$dst]["TA"] = 0;
            $dados[$src][$dst]["TN"] = 0;
            $dados[$src][$dst]["TT"] = 0;
            $totais_q[$src] = 0;
            $totais_t[$src] = 0;
        }
        switch ($disposition) {
            case "ANSWERED":
                $dados[$src][$dst]["QA"]++;
                $dados[$src][$dst]["TA"] += $duration;
                break;
            default:
                $dados[$src][$dst]["QN"]++;
                $dados[$src][$dst]["TN"] += $duration;
                break;
        } // Fim do switch
        $dados[$src][$dst]["QT"]++;
        $dados[$src][$dst]["TT"] += $duration;
        $totais_q[$src]++;
        $totais_t[$src] += $duration;
    } catch (Exception $e) {
        display_error($LANG['error'] . $e->getMessage(), true);
        exit;
    }
    if (count($dados) <= 1) {
        display_error($LANG['msg_notdata'], true);
        exit;
    }
    arsort($totais_q);
    arsort($totais_t);
    // Rankear conforme selecao
    $tot_view = $rank_num - 1;
    if ($rank_type == "qtdade") {
        foreach ($totais_q as $src => $qtd) {
            $ctd = $viewtop;
            foreach ($dados[$src] as $dst => $val) {
                if ($ctd == 0)
                    break;
                $ctd--;
                $rank[$src] [$val['QT']] [$dst] = $val;
            }
            // Numero de origens a exibir
            if ($tot_view == 0)
                break;
            $tot_view--;
        }
    } else {
        foreach ($totais_t as $src => $qtd) {
            $ctd = $viewtop;
            foreach ($dados[$src] as $dst => $val) {
                if ($ctd == 0)
                    break;
                $ctd--;
                $rank[$src] [$val['TT']] [$dst] = $val;
            }
            // Numero de origens a exibir
            if ($tot_view == 0)
                break;
            $tot_view--;
        }
    }
    // ordenar por SRC + Quantidades

    foreach ($rank as $src => $vqtd) {
        krsort($vqtd);
        foreach ($vqtd as $qtd => $vdst) {
            foreach ($vdst as $dst => $val) {
                $rank_final[$src][$qtd][$dst] = $val;
            }
        }
    }
    $_SESSION['tit_date'] = $TIT_DATE;
    $_SESSION['totais_t'] = $totais_t;
    $_SESSION['totais_q'] = $totais_q;
    $_SESSION['rank_final'] = $rank;

    echo "<meta http-equiv='refresh'  content='0; url=./rel_ranking.php?acao=imp&t=$acao'>\n";
}

function exibe_relatorio() {

    $totais_t = $_SESSION['totais_t'];
    $totais_q = $_SESSION['totais_q'];
    $rank_final = $_SESSION['rank_final'];
    $TIT_DATE = $_SESSION['tit_date'];
    $tp_rel = $_GET['t'];
    $rank_type = $_SESSION['relrank']['rank_type'];


    global $smarty, $SETUP, $LANG, $tipos_disp, $acao;

    if ($tp_rel == "csv") {

        $rank_geral = array();

        foreach ($rank_final as $chaves => $valores) {
            $rank = array();
            $rank['origem'] = $chaves;
            foreach ($valores as $key => $value) {
                foreach ($value as $k => $v) {
                    $rank['destino'] = $k;
                    $rank['QA'] = $v['QA'];
                    $rank['QN'] = $v['QN'];
                    $rank['TA'] = $v['TA'];
                    $rank['TN'] = $v['TN'];
                    $rank_geral[] = $rank;
                }
            }
        }
        $titulo = array(
            "origem" => $LANG['csv_origem'],
            "destino" => $LANG['csv_destino'],
            "QA" => $LANG['csv_qt_atendida'],
            "QN" => $LANG['csv_qt_natendida'],
            "TA" => $LANG['csv_temp_ate'],
            "TN" => $LANG['csv_temp_nate']
        );

        /* Chama função monta_csv, passando array $titulo e o resultado da query $row */
        $csv_rel_ranking = monta_csv($titulo, $rank_geral);
    }

    // Cria Objeto para formatacao de dados
    $titulo = $LANG['menu_reports'] . " » " . $LANG['menu_callranking'] . "<br />";
    $titulo.= $TIT_DATE;
    $my_object = new Formata;
    $smarty->register_object("formata", $my_object);
    if ($rank_type == "qtdade") {
        $smarty->assign('TOTAIS', $totais_q);
    } else {
        $smarty->assign('TOTAIS', $totais_t);
    }
    $smarty->assign('DADOS', $rank_final);
    $smarty->assign('ARQCVS', ( isset($csv_rel_ranking) ? $csv_rel_ranking : ''));
    $smarty->assign('TPREL', $tp_rel);
    $smarty->assign('RANKTYPE', $rank_type);

    display_template("rel_ranking_view.tpl", $smarty, $titulo);
    exit;
}

