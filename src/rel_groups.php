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
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_grupos_ramais'] ;
 // SQL padrao
 $sql = "SELECT * FROM groups" ;
 // Opcoes de Filtros
 $opcoes = array( "nome" => $LANG['name']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) 
    $sql .= " WHERE ".$_POST['field_filter']." like '%".$_POST['text_filter']."%' AND name != 'all' AND name!='admin' AND name!='users'" ;
 else {
     $sql .= " WHERE name != 'all' AND name!='admin' AND name!='users'";
 }
 $sql .= " ORDER BY name" ;
 // Executa acesso ao banco de Dados
 try {
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }

 foreach ($row as $key => $group) {
    switch($group['inherit']) {
        case 'admin':
            $group['inherit'] = 'Administradores';
            break;
        case 'users':
            $group['inherit'] = 'Usu&aacute;rios';
            break;
        default:
            $group['inherit'] = $group['inherit'];
    }
    $row[$key] = $group;
}
  // Define variaveis do template          
 $smarty->assign ('DADOS',$row);
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../src/groups.php", "display"  => $LANG['include']." ".$LANG['menu_grupos_ramais']));
 // Exibe template
 display_template("rel_groups.tpl",$smarty,$titulo);
 ?>