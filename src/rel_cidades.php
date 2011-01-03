<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once("../includes/verifica.php");
require_once("../configs/config.php");
$estado = isset($_GET['uf']) && $_GET['uf']!= "" ? $_GET['uf'] : display_error($LANG['msg_nostate'],true);

global $LANG,$db,$smarty,$titulo, $acao,  $codigo;

$sql = "SELECT ars_cidade.name as mucicipio FROM ars_ddd INNER JOIN ars_cidade ON ars_ddd.cidade=ars_cidade.id where ars_ddd.estado = $estado'";

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
      echo "<option>" . $row[$i]['municipio'] . "</option>\n";
   }
}
?>