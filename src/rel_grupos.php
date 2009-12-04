<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_grupos.php - Lista grupos do Sistema
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php"); 
 ver_permissao(11) ;
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_grupos'] ;
 // SQL padrao
 $sql = "SELECT * FROM grupos " ;
 // Opcoes de Filtrros
 $opcoes = array( "nome" => $LANG['name']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) 
    $sql .= " WHERE ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 $sql .= " ORDER BY cod_grupo" ;
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
 $smarty->assign ('PROTOTYPE', True) ;
 $smarty->assign ('array_include_buttom',array("url" => "../src/grupos.php", "display"  => $LANG['include']." ".$LANG['menu_grupos']));
 // Exibe template
 display_template("rel_grupos.tpl",$smarty,$titulo);
 ?>