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
 // Lista de Arquivos que estao cadastrados no Sistema
 try {
    $sql = "SELECT arquivo from sounds" ;
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }           
 // Lista de Arquivos que estao no Disco 
 $files = scandir(SNEP_PATH_SOUNDS);
 foreach($files as $i => $value) {
   if (substr($value, 0, 1) == '.') {
      unset($files[$i]);
   }
   if (is_dir(SNEP_PATH_SOUNDS.$value)){
      unset($files[$i]);
   }
 }
 // Retira da Lista os arquivos que ja estao no Cadastro do SNEP
 foreach ($row as $key => $value) {
   if ($i = array_search($value['arquivo'],$files)) {
      unset($files[$i]);
   }
 }
 $smarty->assign('dt_files',$files);
 if(!isset($titulo))
  $titulo = "";
 display_template("lista_astsounds.tpl",$smarty,$titulo) ;
?>
