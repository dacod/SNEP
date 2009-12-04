<?php
/* ----------------------------------------------------------------------------
 * Programa: pesq_canal.php - Pesquisa se canal ja foi usado em algum ramal
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php");
 //ver_permissao(12) ;
 // Recebe parametro enviado por requisicao AJAX
 $canal = $_GET['c'] ;   
 $type  = $_GET['t'] ;
 $sql = "SELECT name FROM peers WHERE peer_type = '$type' AND canal like'%".$canal."%'";
 $row = $db->query($sql)->fetch() ;
 // Devolve resultado do SQL em forma de echo, que sera trata pelo objeto 
 // Instanciado AJAX
 echo $row['name'] ;
?> 