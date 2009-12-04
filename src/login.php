<?php
/*-----------------------------------------------------------------------------
 * Programa: login.php - Login do Sistema
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 session_start() ;
 session_destroy() ;
 require_once("../configs/config.php");
 session_start() ;
 if (array_key_exists ('login', $_POST)) {        
    try {
       $sql = "SELECT id,name,secret,callerid,vinculo FROM peers " ;
       $sql.= " WHERE name = '".$_POST['user_login']."'";
       $row = $db->query($sql)->fetch();
    } catch  (Exception $e) {;
       display_error($LANG['error'].$e->getMessage(),true) ;
       $ERR= True ;
    }
    if (!isset($ERR)) {
       if ($_POST['user_login'] != $row['name'] || $_POST['user_senha'] !=    $row['secret']) {
          display_error($LANG['msg_loginerror'],true);
          exit ;
       } else {
          $_SESSION['id_user'] = $id_user = $row['id'] ;
          $_SESSION['name_user'] = $name_user = $row['name'] ;
          $_SESSION['active_user'] = $active_user = $row['callerid'] ;
          $_SESSION['vinculos_user'] = $vinculos_user = $row['vinculo'] ;
          header ("Location: ../src/sistema.php");
       }
    }
 }
 display_template("login.tpl",$smarty);
?>
