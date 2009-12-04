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