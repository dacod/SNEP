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
 ver_permissao(32) ;
 $conf_app = $SETUP['ambiente']['conference_app'] ;
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_conference'] ;
  if (array_key_exists ('conference', $_POST)) {
    gravar() ;
 } 

 // Monta array de sala,senha da sala
 for ($i=901 ; $i <= 915; $i++) {
    $salas[$i]["id"] = $i ;
    $salas[$i]["usa_auth"] = False ;
    $salas[$i]["authenticate"] = "" ;
    $salas[$i]["status"] = "N" ;
    $salas[$i]["ccustos"] = "" ;
    
 }
 // Faz Leitura das Senhas da Sala de Conferencia
 unset($senhas);
 if($senhas = executacmd('cat /etc/asterisk/snep/snep-authconferences.conf | grep "^[9][0-1][0-9]"',"",True)) {
    // Monta array de sala,senha da sala
    foreach($senhas as $value){
       $lin = explode(":",$value);
       $salas[$lin[0]]["authenticate"] = $lin[1] ;
       $salas[$lin[0]]["usa_auth"] = True ;
    }
 }
 // Faz Leitura das Salas de Conferencias
 $row = executacmd("cat /etc/asterisk/snep/snep-conferences.conf | grep 'exten => [9][0-1][0-9]' | cut -d '>' -f2","",True) ;
 $sala = "" ;
 $ccustos = "" ;
 foreach($row as $key => $value){
    $room = explode(",",$value);
    if ($sala != trim($room[0])) {
       $sala = trim($room[0]) ;
       $salas[$sala]["status"] = "S";
       $salas[$sala]["ccustos"] = "";
    }
       // Se tem CCustos Definido
       if (strpos($room[2],"accountcode") > 0) {
          $ccustos = trim(substr($room[2],strpos($room[2],"=")+1)) ;
          $ccustos = substr($ccustos,0,strlen($ccustos)-1) ;
          $salas[$sala]["ccustos"] = $ccustos;
       }

 }
 // Lista de Centros de Custos
 try {
    $sql = "select ccustos.* from ccustos ORDER BY ccustos.codigo" ;
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
    exit ;
 }
 unset($val);
 $ccustos = array(""=>"");
 if (count($row) > 0) {
    foreach ($row as $val)
       $ccustos[$val['codigo']] = $val['tipo']." : ".$val['codigo']." - ".$val['nome'] ;
    ksort($ccustos);
 }

 $_SESSION['salas'] = $salas ;
 // Define variaveis do template
 $smarty->assign ('DADOS',$salas);
 $smarty->assign ('CCUSTOS',$ccustos);
 $smarty->assign ('STATUS',array("S"=>$LANG['disable'],"N"=>$LANG['enable']));
 // Exibe template
 display_template("conferencias.tpl",$smarty,$titulo);
 
/*-----------------------------------------------------------------------------
 * Funcao gravar - Converte e grava o arquivo
 * ----------------------------------------------------------------------------*/
function gravar()  {
   global $db, $LANG, $salas, $conf_app, $ccustos ;
   $salas_orig = $_SESSION['salas'] ;
   $file_conf = "/etc/asterisk/snep/snep-conferences.conf" ;
   $file_auth = "/etc/asterisk/snep/snep-authconferences.conf" ;
    // Verifica se arquivo tem permissao de ESCRITA
   if (!is_writable($file_conf) || !is_writable($file_auth)) {
      display_error($LANG['msg_conferencenowrite'],true) ;
      return False ;
   }
   // Le o arquivo para um array
   $linhas_conf = file($file_conf) ;
   $linhas_auth = file($file_auth) ;

   // Ajusta arrays obtidos a partir do formulario
   $status = $_POST['status'] ;
   if (count($_POST['acao']) == 0)
      $acao = array() ;
   else
      $acao = $_POST['acao'] ;
   $senhas = $_POST['authenticate'] ;   
   $usa_senha = $_POST['usa_auth'] ;
   $ccustos   = $_POST['ccustos'] ;
   $atualizado  = "; Atualizado em:".date('d/m/Y H:i:s')."\n" ;
   // Varre todo Array do form
   $novo_conteudo = $novas_senhas = "" ;
   foreach ($status as $sl => $valor) {
      //echo "<br>Valor = $valor   Acao=".$acao[$sl] ;
      if (($valor == "S" && $acao[$sl]) || ($valor == "N" && !$acao[$sl]) ) 
         continue;
      else {
         $novo_conteudo .= ";SNEP($sl): Room added by system\n" ;
         $novo_conteudo .= "exten => $sl,1,Set(CHANNEL(language)=pt_BR)\n" ;
         // Se vai usar senha ou alterou senha, ajusta arquivo de senhas
         // E insere rerencia no dialplan
         if ($usa_senha[$sl] == "yes" && strlen($senhas[$sl])>0) {
            // Se senha e nova ou foi alterada
            if ($senha[$sl] != $salas_orig[$sl]) 
               $authenticate = md5($senhas[$sl]) ;
            else
               $authenticate = $senhas[$sl] ;
            $novas_senhas .= "$sl:$authenticate\n" ;
            $novo_conteudo .= "exten => $sl,n,Authenticate(/etc/asterisk/snep/snep-authconferences.conf,m)\n" ;
         } // Fim de Senhas
         $novo_conteudo .= "exten => $sl,n,Set(CDR(accountcode)=".$ccustos[$sl].")\n";
         if ($conf_app == "C")
             $novo_conteudo .= "exten => $sl,n,Conference(\${EXTEN}/S)\n";
         else
             $novo_conteudo .= "exten => $sl,n,Meetme(\${EXTEN})\n";
            
         $novo_conteudo .= "exten => $sl,n,Hangup\n\n" ;
      }
   } // Fim do foreach que varre todas as salas


   // Remove todas as entradas do array de conferencias, com excessao do cabecalho
   $flag = True ;
   foreach($linhas_conf as $key => $value) {
      if (substr($value,0,1) == ";" && $flag) {
         if (strpos($value,"; Atualizado") === 0) 
            $linhas_conf[$key] = $atualizado ;
         continue ;
      }   
      if (strpos($value,"[conferences]") === 0) {
         $flag = False ;
         continue ;
      }
      unset($linhas_conf[$key]) ;
   } // Fim do foreach para remove salas do arquivo
   
   // --->>>> Ajusta arquivo de conferencias
   $conteudo = implode('',$linhas_conf) ;
   // Adiciona novo conteudo no arquivo   
   $conteudo .= $novo_conteudo ;
   // Adiciona Complemento do Arquivo
   $conteudo .= "; Next Lines = Default of System - don't change, please\n";
   $conteudo .= "exten => i,1,Set(CHANNEL(language)=pt_BR)\n";
   $conteudo .= "exten => i,n,Playback(invalid)\n";
   $conteudo .= "exten => i,n,Hangup\n\n";
   $conteudo .= "exten => t,1,Hangup\n";
   $conteudo .= "exten => h,1,Hangup\n";
   $conteudo .= "exten => H,1,Hangup";
   $handle = @fopen($file_conf, "w+");
   if (!$handle) 
      return False ;   
   if (fwrite($handle, $conteudo) === FALSE) 
      return False ;
   fclose($handle) ;
   
   // --->>>> Ajusta arquivo de senhas
   // Remove todas as entradas do array de senhas, com excessao do cabecalho
   foreach($linhas_auth as $key => $value) {      
      if (strpos($value,"; Atualizado") === 0) { // remove linha do atualizado
         $linhas_auth[$key] = $atualizado ;
         continue ;
      }
      if (substr($value,0,1) == ";")  // Comentarios, mantem
         continue ;
      unset($linhas_auth[$key]) ;
   } // Fim do foreach para remove senhas
   $conteudo = implode('',$linhas_auth) ;
   // Adiciona novo conteudo no arquivo   
   $conteudo .= $novas_senhas ;
   // Adiciona Complemento do Arquivo
   $handle = @fopen($file_auth, "w+");
   if (!$handle) 
      return False ;   
   if (fwrite($handle, $conteudo) === FALSE) 
      return False ;
   fclose($handle) ;

   // Se Ativou ou desativou alguma sala, reload no asterisk
   ast_status("dialplan reload","") ;
   return True ;
 }
?>
