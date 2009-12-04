<?php
/* ----------------------------------------------------------------------------
 * Programa: pesq_cidades.php - Pesquisa cidades de um estado
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");
 require_once("../configs/config.php");
 $uf = $_GET['uf'] ;
 try {
    $sql_cid = "SELECT * FROM cnl WHERE uf = '$uf'";
    $sql_cid .= " GROUP by municipio,uf ORDER by municipio";
    $row_cid = $db->query($sql_cid)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }
 unset($val);
 $cidades = array(""=>$LANG['undef']);
 foreach ($row_cid as $val)
    $cidades[$val['municipio']] = $val['municipio'] ;   
 asort($cidades) ;
 $smarty->assign('CIDADES',$cidades);
?>