<?php
/*-----------------------------------------------------------------------------
 * Programa: ver_auth.php - Verifica o numero do cadeado, script chamado via Ajax pelo cadeado.js
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Rafael Bozzetti <rafael@opens.com.br>
 * ----------------------------------------------------------------------------*/
 require_once("../configs/config.php");
 require_once("conecta.php"); 
 $auth = md5($_POST['authenticate']);
 $name = $_POST['user'];
 
  if(!empty($auth))
  {
      try 
      {
           $sql = "SELECT name, authenticate FROM peers WHERE authenticate = '$auth' AND name != '$name' " ;
           $resultado = $db->query($sql)->fetch();
           
           if ($resultado > 0) {
                echo $LANG['error'].$LANG['senha_cadeado'];
           }else{
               echo '0';
           }
      } catch (Exception $e)
      {
           display_error($LANG['error'].$e->getMessage(),true) ;
           exit ;
      }
  }
?>
