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
 //ver_permissao(12) ;
 // Recebe parametro enviado por requisicao AJAX
 $ramal_ini = $_GET['ci'] ;   // ramal inicial
 $ramal_fim = $_GET['cf'] ;   // ramal final
 $type  = $_GET['t'] ;   // tecnologias slecionadas, separadas por ;
 echo "Ver";
 /*
 $type = explode(";",$type) ; 
 foreach ($type as $valor) {
    $range = $SETUP['canais'][$valor."_channels"] ;
    $range = str_replace("'","",$range);
    $range = explode(";",$range) ;
    if (count($range) > 0) {
       foreach ($range as $val_range) {
          $range_ini = substr($val_range,0,strpos($val_range,"-")) ;
          $range_fim = substr($val_range,strpos($val_range,"-")+1) ;
          // Testa ramal inicial
          if ($ramal_ini < $range_ini || $ramal_ini > $range_fim)
             echo "$ramal_ini/".strtoupper($valor);
          elseif ($ramal_fim < $range_ini || $ramal_fim > $range_fim)
             echo "$ramal_fim/".strtoupper($valor);
       }
    }      
 }*/
?>