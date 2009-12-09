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
ver_permissao(66);

$acao = isset($_GET['acao']) ? $_GET['acao'] : "";

if ($acao ==  "app") {
    app($_GET['app']);
} else {
    principal();
}

/*------------------------------------------------------------------------------
 Funcao principal - mostra lista de aplicações
------------------------------------------------------------------------------*/
function principal(){
    global $smarty, $LANG;
    $titulo = $LANG['avail_apps'];

    $_app_list = ast_status('core show applications', '', true);

    $_app_list = strstr($_app_list, '=-');

    preg_match_all("/(?<app>\w+): (?<desc>.*)/", $_app_list, $_app_list);


    //Removendo os ultimos 2 elementos
    foreach($_app_list as $key => $value){
        unset($_app_list[$key][count($value)-1]);
        unset($_app_list[$key][count($value)-2]);
    }

    $app_list['app'] = $_app_list['app'];
    $app_list['desc'] = $_app_list['desc'];

    $smarty->assign('APP_LIST', $app_list['app']);
    $smarty->assign('APP_DESC', $app_list['desc']);
    display_template("app_list.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
 Funcao app - Lista help para app especifica
------------------------------------------------------------------------------*/
function app($app){
    global $smarty, $LANG;
    
    $_app_help = ast_status('core show application ' . $app, '', true);
    $_app_help = strstr($_app_help, '[Description]');
    $_app_help = substr($_app_help, 13);
    $_app_help = substr($_app_help, 0, strpos($_app_help, '--END COMMAND--'));
    $app_help = nl2br($_app_help);

    
    $titulo = $LANG['help_for'] . $app;
    $smarty->assign('DESCRIPTION', $app_help);
    display_template("app_help.tpl",$smarty,$titulo);
}
?>
