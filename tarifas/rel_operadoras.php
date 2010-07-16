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
 
 ver_permissao(38) ;

 $titulo = $LANG['menu_tarifas']." » ".$LANG['menu_operadoras'] ;
 $opcoes = array( "nome" => $LANG['name'], "codigo" => $LANG['id']) ;

 ( is_null( $_POST['text_filter'] ) ? $text_filter = "" : $text_filter = $_POST['text_filter'] );

 if ( $text_filter != "" ) {
    $row = Snep_Operadoras::getFiltrado($text_filter, $text_filter);

 }else{
    $row = Snep_Operadoras::getFiltrado(null, null);

 }

 $smarty->assign ('DADOS',                $row );
 $smarty->assign ('view_filter',          True );
 $smarty->assign ('view_include_buttom',  True );
 $smarty->assign ('OPCOES',               $opcoes );
 $smarty->assign ('array_include_buttom', array("url"      => "../tarifas/operadoras.php",
                                                "display"  => $LANG['include']." ".$LANG['menu_operadoras'] ) );
 display_template("rel_operadoras.tpl",   $smarty, $titulo );
 ?>
