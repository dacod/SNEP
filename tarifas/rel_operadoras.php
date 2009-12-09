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
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_operadoras'] ;
 // SQL padrao
 $sql = "SELECT * FROM operadoras " ;
 // Opcoes de Filtrros
 $opcoes = array( "nome" => $LANG['name'], "codigo" => $LANG['id']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) 
    $sql .= " WHERE ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 $sql .= " ORDER BY codigo" ;
 // Executa acesso ao banco de Dados
 try {
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }           
 // Define variaveis do template          
 $smarty->assign ('DADOS',$row);
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../tarifas/operadoras.php", "display"  => $LANG['include']." ".$LANG['menu_operadoras']));
 
 // Exibe template
 display_template("rel_operadoras.tpl",$smarty,$titulo);
 ?>