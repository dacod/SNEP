<?php
 session_start();
/*-----------------------------------------------------------------------------
 * Programa: verifica.php - Verifica se usuario autenticou
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------  */
 if (!isset($_SESSION["active_user"])) {
    header( 'Location: ../src/login.php' ) ;
    exit ;
 }                     
?>