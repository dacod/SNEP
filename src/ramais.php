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

ver_permissao(16) ;

global $acao ;
unset($_SESSION['filas_selec']) ;
// Monta lista de Contextos
$ext_list = explode(";",$SETUP['ambiente']['extensions_on']);
$extensions_list = array() ;
$extensions_list[''] = '' ;
foreach ($ext_list as $val)
$extensions_list["$val"] = $val ;
asort($extensions_list);

// Monta Lista de Grupos de Ramais
$user_groups = array() ;
try {
    $sql_grp = "SELECT * FROM groups WHERE name != 'all' ORDER BY name" ;
    $row_grp = $db->query($sql_grp)->fetchAll();
} catch (Exception $e) {
   display_error($LANG['error'].$e->getMessage(),true) ;
}

foreach($row_grp as $grp) {
    switch($grp['name']) {
        case 'admin':
            $grp_name = 'Administradores';
            break;
        case 'users':
            $grp_name = 'Usu&aacute;rios';
            break;
        default:
            $grp_name = $grp['name'];
    }
    $user_groups[$grp['name']] = $grp_name;
}

// Monta Lista de Grupos de Captura
if (!isset($grupos) || count($grupos) == 0) {
    try {
        $sql_grp = "SELECT * FROM grupos ORDER by nome" ;
        $row_grp = $db->query($sql_grp)->fetchAll();
    } catch (Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }
    unset($val);
    $grupos = array(""=>$LANG['undef']);
    foreach ($row_grp as $val) {
       $grupos[$val['cod_grupo']] = $val['nome'] ;
    }
    asort($grupos) ;
}

// Monta informações para placas khomp
$khomp_boards_list = array();
try {
    $khompInfo = new PBX_Khomp_Info();
}
catch( Asterisk_Exception_CantConnect $ex ) {
    display_error("Falha ao conectar com o servidor Asterisk: {$ex->getMessage()}", true, 0);
}

if( $khompInfo->hasWorkingBoards() ) {
    foreach( $khompInfo->boardInfo() as $board ) {
        if( ereg("KFXS", $board['model']) ) {
            $channels = range(0, $board['channels']);

            $khomp_boards_list[$board['id']] = $channels;
        }
    }
}

/* ----------------------------------------------------------------- */
/* Lista de troncos */
/* ----------------------------------------------------------------- */
$trunks = array();
foreach (PBX_Trunks::getAll() as $tronco) {
    $trunks[$tronco->getId()] = $tronco->getId() . " - " . $tronco->getName();
}
$smarty->assign('TRUNKS', $trunks);
 
// Variaveis de ambiente do form
$smarty->assign('ACAO',$acao) ; 
$smarty->assign('EXTEN_LIST',$extensions_list);   
$smarty->assign('OPCOES_YN',$tipos_yn) ;
$smarty->assign('OPCOES_DTMF',$tipos_dtmf) ;
$smarty->assign('OPCOES_CODECS',$tipos_codecs) ;
$smarty->assign('OPCOES_GRUPOS',$grupos);
$smarty->assign('khomp_boards', $khomp_boards_list);
$smarty->assign('OPCOES_USERGROUPS',$user_groups);
$smarty->assign('PROTOTYPE', True);


if ($acao == "cadastrar") {
    cadastrar();
} elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_register']." -> ".$LANG['menu_ramais']." -> ".$LANG['change'];
    alterar() ;
} elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
} elseif ($acao ==  "excluir") {
    excluir() ;
} elseif ($acao ==  "pesquisar") {
    pesquisa_canal() ;
} else {
    $titulo = $LANG['menu_register']." -> ".$LANG['menu_ramais']." -> ".$LANG['include'];
    principal() ;
}
 
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
 ------------------------------------------------------------------------------*/
 function principal()  {
   global $db,$smarty,$titulo,$codecs_default,$SETUP ;
   // Sugestao de numero proximo ramal
   try {
      $sql = "SELECT name FROM peers " ;
      $sql.= " WHERE peer_type = 'R'" ;
      $sql.= " ORDER BY CAST(name as DECIMAL) DESC LIMIT 1" ;
      $row = $db->query($sql)->fetch();
   } catch (PDOException $e) {
      display_error($LANG['error'].$e->getMessage(),true) ;
   }  
   $row['name'] = trim($row['name'])+1 ;
   if ( $row['name'] == "1" )
      $row['name'] = "" ;
   // Codecs Default
   $row = $row + $codecs_default ;

   // Authenticate
   $row['usa_auth'] = "No" ;
   
   // Monta Lista de Filas Disponiveis
   if (!isset($filas_disp) || count($filas_disp) == 0) {
      try {
         $sql_queue = "SELECT name FROM queues ORDER by name" ;
         $row_queue = $db->query($sql_queue)->fetchAll();
      } catch (Exception $e) {
         display_error($LANG['error'].$e->getMessage(),true) ;
      }
      unset($val);
      if(count($row_queue) > 0){
        foreach ($row_queue as $val)
           $filas_disp[$val['name']] = $val['name'];
        asort($filas_disp);
      }
      else {
        $filas_disp = "";
      }
   }
   $row['group'] = "users";
   // Variavies do Template

   $count = 20;//count($row);

   $smarty->assign('FILAS_DISP',$filas_disp);
   $smarty->assign('dt_ramais',$row) ;
   $smarty->assign('COUNT',$count) ;
   $smarty->assign('ACAO',"cadastrar") ;
   display_template("ramais.tpl",$smarty,$titulo) ;
}
/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $trunk, $name, $group, $vinc, $callerid, $mailbox, $qualify,  $secret, $cod1, $cod2, $cod3, $cod4, $cod5,$dtmfmode, $vinculo, $email, $call_limit, $calllimit, $usa_vc, $senha_vc, $pickupgroup, $def_campos_ramais, $canal,$nat, $peer_type, $authenticate, $usa_auth, $filas_selec, $tempo, $time_total, $time_chargeby, $khomp_boards, $khomp_channels;

   $context = "default";

   // Campos com dados identicos ao outros
   $fromuser = $name; 
   $username = $name ;
   $callerid = addslashes($callerid) ;
   $context  = addslashes($context) ;
   $mailbox  = addslashes($mailbox) ; 
   $fullcontact = "" ; 
   $call_limit = $calllimit ;
   $callgroup  = $pickupgroup ;
   $peer_type = "R" ; // Ramais
      
   $type = "peer"; // Default para todos, caso alterado pode causar problemas em
                   // registro de troncos sip.
   
   // monta a cadeia de codecs permitidos
   $allow="" ;
   $allow .= (strlen(trim($cod1))>0) ? $cod1 : "" ;
   $allow .= (strlen(trim($cod2))>0) ? ";$cod2" : ";" ;
   $allow .= (strlen(trim($cod3))>0) ? ";$cod3" : ";" ;
   $allow .= (strlen(trim($cod4))>0) ? ";$cod4" : ";" ;
   $allow .= (strlen(trim($cod5))>0) ? ";$cod5" : ";" ;  
   
   // Monta a cadeia de canais
   if($canal == "KHOMP") {
       $canal .= "/b" . $khomp_boards . 'c' . $khomp_channels;
   }
   else if($canal == "VIRTUAL") {
        $canal .= "/" . $trunk;
   }
   else {
        $canal .= "/" . $name;
   }

   // Tempos de Minutagem
   if ($tempo == "s") {
      $time_chargeby = $time_total > 0? "'$time_chargeby'": "NULL";
      $time_total = $time_total*60;
      $time_total = $time_total == 0? "NULL": "'$time_total'";
   } else {
      $time_chargeby = "NULL";
      $time_total = "NULL";
   }

   $authenticate = $usa_auth == "yes"? 'true' : 'false';
      
   // Monta array para SQL da tabela de vinculos
   if (strlen($vinculo) > 0) {
      $vinc=array();
      $vinc = monta_vinculo($vinculo); 
   }
   
   // Monta lista campos Default
   $sql_fields_default = $sql_values_default = "" ;
   foreach( $def_campos_ramais as $key => $value ) {
       $sql_fields_default .= ",$key";
       $sql_values_default .= ",$value" ;
   } 
   
   $pickupgroup = ($pickupgroup == '' ? "NULL" : $pickupgroup);

   try {
      $db->beginTransaction() ;
      $sql = "INSERT INTO peers (" ;
      $sql.= "name,callerid,context,mailbox,qualify,";
      $sql.= "secret,type,allow,fromuser,username,fullcontact,";
      $sql.= "dtmfmode,vinculo,email,`call-limit`,incominglimit,";
      $sql.= "outgoinglimit, usa_vc, pickupgroup, canal,nat,peer_type, authenticate," ;
      $sql.= "trunk, `group`, callgroup, time_total, " ;
      $sql.= "time_chargeby ".$sql_fields_default ;
      $sql.= ") values (";
      $sql.=  "'$name','$callerid','$context','$mailbox','$qualify',";
      $sql.= "'$secret','$type','$allow','$fromuser','$username','$fullcontact',";
      $sql.= "'$dtmfmode','$vinculo','$email','$call_limit','1',";
      $sql.= "'1', '$usa_vc', $pickupgroup ,'$canal','$nat', '$peer_type',";
      $sql.= "$authenticate,'no','$group',";
      $sql.= "'$callgroup', $time_total, '$time_chargeby' ".$sql_values_default;
      $sql.= ")" ; 
      $stmt = $db->prepare($sql) ;
      $stmt->execute() ;
      // Pega Codigo do Ramal que esta sendo cadastrado
      $sql = "SELECT id FROM peers ORDER BY id DESC LIMIT 1" ;
      $id = $db->query($sql)->fetch();
      $id = $id['id'] ;

      if ($usa_vc) {
         $sql = "INSERT INTO voicemail_users ";
         $sql.= " (fullname, email, mailbox, password, customer_id) VALUES ";
         $sql.= " ('$callerid', '$email','$mailbox','$senha_vc','$name')";
         $stmt = $db->prepare($sql) ;
         $stmt->execute() ;
      }
      // Vinculos
      if (count($vinc)>0){
         $stmt = $db->prepare("INSERT into vinculos (cod_usuario,ramal) VALUES (:codigo, :ramal)") ;
         $stmt->bindParam('codigo',$tmp_cod) ;
         $stmt->bindParam('ramal',$tmp_ramal) ;
         $tmp_cod = $name ;
         foreach ($vinc as $val) {
            $tmp_ramal = $val ;
            $stmt->execute() ;
         }
      }
      // Filas Relacionadas
      if (count($filas_selec)>0){
          $stmt = $db->prepare("INSERT into queue_peers (ramal,fila) VALUES (:id, :fila)") ;
         $stmt->bindParam('id',$id) ;
         $stmt->bindParam('fila',$tmp_fila) ;
         foreach ($filas_selec as $val) {
            $tmp_fila = $val ;
            $stmt->execute() ;
         } 
      }      
      $db->commit();
      
      /* Gera arquivo /etc/asterisk/snep/snep-sip.conf */ 
      grava_conf();      

      echo "<meta http-equiv='refresh' content='0;url=../src/ramais.php'>\n" ;
   } catch (Exception $e) {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),true);
   }
 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Altera um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG,$db,$smarty,$titulo, $acao, $user_groups ;
   $id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
   if (!$id) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
      $sql = "SELECT id,name,callerid,context,mailbox,qualify,secret,";
      $sql.= " allow,dtmfmode, vinculo, email, `call-limit`,incominglimit,";
      $sql.= " outgoinglimit, usa_vc, pickupgroup, nat, canal, authenticate, " ;
      $sql.= " `group`, time_total, time_chargeby FROM peers WHERE id=".$id;
      $row = $db->query($sql)->fetch();
   } catch (PDOException $e) {
      display_error($LANG['error'].$e->getMessage().$sql,true) ;
   }  
   
   // Desmembra campo allow
   $cd = explode(";",$row['allow']);
   $row['cod1']=$cd[0] ;
   $row['cod2']=$cd[1] ;
   $row['cod3']=$cd[2] ;
   $row['cod4']=$cd[3] ;
   $row['cod5']=$cd[4] ;

   $row['call_limit'] = $row['call-limit'];

   $khomp_board = false;
   $khomp_channel = false;
   
   $row['channel_tech'] = substr($row['canal'], 0, strpos($row['canal'], '/'));
   if($row['channel_tech'] == "KHOMP") {
       $interface = substr($row['canal'], strpos($row['canal'], '/')+1);
       $khomp_board = substr($interface,1,1);
       $khomp_channel = substr($interface,3);
   }

   if($row['channel_tech'] == "VIRTUAL") {
       $row['trunk'] = substr($row['canal'], strpos($row['canal'], '/')+1);
   }
   
   $smarty->assign ('khomp_board',$khomp_board);
   $smarty->assign ('khomp_channel',$khomp_channel);

    // Para Verificar se mudou o nome - causa: tabela voicemail_users
   $row['old_name'] = $row['name'];

   // Para Verificar se mudou a senha do cadeado
   $row['old_authenticate'] = $row['authenticate'];

   if ($row['authenticate'])
      $row['usa_auth'] = "yes";
   else
      $row['usa_auth'] = "no";
      
   // Para Verificar se mudou vinculos - causa: tabela vinculos
   $row['old_vinculo'] = $row['vinculo'];
      
   // Monta Lista de Filas Disponiveis
   if (!isset($filas_disp) || count($filas_disp) == 0) {
      $filas_disp = array() ;
      try {
         $sql_queue = "SELECT queues.name FROM queues ";
         $sql_queue.= " WHERE queues.name NOT IN (SELECT fila FROM queue_peers ";
         $sql_queue.= " WHERE queue_peers.ramal = ".$id.") ORDER by name";
         $row_queue = $db->query($sql_queue)->fetchAll();
      } catch (Exception $e) {
         display_error($LANG['error'].$e->getMessage(),true);
      }
      if (count($row_queue) > 0) {
         unset($val);      
         foreach ($row_queue as $val)
            $filas_disp[$val['name']] = $val['name'];
         asort($filas_disp);
      }
   }

   
   // Monta Lista de Filas Selecionadas para o ramal
   $filas_selec = array() ;
   if ($acao == "alterar") {
      if (!isset($filas_selec) || count($filas_selec) == 0) {
         try {
            $sql_queue = "SELECT fila,ramal FROM queue_peers ";
            $sql_queue.= " WHERE ramal = ".$id." ORDER by fila" ;
            $row_queue = $db->query($sql_queue)->fetchAll();
         } catch (Exception $e) {
            display_error($LANG['error'].$e->getMessage(),true) ;
         }
         unset($val);
         if (count($row_queue) > 0) {
            foreach ($row_queue as $val)
               $filas_selec[$val['fila']] = $val['fila'] ;
            asort($filas_selec) ;
         }
      }
   }
   $row['time'] = isset($row['time_total'])? "s" : "n";
   $row['time_total'] = round($row['time_total']/60);
   
   $smarty->assign('FILAS_SELEC',$filas_selec);
   $smarty->assign('FILAS_DISP',$filas_disp);
   $smarty->assign ('dt_ramais',$row);
   $smarty->assign('ACAO',"grava_alterar") ;
   display_template("ramais.tpl",$smarty,$titulo);
   
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $id, $trunk, $name, $callerid, $mailbox, $qualify, $secret, $cod1, $cod2, $cod3, $cod4, $cod5, $dtmfmode, $email,  $call_limit, $calllimit, $usa_vc, $senha_vc, $no_vc, $old_name, $pickupgroup, $nat,$canal, $old_vinculo,$vinculo,$authenticate, $old_authenticate, $usa_auth, $filas_selec, $group,$time_total, $time_chargeby, $tempo, $khomp_boards, $khomp_links, $khomp_channels;

   $context = "default";
   
   // Campos com dados identicos ao outros
   $fromuser = $name; 
   $username = $name ;
   $callerid = addslashes($callerid) ;
   $context  = addslashes($context) ;
   $mailbox  = addslashes($mailbox) ; 
   $fullcontact = "" ;
   $call_limit = $calllimit ;
   $callgroup  = $pickupgroup ;
   $pickupgroup = $pickupgroup == "" ? 'null' : "'$pickupgroup'";
   
   $type = "peer"; // Default para ramais

   if ($tempo == "n") {
      $time_chargeby = "NULL";
      $time_total = "NULL";
   } else {
      $time_chargeby = $time_total > 0 ? $time_chargeby: "NULL";
      $time_total = $time_total*60;
   }
   // monta a cadei de codecs allow
   $allow="" ;
   $allow .= (strlen(trim($cod1))>0) ? $cod1 : "" ;
   $allow .= (strlen(trim($cod2))>0) ? ";$cod2" : ";" ;
   $allow .= (strlen(trim($cod3))>0) ? ";$cod3" : ";" ;
   $allow .= (strlen(trim($cod4))>0) ? ";$cod4" : ";" ;
   $allow .= (strlen(trim($cod5))>0) ? ";$cod5" : ";" ;    
   
   if($canal == "KHOMP") {
       $canal .= "/b" . $khomp_boards . 'c' . $khomp_channels;
   }
   else if($canal == "VIRTUAL") {
        $canal .= "/" . $trunk;
   }
   else {
        $canal .= "/" . $name;
   }
       
   // Monta array para SQL da tabela de vinculos
   $vinc=array();
   if ($vinculo != $old_vinculo) {
      $vinc = monta_vinculo($vinculo); 
   }

   $authenticate = $usa_auth == "yes"? 'true' : 'false';

   $sql = "UPDATE peers ";
   $sql.=" SET name='$name',callerid='$callerid', ";
   $sql.= "context='$context',mailbox='$mailbox',qualify='$qualify',";
   $sql.= "secret='$secret',type='$type', allow='$allow', fromuser='$fromuser'," ;
   $sql.= "username='$username',fullcontact='$fullcontact',dtmfmode='$dtmfmode'," ;
   $sql.= "vinculo='$vinculo', email='$email', `call-limit`='$call_limit',"; 
   $sql.= "outgoinglimit='1', incominglimit='1',";
   $sql.= "usa_vc='$usa_vc',pickupgroup=$pickupgroup,callgroup='$callgroup'," ;
   $sql.= "nat='$nat',canal='$canal', authenticate=$authenticate, ";
   $sql.= "`group`='$group', ";
   $sql.= "time_total=$time_total, time_chargeby='$time_chargeby'  WHERE id=$id" ;

   try {
       $db->beginTransaction() ;
       $stmt = $db->prepare($sql);
       $stmt->execute() ;
       // Alteracao da tabela voicemail_users
       // Alteracao da tabela voicemail_users
       if ($usa_vc == "yes") {
          if ($no_vc == "yes") {   // Se Nao tem cadastro no voicemail, insere-o
             $sql = "insert into voicemail_users ";
             $sql.= " (fullname, email, mailbox, password, customer_id) values ";
             $sql.= " ('$callerid', '$email','$mailbox','$senha_vc','$name')";      
          } else {  // Senao, somente altera os dados necessarios
             $sql = "UPDATE voicemail_users SET password='$senha_vc', email='$email',";
             $sql .= "customer_id='$name', fullname='$callerid', mailbox='$mailbox' ";
             $sql.= " WHERE customer_id = '$old_name'";
          }
       } else {
         if ($no_vc == "no") {   // Nao vai mais usar Voicemail mas tem cadastrado
            $sql = "delete from voicemail_users " ;
            $sql.= " WHERE customer_id = '$old_name'";
         }
       }
       $stmt = $db->prepare($sql);
       $stmt->execute() ;
       // Vinculos
       if (count($vinc)>0){
         $stmt = $db->prepare("DELETE from vinculos where cod_usuario='$name'");
         $stmt->execute() ;
         $stmt = $db->prepare("INSERT into vinculos (cod_usuario,ramal) VALUES (:codigo, :ramal)") ;
         $stmt->bindParam('codigo',$tmp_cod) ;
         $stmt->bindParam('ramal',$tmp_ramal) ;
         $tmp_cod = $id ;
         foreach ($vinc as $val) {
            $tmp_ramal = $val ;
            $stmt->execute() ;
         }
       } else {
         $stmt = $db->prepare("DELETE from vinculos where cod_usuario='$name'");
         $stmt->execute() ;
       }     
       // Filas Relacionadas
       if (count($filas_selec)>0){
         $stmt = $db->prepare("DELETE from queue_peers where ramal=$id");
         $stmt->execute() ;
         $stmt = $db->prepare("INSERT into queue_peers (ramal,fila) VALUES (:id, :fila)") ;
         $stmt->bindParam('id',$id) ;
         $stmt->bindParam('fila',$tmp_fila) ;
         foreach ($filas_selec as $val) {
            $tmp_fila = $val ;
            $stmt->execute() ;
         } 
       } else {
         $stmt = $db->prepare("DELETE from queue_peers where ramal=$id");
         $stmt->execute() ;
       }
       $db->commit();
       
       /* Gera arquivo de configura??o */ 
       grava_conf();

       $pag =  ($_SESSION['pagina'] ? $_SESSION['pagina'] : 1 );

       echo "<meta http-equiv='refresh' content='0;url=../src/rel_ramais.php?pag=$pag'>\n" ;
   } catch (Exception $e) {
       $db->rollBack();
       display_error($LANG['error'].$e->getMessage(),true) ;
   }
   grava_conf();
 }

/*------------------------------------------------------------------------------
 * Funcao EXCLUIR - Excluir registro selecionado da Tabela RAMAIS
 *                  Excluir registro correspondente da tabela voicemail_users
 *                  Excluir registro correspondente da tabela vinculos
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $db, $name, $canal, $id;

   //display_confirme($LANG['msg_excluded'],true) ;

   $id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
   if (!$id) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
       // Fazendo procura por referencia a esse ramal em regras de negócio.
        $rules_query = "SELECT id, `desc` FROM regras_negocio WHERE origem LIKE '%R:$name%' OR destino LIKE '%R:$name%'";
        $regras = $db->query($rules_query)->fetchAll();
        if(count($regras) > 0) {
            $msg = $LANG['extension_conflict_in_rules'].":<br />\n";
            foreach ($regras as $regra) {
                $msg .= $regra['id'] . " - " . $regra['desc'] . "<br />\n";
            }
            display_error($msg,true);
            exit(1);
        }
      $sql = "DELETE FROM peers WHERE id='".$id."'";
      $db->beginTransaction() ;
      $stmt = $db->prepare($sql);
      $stmt->execute() ;
      $sql = "delete from voicemail_users where customer_id='$name'";
      $stmt = $db->prepare($sql);
      $stmt->execute() ;
      $sql = "delete from vinculos where cod_usuario='$name'";
      $stmt = $db->prepare($sql);
      $stmt->execute() ;
      $db->commit();
      
      /* Gera arquivo de configura??o */ 
      grava_conf();
      
      //echo "<meta http-equiv='refresh' content='0;url=../src/rel_ramais.php'>\n" ;
      
   } catch (PDOException $e) {
      display_error($LANG['error'].$e->getMessage(),true) ;
   }
   
}
?>
<script src="../includes/javascript/prototype.js" type="text/javascript"></script>
