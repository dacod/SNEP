<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_operadoras.php - Lista Operadoras Cadastradas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
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