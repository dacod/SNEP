<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_ccustos.php - Lista de Centros de Custos Cadastrados
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");

 ver_permissao(27) ;
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_ccustos'] ;
 // SQL padrao
  $sql = "SELECT * FROM ccustos " ;
 // Opcoes de Filtrros
 $opcoes = array( "nome" => $LANG['name'],
                  "tipo" => $LANG['type']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) {
    unset($text) ;
    if ($_POST['field_filter'] == "tipo")
       $text = array_search($_POST['text_filter'], $tipos_ccustos);
    else
       $text = $_POST['text_filter'] ;
    $sql .= " WHERE ".$_POST['field_filter']." like '%".$text."%'" ;
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
 $smarty->assign('TIPOS_CCUSTOS',$tipos_ccustos) ;
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('PROTOTYPE', True);
 $smarty->assign ('array_include_buttom',array("url" => "../src/ccustos.php", 
"display"  => $LANG['include']." ".$LANG['menu_ccustos']));
 
 // Exibe template
 display_template("rel_ccustos.tpl",$smarty,$titulo);
 ?>
