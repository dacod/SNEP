<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
ver_permissao(103);

$acao = ( isset($acao) ? $acao : '' );
if ($acao == "relatorio") {
    verlog();
}
if ($acao == "tail") {
    vertail();
}

// Dados iniciais do formulário.
$dados_iniciais = array('dataini' => ( isset($_SESSION['sneplog']['diaini']) ? $_SESSION['sneplog']['diaini'] : date('d/m/Y', strtotime("-1 days")) ),
    'datafim' => ( isset($_SESSION['sneplog']['diafim']) ? $_SESSION['sneplog']['diafim'] : date('d/m/Y')),
    'horaini' => ( isset($_SESSION['sneplog']['horaini']) ? $_SESSION['sneplog']['horaini'] : "00:00"),
    'horafim' => ( isset($_SESSION['sneplog']['horafim']) ? $_SESSION['sneplog']['horafim'] : "23:59"),
    'status' => 'todos');

$status = array('all' => ( isset($_SESSION['sneplog']['statusall']) ? $_SESSION['sneplog']['statusall'] : '' ),
    'alert' => ( isset($status_alert) ? $status_alert : ''),
    'cri' => ( isset($status_cri) ? $status_cri : ''),
    'err' => ( isset($status_err) ? $status_err : ''),
    'inf' => ( isset($status_inf) ? $status_inf : ''),
    'deb' => ( isset($status_deb) ? $status_deb : ''));

// Recupera variáveis em sessão.
$src = (isset($_SESSION['sneplog']['src']) ? $_SESSION['sneplog']['src'] : '');
$dst = (isset($_SESSION['sneplog']['dst']) ? $_SESSION['sneplog']['dst'] : '');

$titulo = $LANG['menu_status'] . " » " . $LANG['tit_logger'];
$smarty->assign('status', $status);
$smarty->assign('dados', $dados_iniciais);
$smarty->assign('src', $src);
$smarty->assign('dst', $dst);

display_template("sneplog.tpl", $smarty, $titulo);

// Apresenta Relatório conforme parametros escolhidos.
function verlog() {

    global $LANG, $db, $smarty, $dia_ini, $dia_fim, $hora_ini, $hora_fim, $status_all, $status_alert, $status_cri, $status_err, $status_deb, $status_inf, $src, $dst;

    // Salva informações na sessão.
    $_SESSION['sneplog']['diaini'] = $dia_ini;
    $_SESSION['sneplog']['diafim'] = $dia_fim;
    $_SESSION['sneplog']['horaini'] = $hora_ini;
    $_SESSION['sneplog']['horafim'] = $hora_fim;
    $_SESSION['sneplog']['statusall'] = $status_all;
    $_SESSION['sneplog']['statusalert'] = $status_alert;
    $_SESSION['sneplog']['statuscri'] = $status_cri;
    $_SESSION['sneplog']['statuserr'] = $status_err;
    $_SESSION['sneplog']['statusinf'] = $status_inf;
    $_SESSION['sneplog']['statusdeb'] = $status_deb;
    $_SESSION['sneplog']['src'] = $src;
    $_SESSION['sneplog']['dst'] = $dst;
    $st = array('all' => $status_all,
        'alert' => $status_alert,
        'cri' => $status_cri,
        'err' => $status_err,
        'inf' => $status_inf,
        'deb' => $status_deb);

    // Instancia classe Snep_log e submete pesquisa.
    $log = new Snep_Log($smarty->agi_log, 'agi.log');

    if ($log != 'error') {
        $result = $log->getLog($dia_ini, $dia_fim, $hora_ini, $hora_fim, $st, $src, $dst);
    } else {
        display_error($LANG['error_logfile'], true);
        exit;
    }

    if (count($result) <= 0) {
        display_error($LANG['error_lognoresult'], true);
    }
    $titulo = $LANG['menu_status'] . " » " . $LANG['tit_logger'] . " » " . $LANG['tit_logger_find'];

    $smarty->assign('type', 'log');
    $smarty->assign('resultado', $result);
    $smarty->assign('PROTOTYPE', True);
    display_template("sneplog_view.tpl", $smarty, $titulo);
    exit;
}

// Apresenta somente tail das ultimas 50 linhas do log.
function vertail() {

    global $LANG, $db, $smarty;

    // Esta função exibe um template e as ações de busca no arquivo são feitos em ajax, chamando
    // o script sneplogtail.php.
    $titulo = $LANG['menu_status'] . " » " . $LANG['tit_logger'] . " » " . $LANG['tit_logger_tail'];
    $smarty->assign('type', 'tail');
    $smarty->assign('PROTOTYPE', True);
    display_template("sneplog_view.tpl", $smarty, $titulo);
    exit;
}
