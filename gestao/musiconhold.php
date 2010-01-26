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
 ver_permissao(53) ;
 // Variaveis de ambiente 
 if (isset($_GET['acao']) && $_GET['acao'] == 'listar') {
    $acao = "listar" ;
    $row=$_SESSION['secao'] ;
    $name = $row['secao'];
    $directory = $row['diretorio'];
    $mode = $row['modo'] ;
    $application = $row['application'] ;
 }

 $config = Zend_Registry::get('config');

 $smarty->assign("MOH_DIRECTORY", $config->system->path->asterisk->moh);

 $smarty->assign('ACAO',$acao) ;
 $smarty->assign ('MUSIC_MODES',$musiconhold_modes);
 if ($acao == "cadastrar") {
    cadastrar();
 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_config']." -> ".$LANG['menu_musiconhold']." -> ".$LANG['change']." ".$LANG['sections'];
    alterar() ;
 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;    
 } elseif ($acao ==  "listar") {
    listar_musicas() ;
 } elseif ($acao ==  "excluir") {
    excluir() ;
 } else {
   $titulo = $LANG['menu_config']." -> ".$LANG['menu_musiconhold']." -> ".$LANG['include']." ".$LANG['sections'];
   principal() ;
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo ;
   $smarty->assign('ACAO',"cadastrar");
   display_template("musiconhold.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG,$name, $desc, $directory, $application, $mode;
    
   if (!configura_musiconhold("I",trim($name),$desc,$mode,$directory,$application)) {
      display_error($LANG['msg_errmusiconhold'],true) ;
      exit ;
   }
   $directory = SNEP_PATH_MOH.$directory ;
   // Cria diretorio se nao existir
   $comando = "mkdir -p $directory $directory/tmp $directory/backup" ;
   $result = exec("$comando 2>&1",$out,$err) ;
   if ($err) {
      display_error($LANG['msg_err_mkdir'].$gsm_name,true);
      configura_musiconhold("E",trim($name)) ;
      exit ;
   }
   
   echo "<meta http-equiv='refresh' content='0;url=../gestao/rel_musiconhold.php'>\n";
}

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG,$smarty,$name, $acao ;
   $name = isset($_POST['name']) ? $_POST['name'] : $_GET['name'];
   if (!$name) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }

   $config = Zend_Registry::get('config');
   $asterisk_dir = $config->system->path->asterisk->conf;

   $filename = "$asterisk_dir/snep/snep-musiconhold.conf" ;
   $linhas = file($filename,FILE_IGNORE_NEW_LINES) ;
   // Varre o array do  arquivo e verifica se existe entradas para a secao
   $flag = False ;
   $secao = array() ;
   foreach($linhas as $key => $value) {
      $value= rtrim($value) ;
      if (!$flag && strpos($value,"SNEP(".$name.")") > 0) {
         $flag = True ;
         $secao['name'] = $name ;
         $secao['desc'] = substr($value,strpos($value,"=")+1) ;
         continue ;
      }         
      // Se encontrou ocorrencia da secao , vai definindo variaveis
      if ($flag ) {
         // Se encontrou nome da secao [secao], ignora esta linha
         if (substr($value,0,1)=="[")
            continue;
         // Se encontrou o inicio de outra secao, termina a leitura
         if (strpos($value,"SNEP(") > 0) 
            break ;
         $ind=substr($value,0,strpos($value,"=")) ;
         if (strlen(trim($ind)) > 0)
            if (trim($ind) === "directory") {
               $secao[$ind] = substr($value,strrpos($value,"moh")+4);
            } else 
               $secao[$ind] = substr($value,strpos($value,"=")+1) ;
      }
   }
   $smarty->assign('ACAO',"grava_alterar") ;
   $smarty->assign ('dt_secoes',$secao);
   display_template("musiconhold.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $name, $desc, $directory, $application, $mode;
    
   if (!configura_musiconhold("A",trim($name),$desc,$mode,$directory,$application)) {
      display_error($LANG['msg_errmusiconhold'],true) ;
      exit ;
   }
   echo "<meta http-equiv='refresh' content='0;url=../gestao/rel_musiconhold.php'>\n";
  
 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $name;
   if (!configura_musiconhold("E",trim($name))) {
      display_error($LANG['msg_errmusiconhold'],true) ;
      exit ;
   }
   //$directory = SNEP_PATH_MOH.$directory ;
   // Exclui o diretorio 
   $comando = "rm -rf $directory" ;
   $result = exec("$comando 2>&1",$out,$err) ;
   if ($err) {
      display_error($LANG['msg_errdeldir']."($directory)",true);
      exit ;
   }
   
   echo "<meta http-equiv='refresh' content='0;url=../gestao/rel_musiconhold.php'>\n";
}
/*-----------------------------------------------------------------------------
 * Funcao configura_musiconhold - Ajusta o arquivo snep-musiconhold.conf
 * Recebe : action   - acao (I, A, E)
 *          name     - nome da secao
 *          desc     - descricao da secao
 *          mode     - modo de leitura do asterisk
 *          dir      - Diretorio onde estao os arquivos
 *          app     - aplicacao a ser usada se mode=custom
 * Obs: - Esta funcao SEMPRE manipula intervalos de linhas no arquivo , A
 *        PARTIR de Comentario padrao: ;SNEP(secao): ...
 * ----------------------------------------------------------------------------*/
function configura_musiconhold($action,$name,$desc="",$mode="",$dir="",$app="") {
   $config = Zend_Registry::get('config');
   $asterisk_dir = $config->system->path->asterisk->conf;

   $filename = "$asterisk_dir/snep/snep-musiconhold.conf" ;
   // Se acao = Incluir ou Alterar, obriga a ter todos os parametros
   if ($action != "E" && ($name=="" || $desc=="" || $mode=="")) {
      return False ;
   }
   // Monta nova Entrada para a secao no Arquivo
   $novo_conteudo  = "\n;SNEP($name) = $desc\n" ;
   $novo_conteudo .= "[$name]\n" ;
   $novo_conteudo .= "mode=$mode\n" ;
   $novo_conteudo .= "directory=".SNEP_PATH_MOH."$dir\n" ;
   if (strlen($app) > 0) {
      $novo_conteudo .= "application=$app\n" ;
   }
   // Verifica se arquivo tem permissao de ESCRITA
   if (!is_writable($filename)) 
      return False ;
   // Le o arquivo para um array
   $linhas = file($filename) ;      
   // Varre o array do  arquivo e verifica se existe entradas para a secao
   //  em questao e, Se houver, remove estas entradas
   $flag = True ; 
   if ($action != "I") {
      foreach($linhas as $key => $value) {
          if (strpos($value,"SNEP(".$name.")") > 0) {
             $flag = False ;     
             unset($linhas[$key]) ;
             continue ;
         }         
         // Se encontrou ocorrencia do ramal ...
         if (!$flag ) {
            // Vai apagando ate o proximo cabecalho
            if (strpos($value,"SNEP") > 0) {
               $flag = True ;
               continue ;
            } else 
               unset($linhas[$key]) ;
         }
      }
   }
   // Coloca o array numa String, abre o arquivo e grava mudancas
   $conteudo = implode('',$linhas) ;
   if ($action != "E")
      $conteudo .= $novo_conteudo ;
   $handle = @fopen($filename, "w+");
   if (!$handle) 
      return False ;   
   if (fwrite($handle, $conteudo) === FALSE) 
      return False ;
   fclose($handle) ;
   return True ;
}


/*-----------------------------------------------------------------------------
 * Funcao listar_musicas -  Lista de Sons de Musicas em Espera
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/ 
function listar_musicas() {
 global $db, $smarty, $LANG, $SETUP, $directory, $name, $mode, $application ;
 unset($_SESSION['secao']) ;
 $secao = $name ;
 $config = Zend_Registry::get('config');
 $moh_dir = $config->system->path->asterisk->moh;
 $dir_sounds = str_replace($moh_dir,SNEP_PATH_MOH,$directory) ;
 if (trim($secao) == "" || trim($dir_sounds) == "") {
    display_error($LANG['msg_errsectionmusiconhold'],true) ;
    exit ;
 }
 $titulo = $LANG['menu_config']." -> ".$LANG['menu_sounds']." ".$LANG['ofa']." ".$LANG['section']." : ".$secao ;
 // SQL padrao
 $sql = "SELECT arquivo,descricao,tipo,date_format(data,'%d/%m/%Y %h:%i:%s') as data FROM sounds  where tipo = 'MOH' and secao='$secao'" ;
 // Opcoes de Filtrros
 $opcoes = array( "arquivo" => $LANG['name'],
                  "descricao" => $LANG['desc']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) 
    $sql .= " AND ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 $sql .= " ORDER BY arquivo" ;
 // Executa acesso ao banco de Dados
 try {
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }
 // Varre diretorio de Sons e Backup para relacionar arquivo a ser ouvido
 $dir_sounds = $dir_sounds."/" ;
 foreach ($row as $key=>$val) {
    $tmp = array("atual"=>False,"backup"=>False,
                 "arq_atual"=>"","arq_backup"=>"") ;
    if (file_exists($dir_sounds.$val['arquivo'])) {
       $tmp['atual'] = True ;
       $tmp['arq_atual'] = $dir_sounds.$val['arquivo'] ;
    }
    if (file_exists($dir_sounds."backup/".$val['arquivo'])) {
       $tmp['backup'] = True ;
       $tmp['arq_backup'] = $dir_sounds."backup/".$val['arquivo'] ;
    }
    $row[$key] += $tmp ;
 }

 $tot_pages = ceil(count($row)/$SETUP['ambiente']['linelimit']) ;
 for ($i = 1 ; $i <= $tot_pages ; $i ++ )
     $paginas[$i] = $i;
 // Cria uma SECAO para a secao
 $_SESSION['secao'] = array("secao"=>$secao,"diretorio"=>$directory,
                            "app"=>$application,"modo"=>$mode) ;
 
 // Define variaveis do template     
 $smarty->assign ('DADOS',$row);
 $smarty->assign ('TOT',$tot_pages);
 $smarty->assign ('PAGINAS',$paginas) ;
 $smarty->assign ('INI',1);
 $smarty->assign ('SECAO',$secao) ;
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../src/sounds.php?secao=$secao&diretorio=$dir_sounds&modo=$mode&app=$application", "display"  => $LANG['register']." ".$LANG['menu_sounds']));
 // Exibe template
 display_template("rel_sounds_musiconhold.tpl",$smarty,$titulo);
}
