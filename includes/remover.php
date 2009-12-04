<?php
/* ----------------------------------------------------------------------------
 * Programa: remover.php - Manutenção: Remove arquivos do Banco e demais gravações.
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Rafael Bozzetti <rafael@opens.com.br>
 * ---------------------------------------------------------------------------- */
require_once("../includes/verifica.php");
require_once("../configs/config.php");
require_once("../includes/classe_progressbar.php") ;

ver_permissao(62);

/* Recebe dados do Ajax */    
$d_ini = explode("/", $_POST['rm_dia_ini']);
$d_fim = explode("/", $_POST['rm_dia_fim']);
$data_ini = $d_ini[2] ."-". $d_ini[1] ."-". $d_ini[0];
$data_fim = $d_fim[2] ."-". $d_fim[1] ."-". $d_fim[0];
$caminho = $SETUP['ambiente']['path_voz'] ;
$sufixo = $SETUP['ambiente']['sufixo_voz'] ;    
$arquivos = 0;

/* Monta query com intervalos de dias */    
$date_clause =" ( calldate >= '$data_ini 00:00:00'";
$date_clause.=" AND calldate <= '".$data_fim." 23:59:59' )";
$sql = "select * from cdr where $date_clause ";
$sql.= " ORDER BY userfield,calldate,amaflags";

   try {
          $stmt = $db->prepare($sql);
          $stmt->execute();
          $atual = $stmt->rowCount() ;
       } 
   catch (Exception $e) 
       {
         display_error($LANG['error'].$e->getMessage(),false) ;
       }    
        /* Percorre retorno e */
        if ($atual > 0 ) {

                  while ($row = $stmt->fetch()) {
                        if ( ( $row['userfield'] != '' ) ) {
                            
                            /* Procura arquivos de gravação  */
                            $comando = 'find ../'.$caminho.' -iname \*'.$row["userfield"]."\*".$sufixo ;
                            $arq_voz = exec($comando) ;
                            /* Remove arquivos de gravação que esteja dentro do intervalo estabelecido. */
                            if($arq_voz != '') {
                                $remove = 'rm -f '.$arq_voz;
                                $arquivos++;
                                exec($remove);
                            }
                        }
                  }
        
        }    
        else
        {
            echo $LANG['removeMsg'];
        }
        
/* Query de remoção no Banco de Dados. */    
$sql_delete = "DELETE from cdr where $date_clause ";

   try {
          $stmt = $db->prepare($sql_delete);
          $stmt->execute();
          
       } 
   catch (Exception $e) 
       {
         display_error($LANG['error'].$e->getMessage(),false) ;
       } 
       
    if($arquivos > 0) {
        echo $arquivos ." ". $LANG['removeNumberMsg'] ;
    } else {
        echo $LANG['removeMsgDb'];
    }
?>