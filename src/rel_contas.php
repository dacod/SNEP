<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_contas.php - Lista Contas Cadastradas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 ver_permissao(27) ;
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_accounts'] ;
 // SQL padrao
 $sql = "SELECT * FROM contas " ;
 // Opcoes de Filtrros
 $opcoes = array( "nome" => $LANG['desc'],
                  "tipo" => $LANG['type']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) {
    unset($text) ;
    if ($_POST['field_filter'] == "tipo")
       $text = array_search($_POST['text_filter'], $tipos_contas);
    else
       $text = $_POST['text_filter'] ;
    $sql .= " WHERE ".$_POST['field_filter']." = '".$text."'" ;
 }
 $sql .= " ORDER BY codigo" ;
 // Executa acesso ao banco de Dados
 try {
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }           
 // Define variaveis do template          
 $smarty->assign ('DADOS',$row);
 $smarty->assign ('tipos_contas',$tipos_contas) ;
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../src/contas.php", "display"  => $LANG['include']." ".$LANG['menu_accounts']));
 
 // Exibe template
 display_template("rel_contas.tpl",$smarty,$titulo);
 ?>