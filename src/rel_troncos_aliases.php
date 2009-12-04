<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_troncos_aliases.php - Lista Aliases de Troncos do Sistema
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php"); 
 ver_permissao(65) ;
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_trunkaliases'] ;
 // SQL padrao
 $sql = "SELECT * FROM trunks_aliases " ;
 // Opcoes de Filtrros
 $opcoes = array( "realname" => $LANG['realname'],"aliasname" => $LANG['aliasname']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) 
    $sql .= " WHERE ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 $sql .= " ORDER BY realname" ;
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
 $smarty->assign ('array_include_buttom',array("url" => "../src/troncos_aliases.php", "display"  => $LANG['include']." ".$LANG['menu_trunkaliases']));
 // Exibe template
 display_template("rel_troncos_aliases.tpl",$smarty,$titulo);
 ?>