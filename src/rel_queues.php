<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_queues.php - Lista Filas Cadastradas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 ver_permissao(18) ;
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_queues'] ;
 // SQL padrao
 $sql = "SELECT name,strategy,maxlen,musiconhold FROM queues " ;
 // Opcoes de Filtros
 $opcoes = array( "name" => $LANG['name']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) {
    $sql .= " WHERE ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 }
 $sql .= " ORDER BY name" ;
 // Executa acesso ao banco de Dados
 try {
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }           
 
 $tipos_strategy = array("ringall" => $LANG['ringall'],
                         "roundrobin" => $LANG['roundrobin'],
                         "leastrecent" => $LANG['leastrecent'], 
                         "random" => $LANG['random'],
                         "fewestcalls" => $LANG['fewestcalls'], 
                         "rrmemory" => $LANG['rrmemory'] );
                         
 $tipos_joinempty = array("yes" => $LANG['yes'], 
                          "no" => $LANG['no'], 
                          "strict" => $LANG['strict']) ;
 // Define variaveis do template          
 $smarty->assign ('DADOS',$row);
 $smarty->assign ('OPCOES_JOINEMPTY',$tipos_joinempty);
 $smarty->assign ('OPCOES_STRATEGY',$tipos_strategy);
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('PROTOTYPE', True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../src/queues.php", "display"  => $LANG['include']." ".$LANG['menu_queues']));
 
 // Exibe template
 display_template("rel_queues.tpl",$smarty,$titulo);
 ?>