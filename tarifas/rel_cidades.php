<?php
/* ----------------------------------------------------------------------------
 * Programa: rel_cidades.php - Relatorio de Cidades
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *---------------------------------------------------------------------------*/
require_once("../includes/verifica.php");
require_once("../configs/config.php");
$estado = isset($_GET['uf']) && $_GET['uf']!= "" ? $_GET['uf'] : display_error($LANG['msg_nostate'],true);

global $LANG,$db,$smarty,$titulo, $acao,  $codigo;

$sql = "SELECT DISTINCT municipio FROM cnl WHERE uf='$estado' ORDER BY municipio";
try {
   $row = $db->query($sql)->fetchAll();
}
catch (PDOException $e) {
   display_error($LANG['error'].$e->getMessage(),true);
   exit ;
}

if(count($row) == 0)
   echo "<option>" . $LANG['select'] . "</option>";
else {
   for( $i = 0; $i < count($row); $i++) {
      echo "<option value=".$row[$i]['municipio'].">" . $row[$i]['municipio'] . "</option>\n";
   }
}
?>