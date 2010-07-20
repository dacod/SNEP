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
ver_permissao(105);

if (!$data = ast_status("show channels","",True )) {
   display_error($LANG['msg_nosocket'], true) ;
   exit;
}

$titulo = $LANG['menu_links_erros'] ;
$smarty->assign ('REFRESH',array('mostrar'=> false,
                              'tempo'  => $SETUP['ambiente']['tempo_refresh'],
                              'url'    => "../gestao/links_errors.php"));
$titulo = $LANG['menu_links_erros'];
display_template("cabecalho.tpl",$smarty,$titulo) ;
