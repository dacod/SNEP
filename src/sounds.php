<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
 ver_permissao(51);
 // Variaveis de ambiente do form
 $path_sounds = SNEP_PATH_SOUNDS ;
 $convert_gsm = $SETUP['ambiente']['convert_gsm'] ;
 // Se receber Variavel com nome da secao pela URL
 if (isset($_SESSION['secao']))  {
    $row =  $_SESSION['secao'];
    $secao = $row['secao'] ;
    $path_sounds = $row['diretorio'] ;
    $modo = $row['modo'] ;
    $app = $row['app'] ;
    $smarty->assign('SECAO',$secao) ;
    $smarty->assign('DIRECTORY',$path_sounds) ;
    $smarty->assign('MODO',$modo) ;
    $smarty->assign('APP',$app) ;
    unset ($_SESSION['secao']) ;
    $_SESSION['secao'] = array("secao"=>$secao,
                               "diretorio"=>$path_sounds,
                               "app"=>$app,
                               "modo"=>$modo) ;
 }
 $smarty->assign('ACAO',$acao) ;
 // TIPOS DE SOM
 // Remove o som do tipo MOH para nao gerar confusao
 unset($tipos_sons['MOH']) ;
 $smarty->assign('TIPOS_SONS',$tipos_sons) ;
 
 if ($acao == "cadastrar") {
    cadastrar();
 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_config']." -> ".$LANG['menu_sounds']." -> ".$LANG['change'];
    if ($secao)
       $titulo = $LANG['menu_config']." -> ".$LANG['menu_sounds']." ".$LANG['ofa']." ".$tLANG['section']." : ".$secao." -> ".$LANG['change'];
    alterar() ;
 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
 } elseif ($acao ==  "voltar") {
    voltar_backup() ;
 } elseif ($acao ==  "excluir") {
    excluir() ;
 } else {
   $titulo = $LANG['menu_config']." -> ".$LANG['menu_sounds']." -> ".$LANG['include'];
   if ($secao)
       $titulo = $LANG['menu_config']." -> ".$LANG['menu_sounds']." ".$LANG['ofa']." ".$LANG['section']." : ".$secao." -> ".$LANG['include'];
   principal() ;
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo, $LANG, $db, $secao, $path_sounds ;   
   if ($secao)
      $smarty->assign('dt_sounds',array("tipo" => "MOH"));
   else
      $smarty->assign('dt_sounds',array("tipo" => "AST"));
   $smarty->assign('ACAO',"cadastrar");
   display_template("sounds.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $arquivo, $descricao, $file, $path_sounds, $convertgsm, $tipo;

   // Variaiveis para maniputar Musicas em Espera  
   if (isset($_POST['secao']) && strlen($_POST['secao']) > 0) {
      $secao = $_POST['secao']  ;  
      $path_sounds = $_POST['diretorio'] ;
      $tipo = "MOH" ;
      $_SESSION['secao'] = array("secao"=>$_POST['secao'],
                                 "diretorio"=>$_POST['diretorio'],
                                 "app"=>$_POST['app'],
                                 "modo"=>$_POST['modo']) ;
   } else {
      $secao =  "" ;
   }
   // Verifica se Arquivo veio por <INPUT FILE> -  o que significa um novo
   // arquivo no diretorio de sons
   $UPL = $_FILES['file']['name'] != "" ? True : False ;
   if ($UPL) {
      $file = $_FILES['file'];
      $file['name']= str_replace(' ', '_',  $file['name']);
      $arquivo= str_replace(' ', '_',  $arquivo);

      if(strlen($arquivo) >= 50){
           display_error('Nome do arquivo não deve exceder 50 caracteres. DICA: Um espaço conta como um caracter.' ,true) ;
            exit;
      }
      // Verifica se ja nao tem arquivo com este nome no BD
      try {
         $sql = "SELECT arquivo FROM sounds WHERE (arquivo='$arquivo'" ;
         $sql.= " OR arquivo='".pathinfo($file['name'],PATHINFO_FILENAME).".gsm')" ;
         $sql.= " AND tipo = '$tipo' AND secao = '$secao'";
         $row = $db->query($sql)->fetch();
         if ($row) {
            display_error($LANG['msg_fileexists'],true) ;
            exit;
         }   
      } catch (PDOException $e) {
         display_error($LANG['error'].$e->getMessage(),true) ;
         exit ;
      }
      $arq_tmp = $path_sounds."/tmp/".$file["name"] ;
      $arq_dst = $path_sounds."/".$file["name"] ;
      $arq_bkp = $path_sounds."/backup/".$file["name"] ;

      // Move upload para tmp
      if (!move_uploaded_file($file["tmp_name"], $arq_tmp)){         
         display_error($LANG['msg_errmovetmp'].": ".$file['tmp_name'].' -> '.$file['name'], true) ;
         exit();
      }

      // Move Arquivo do tmp para o dir path_sounds, faz backup se ja
      // existir arquivo com mesmo nome
      $result = move_arquivo($arq_tmp, $arq_dst, $arq_bkp) ;
      if ($result != "") {
         display_error($result,true) ;
         exit ;
      }
      
      // Converte Arquivo para .gsm, se for o caso
      if ($convertgsm) {
         // Renomeia a extensao da nome do arquivo
         $arquivo = pathinfo($file['name'],PATHINFO_FILENAME).".gsm" ;
         
         $gsm_name=$path_sounds."/".$arquivo ;
         $gsm_bkp = $path_sounds."/backup/".$arquivo ;
         
         // Verfica se ja existe um arquivo com mesmo nome, cria backup deste
         if (file_exists($gsm_name)) {
            $result = backup_file($gsm_name,$gsm_bkp,$LANG['msg_errcreatebackup']);
         }
         // Converte para gsm, se arquivo for diferente de.gsm
         if (strtolower(pathinfo($file['name'],PATHINFO_EXTENSION)) != 'gsm' ) {
            $comando = "umask 000; /usr/bin/sox \"$arq_dst\" -r 8000 -c 1  ".$gsm_name ;
            $result = exec("$comando 2>&1",$out,$err) ;
            if ($err) {
               display_error($LANG['msg_errconvertgsm'].$gsm_name,true);
               exit ;
            }
         }
         // Elimina arquivo que deu origem ao GSM
         $comando = "rm -f $arq_dst" ;
         $result = exec("$comando 2>&1",$out,$err) ;
         if ($err) {
            display_error($LANG['msg_errdelfile']."(".$arq_dst.")",true);
            exit ;
         }         
      }
   } // Fim do IF se arquivo veio por <INPUT FILE>
   
   // Insere dados na Tabela sounds
   try {
      $sql  = "INSERT INTO sounds(arquivo,descricao,data, tipo, secao)" ;
      $sql .= " VALUES ('$arquivo','$descricao', now(),'$tipo', '$secao')" ;
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      echo "<meta http-equiv='refresh' content='0;url=../src/sounds.php'>" ;
   } catch (Exception $e) {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),true) ;
      exit ;
   }

 }
/*------------------------------------------------------------------------------
  Funcao move_arquivo -  Move arquivo de um dir p/ outro
------------------------------------------------------------------------------*/
function move_arquivo($ori, $dst , $bkp="") {
   global $LANG ;
   $erros = "" ;
   // Verfica se ja existe um arquivo com mesmo nome, cria backup deste
   if ($bkp != "") {
      $erros = backup_file($dst,$bkp,$LANG['msg_errcreatebackup']) ;
   }
   // Move Arquivo de Origem para Destino
   $comando = "mv $ori $dst" ;
   $result = exec("$comando 2>&1",$out,$err) ;
   if ($err) {
      $erros .= $LANG['msg_errmovefile']."$ori -> $dst\n" ;
      if ($bkp != "") {
         // Se der problema, Tenta Restaurar Backup
         $comando = "mv $bkp $dst" ;
         $result = exec("$comando 2>&1",$out,$err) ;
         if ($err) {
            $erros .= $LANG['msg_errrestorebackup'].$bkp."\n" ;
         }
      }
   }
   return $erros ;
}

/*------------------------------------------------------------------------------
  Funcao backup_file -  Cria Backup dop Arquivo
------------------------------------------------------------------------------*/
function backup_file($dst, $bkp, $msg_err ) {
   global $LANG ;
   $erros  = "" ;
   if (file_exists($dst)) {
      $comando = "mv $dst $bkp" ;
      $result = exec("$comando 2>&1",$out,$err) ;
      if ($err) {
         $erros .= $msg_err.$bkp."\n" ;
      }
   }
   return $erros ;
}


/*------------------------------------------------------------------------------
  Funcao voltar_backup -  Restaura um arquivo de Backup
------------------------------------------------------------------------------*/
function voltar_backup() {
   global $LANG, $db, $atual, $backup, $path_sounds;
   if ($backup === "") {
      display_error($LANG['msg_nobackups'],true) ;
      exit;
   }
   //echo "Atual=$atual<br>Backup=$backup<br>Path=$path_sounds";exit;
   if ($atual == "") {
      $atual = $path_sounds."/".pathinfo($backup,PATHINFO_FILENAME);
   }
   $result = move_arquivo($backup, $atual) ;
   if ($result != "") {
      display_error($result,true) ;
      exit ;
   }
   if ($convert_gsm) {
      // Se Backup nao for GSM, cria nome para .GSM
      if (strtolower(pathinfo($backup,PATHINFO_EXTENSION)) != "gsm") {
         $gsm_name = pathinfo($backup,PATHINFO_DIRNAME)."/".
                     pathinfo($backup,PATHINFO_FILENAME).".gsm" ;
         // Converte para gsm, se arquivo for diferente de.gsm
         $comando = "umask 000; /usr/bin/sox \"$atual\" -r 8000 -c 1  ".$gsm_name ;
         $result = exec("$comando 2>&1",$out,$err) ;
         if ($err) {
            display_error($LANG['msg_errconvertgsm'].$gsm_name,true);
            exit ;
         }
         // Elimina arquivo que deu origem ao GSM
         $comando = "rm -f $atual" ;
         $result = exec("$comando 2>&1",$out,$err) ;
         if ($err) {
            display_error($LANG['msg_errdelfile']."(".$arq_dst.")",true);
            exit ;
         }         
      }
   }
   echo "<script>history.go(-1);</script>" ;
   //echo "<meta http-equiv='refresh' content='0;url=../src/rel_sounds.php'>\n" ;     
}

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG, $SETUP, $db, $smarty, $titulo, $acao, $tipo ;
   $arquivo = isset($_POST['arquivo']) ? $_POST['arquivo']: $_GET['arquivo'];
   if (!$arquivo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }

   if (isset($_SESSION['secao'])) {
      $row = $_SESSION['secao']  ;
      $secao = $row['secao'];
      $dir_sounds = $row['diretorio'] ;
      $modo = $row['modo'] ;
      $tipo = 'MOH';
   } else {
      $secao =  "" ;
      $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : $_GET['tipo'];
      $dir_sounds = SNEP_PATH_SOUNDS ;
   }
   try {
      $sql = "SELECT arquivo,descricao,tipo,date_format(data,'%d/%m/%Y %h:%i:%s') as data FROM sounds WHERE arquivo='$arquivo' AND tipo='$tipo' AND secao='$secao'" ;
      $row = $db->query($sql)->fetch();
   } catch (PDOException $e) {
      display_error($LANG['error'].$e->getMessage(),true) ;
   }
   $dir_sounds .= "/" ;
   // Varre diretorio de Sons e Backup para relacionar arquivo a ser ouvido
   $tmp = array("atual"=>False,"backup"=>False,
                "arq_atual"=>"N.A.","arq_backup"=>"N.A.") ;
   if (file_exists($dir_sounds.$arquivo)) {
      $tmp['atual'] = True ;
      $tmp['arq_atual'] = $dir_sounds.$arquivo ;
   }
   if (file_exists($dir_sounds."backup/".$arquivo)) {
      $tmp['backup'] = True ;
      $tmp['arq_backup'] = $dir_sounds."backup/".$arquivo ;
   }
   $row += $tmp;
   $smarty->assign('ACAO',"grava_alterar") ;
   $smarty->assign('dt_sounds',$row);
   display_template("sounds.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
  OBs: TYem DEBUG na execao desta rotina logo abaixo - Verifique se for preciso     
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $arquivo, $descricao, $file, $convertgsm, $tipo, $backup, $atual, $path_sounds, $nome_original;
   if (isset($_POST['secao']) && strlen($_POST['secao']) > 0) {
      $secao = $_POST['secao']  ;  
      $path_sounds = $_POST['diretorio'] ;
      $tipo = "MOH" ;
      $_SESSION['secao'] = array("secao"=>$_POST['secao'],
                                 "diretorio"=>$_POST['diretorio'],
                                 "app"=>$_POST['app'],
                                 "modo"=>$_POST['modo']) ;
   } else {
      $secao =  "" ;
   }
   $UPL = $_FILES['file']['name'] != "" ? True : False ;
   if ($UPL) {
      $file = $_FILES['file'];
      // Nome do Arquivo que sera substituido nao pode mudar
      // Verifica se extensao do arquivo a ser substituidoe a mesma que o atual
      // Renomeia arq_dst se for necessario
      $ext_atual = strtolower(pathinfo($atual,PATHINFO_EXTENSION)) ;
      $ext_novo  = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
      $arquivo = $nome_original ;
      $arq_bkp = $path_sounds."/backup/".$nome_original ;      


      // Se as ext forem iguais , mantem o nome do arquivo atual
      if ($ext_atual === $ext_novo) {
         $arq_dst = $path_sounds."/".$nome_original ;
         $arq_tmp = $path_sounds."/tmp/".$nome_original ;
      } else {  // Se as ext forem diferentes ...
         // Se ext atual for = gsm, vai converter mais abaixo
         if ($ext_atual === "gsm") {
            $arquivo = strtolower(pathinfo($atual,PATHINFO_FILENAME)).".$ext_novo" ;
            $arq_dst = $path_sounds."/".$arquivo ;
            $arq_tmp = $path_sounds."/tmp/".$arquivo ;
         } else { // Senao, mantem o nome do arquivo, trocando para nova extUensao
            $arquivo = strtolower(pathinfo($atual,PATHINFO_FILENAME)).".$ext_novo" ;
            $arq_dst = $path_sounds."/".$arquivo ;
            $arq_tmp = $path_sounds."/tmp/".$arquivo ; 
         } 
      }
      // Move arquivo upload para diretorio tmp
      if (!move_uploaded_file($file["tmp_name"], $arq_tmp)){
         display_error($LANG['msg_errmovetmp'].": ".$file['tmp_name'].' -> '.$arq_tmp, true) ;
         exit();
      }
      // Move Arquivo do tmp para o dir path_sounds, faz backup se ja
      // existir arquivo com mesmo nome
      $result = move_arquivo($arq_tmp, $arq_dst, $arq_bkp) ;
      if ($result != "") {
         display_error($result,true) ;
         exit ;
      }
         
      // Converte Arquivo para .gsm, se for o caso
      if ($convertgsm && $ext_novo != "gsm") {
         // Renomeia a extensao da nome do arquivo
         $arquivo = pathinfo($atual,PATHINFO_FILENAME).".gsm" ;
         
         // Renomeia a extensao da nome do arquivo
         $gsm_name=$path_sounds."/".$arquivo ;
         $gsm_bkp = $path_sounds."/backup/".$arquivo ;
            
         // Verfica se ja existe um arquivo com mesmo nome, cria backup deste
         if (file_exists($gsm_name)) {
            $result = backup_file($gsm_name,$gsm_bkp,$LANG['msg_errcreatebackup']);
         }
         // Converte para gsm, se arquivo for diferente de.gsm
         $comando = "umask 000; /usr/bin/sox \"$arq_dst\" -r 8000 -c 1  ".$gsm_name ;
         $result = exec("$comando 2>&1",$out,$err) ;
         if ($err) {
            display_error($LANG['msg_errconvertgsm'].$gsm_name,true);
            exit ;
         }
         // Elimina arquivo que deu origem ao GSM
         $comando = "rm -f $arq_dst" ;
         $result = exec("$comando 2>&1",$out,$err) ;
         if ($err) {
            display_error($LANG['msg_errdelfile']."(".$arq_dst.")",true);
            exit ;
         }         
      }
   }
   $sql = "update sounds set arquivo='$arquivo', tipo='$tipo',";
   $sql .= " descricao='$descricao', data = now()  WHERE arquivo='$nome_original'" ;
   $sql .= " AND tipo='$tipo' AND secao='$secao'";

   $DEBUG = False;
   if ($DEBUG) {
      echo $sql ;
      echo "<br>ATUAL = $atual" ;
      echo "<br>BACKUP = $backup" ;
      echo "<br>EXT ATUAL = $ext_atual" ;
      echo "<br>EXT NOVO = $ext_novo" ;
      echo "<br>ARQUIVO = $arquivo" ;
      echo "<br>ARQ BKP = $arq_bkp" ;
      echo "<br>ARQ DST = $arq_dst" ;
      echo "<br>ARQ TMP = $arq_tmp" ;   
      echo "<br>".$sql ;      
   }
   // Insere dados na Tabela sounds <meta http-equiv='refresh' content='0;url=../gestao/musiconhold.php
   try {
     $db->beginTransaction() ;
     $db->exec($sql) ;
     $db->commit();
     if (!$DEBUG)
        if ($secao === "")
           echo "<meta http-equiv='refresh' content='0;url=../src/rel_musiconhold.php'>\n" ;
        else
           echo "<meta http-equiv='refresh' content='0;url=../gestao/rel_musiconhold.php'>\n";
   } catch (Exception $e) {
     $db->rollBack();
     display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $db, $tipo, $secao, $atual;
   $arquivo = isset($_POST['arquivo']) ? $_POST['arquivo']: $_GET['arquivo'];
   if (!$arquivo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   if (isset($_SESSION['secao'])) {
      $row = $_SESSION['secao']  ;
      $secao = $row['secao'];
      $tipo = 'MOH';
      $dir = $row['diretorio'] ;
   }

   // Se for Arquivo de MOH, apaga tudo que tiver o mesmo nome,
   // idepenente da extensao      
      $comando = "rm -f $dir/".pathinfo($atual,PATHINFO_FILENAME).".* " ;
      $comando.= " $dir/backup/".pathinfo($atual,PATHINFO_FILENAME).".* " ;
      $result = exec("$comando 2>&1",$out,$err) ;
      if ($err && $err!= 1) {
         display_error($LANG['msg_errdelbackup'].$arq_atual.$err,true) ;
         exit ;
      }
    else {
      $secao =  "" ;
      $tipo = isset( $_POST['tipo'])? $_POST['tipo'] : '';
      // Apaga Arquivo de Backup, se Existir
      $arq_bkp = $_POST['backup'];
      if (file_exists($arq_bkp)) {
         $comando = "rm -f $arq_bkp" ;
         $result = exec("$comando 2>&1",$out,$err) ;
         if ($err) {
            display_error($LANG['msg_errdelbackup'].$arq_bkp,true) ;
            exit ;
         }      
      }
   }
    if (isset($_SESSION['secao'])) {
      $row = $_SESSION['secao']  ;
      $secao = $row['secao'];
      $tipo = 'MOH';
      $dir = $row['diretorio'] ;

       try {
          $sql = "DELETE FROM sounds WHERE arquivo='".$arquivo."'";
          $sql.= " AND tipo='$tipo' AND secao='$secao'" ;
          $db->beginTransaction() ;
          $db->exec($sql) ;
          $db->commit();
          display_error($LANG['msg_excluded'],true) ;
          if ($secao == "")
             echo "<meta http-equiv='refresh' content='0;url=../src/rel_musiconhold.php'>\n" ;
          else
             echo "<meta http-equiv='refresh' content='0;url=../gestao/rel_musiconhold.php'>\n";
       } catch (PDOException $e) {
          display_error($LANG['error'].$e->getMessage(),true) ;
       }
   }
}
