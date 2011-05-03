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
 ver_permissao(35);

/*-----------------------------------------------------------------------------
 * Funcao visualizar - Exibe relacao de filas
 *-----------------------------------------------------------------------------*/
  
  global $smarty, $SETUP, $filas_selec, $LANG, $view_ps;

  $titulo = $LANG['menu_status']." » ".$LANG['menu_databaseshow']." » ".$LANG['view'];
  $smarty->assign ('REFRESH',array('mostrar'=> true,
                                   'tempo'  => 10,
                                   'url'    => "../src/database_show.php"));
  display_template("cabecalho.tpl",$smarty,$titulo) ;
  exit ;
 
