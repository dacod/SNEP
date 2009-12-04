<?php
/* ----------------------------------------------------------------------------
 * Programa: compactar.php - Compacta arquivos de voz em arquivo .ZIP
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- */
require_once("../includes/verifica.php");
require_once("../configs/config.php");
require_once("../includes/classe_progressbar.php") ;
ver_permissao(62);
?>
<html>
   <head>
     <link rel="stylesheet" href="../css/<?=CSS_TEMPL;?>.css" type="text/css" />
   </head>
   <body>
      <div id="compactar">
      <?php

      // Dados vindos da interface
      $dia_ini= str_replace("/","-",$_GET['di']);
      $dia_fim= str_replace("/","-",$_GET['df']);

      // type == remove || null
      $type = $_GET['type'];
      
      $di_sql = explode("-",$dia_ini) ;
      $di_sql = $di_sql[2]."-".$di_sql[1]."-".$di_sql[0];
      $df_sql = explode("-",$dia_fim) ;
      $df_sql = $df_sql[2]."-".$df_sql[1]."-".$df_sql[0];

      // Verifica se existe o compactador
      $compactador = exec('which zip') ;
      if (!$compactador) {
         display_error($LANG['msg_nozip'],false) ;
         exit ;
      }

      // Define nome do arquivo compactado
      $arquivo_zip = $dia_ini."_ate_".$dia_fim.".zip" ;
      $nome_arquivo_zip="Chamadas_".$arquivo_zip;
      $arquivo_zip = $SETUP['ambiente']['path_voz_bkp'].$nome_arquivo_zip ;

      // Variaveis de ambiente
      $caminho = $SETUP['ambiente']['path_voz'] ;
      $sufixo = $SETUP['ambiente']['sufixo_voz'] ;
      
      // Clausula do SQL
      $date_clause =" ( calldate >= '$di_sql 00:00:00'";
      $date_clause.=" AND calldate <= '".$df_sql." 23:59:59' )";
      $sql = "select userfield from cdr where $date_clause ";
      $sql.= " ORDER BY userfield,calldate,amaflags";

      try
      {
         $stmt = $db->prepare($sql);
         $stmt->execute();
         $atual = $stmt->rowCount() ;
      } 
      catch (Exception $e)
      {
         display_error($LANG['error'].$e->getMessage(),false) ;
      }
      
      if($type == 'remove') {
            echo $LANG['msg_waitforcompress']."<br />" ;
      }else{
            echo $LANG['msg_waitforonlymove']. "<br />";
      }

      $file_not_remov = array();
      $ctd = 0 ;
      $ctd_ok = 0 ;
      $ctd_mv = 0;
      if ($atual > 0 ) {
         // Define que Nao havera limite no tempo(segundos) de execucao do script
         @set_time_limit(0);       
         $prb1 = new ProgressBar (380, 20);
         $prb1->left = 0;
         $prb1->top  = 10;
         $prb1->color = "#fff000" ;
         $prb1->addLabel('step','pct1');
         $prb1->setLabelPosition('pct1',170,28,40,0,'right');
         $prb1->setLabelFont('pct1',12,'','bold');
         $prb1->max  = $atual; 
         $prb1->show();    // show the ProgressBar

         // Percorre o arquivo com os registros selecionados
         $path = '';

         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // Se o campo userfield, que determina parte do nome do arquivo, for diferente de branco ...
            if ( ( $row['userfield'] != '' ) ) {

                // Se (type == remove) -> compacta e remove arquivos.
                if($type == 'remove') {

                   // Procura um arquivo de voz tendo como parte o campo userfield
                   $comando = 'find ../'.$caminho.' -iname \*'.$row["userfield"]."\*".$sufixo ;
                   $arq_voz = exec($comando) ;
                                      
                   // Verifica se o arquivo existe
                   if ( file_exists( $arq_voz ) ) {
                      // Se existir o arquivo, adicona ele ao arquivo .ZIP
                      $comando = "$compactador -g $arquivo_zip $arq_voz" ;

                      if (exec($comando) ) {
                         // Se conseguiu adicionar o arquivo de voz ao arquivo .ZIP ,
                         // Insere registro correspondente na tabela adequada
                         $registro = $row['userfield'] ;
                         try {
                            $db->beginTransaction() ;
                            $sql_atlz = "INSERT INTO cdr_compactado ";
                            $sql_atlz.= "(userfield, arquivo, data) VALUES ";
                            $sql_atlz.= "('$registro','$nome_arquivo_zip' , now())";
                            $db->exec($sql_atlz) ;
                            $db->commit();
                         } catch (Exception $e) {
                            $db->rollback();
                            display_error($LANG['error'].$e->getMessage(),false) ;
                            $comando = "$compactador -d $arquivo_zip $arq_voz" ;
                            exec($comando) ;
                            array_push($file_not_remov ,$arq_voz) ;
                            continue ;
                         }
                         // Se conseguiu atualizar tabela ,tenta apagar arquivo
                         if ( !unlink($arq_voz) ) {
                            array_push($file_not_remov ,$arq_voz) ;
                            $comando = "$compactador -d $arquivo_zip $arq_voz" ;

                            exec($comando) ;
                         } else {
                            $ctd_ok ++ ;
                         }
                      }                      
                   } // Fim de : Arquivo Existe
                }else{

                    $dir = false;
                    $comando = 'find ../'.$caminho.' -iname \*'.$row["userfield"]."\*".$sufixo ;
                    $arq_voz = exec($comando);


                    if($arq_voz != '') {
                        $directory = explode("/", $arq_voz);
                        
                        if(is_array($directory) && $directory[2] != $path) {
                            if( is_dir ('../' . $caminho . $directory[2] ) && $directory[2] != 'backup' ) {

                                $cmd = "mv ../" . $caminho . $directory[2] ." ". $SETUP['ambiente']['path_voz_bkp'] ;
                                exec($cmd);                                

                                $path = $directory[2];
                                $dir = true;
                            }
                                $cmd = "mv ../" . $caminho . $directory[2] ." ". $SETUP['ambiente']['path_voz_bkp'] ;
                                exec($cmd);                                
                        }
                    }
                    

                   //echo "<br />$comando - $arq_voz";
                   //
                   // Verifica se o arquivo existe
                   /*
                   if ( file_exists( $arq_voz ) ) {
                      // Se existir o arquivo, adicona ele ao arquivo .ZIP
                      if($dir) {
                          $comando = "mv " . $arq_voz ." ". $SETUP['ambiente']['path_voz_bkp'] .  "$directory[2] ";
                          exec($comando) ;
                      }else{
                          $comando = "mv " . $arq_voz ." ". $SETUP['ambiente']['path_voz_bkp'];
                          exec($comando) ;
                      }

                      
                      $ctd_mv++;
                   }
                   */

                }

            } // Fim de: Campo userfild != branco

            
            // Grafico --. Avanca posicao no grafico
            $ctd ++ ;
            $prb1->moveStep($ctd);
         }  // Fim do foreach
         if ( $ctd_ok == 0)
            $nome_arquivo = "" ;

         if($type == 'remove') {

             echo "
                  <br /><br />
                 &nbsp;&nbsp;<b><u>".$LANG['conclusion'].":</u></b> <br />

                 &nbsp;&nbsp;<b>".$LANG['fileresult'].":</b>".$nome_arquivo_zip ." <br />
                 &nbsp;&nbsp;<b>".$LANG['numfiles'].":</b> ". $ctd_ok ."

                 <br />

                 <div align=\"center\">
                    <input type=\"button\" class=\"button\" value='".$LANG['close'] ."'  onClick='self.close(); window.opener.location.reload();\'></input>
                    <div class=\"buttonEnding\"></div>
                 </div>
             ";
         }else{
             echo "
                  <br /><br />
                 &nbsp;&nbsp;<b><u>".$LANG['conclusion'] .":</u></b> <br />
                 <br />
                 &nbsp;&nbsp;<b> ". $ctd_mv ." </b>". $LANG['msg_onlymove']."
                 
                 <br /><br />

                 <div align=\"center\">
                    <input type=\"button\" class=\"button\" value='". $LANG['close'] ."' onClick='self.close(); window.opener.location.reload();'></input>
                    <div class=\"buttonEnding\"></div>
                 </div>
                ";
         }
        
     } else {  // Nao existem dados no criterio definido
       display_error($LANG['msg_notdata'],false) ;
       $acao = '' ;
     } ?>
     </div>
   </body>
</html>
