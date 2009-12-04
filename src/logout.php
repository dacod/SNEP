<?php
/*-----------------------------------------------------------------------------
 * Programa: logout.php - Programa para fechar o sistema
 * Copyright (c) 2007- Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/  
  session_start();
  session_unregister("active_user");
  session_unregister("id_user");
  session_unregister("name_user");
  session_destroy() ;
  header("Location: ../index.php");
  exit ;
?>