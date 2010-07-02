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
unset($_SESSION['filas_selec']);

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

$no_khomp = false;
if( $khompInfo->hasWorkingBoards() ) {
    foreach( $khompInfo->boardInfo() as $board ) {
        if( preg_match("/KFXS/", $board['model']) ) {
            $channels = range(0, $board['channels']);

            $khomp_boards_list[$board['id']] = $channels;
        }
    }
}
else {
    $no_khomp = true;
}
$smarty->assign('no_khomp',$no_khomp);

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
$smarty->assign('OPCOES_YN',$tipos_yn) ;
$smarty->assign('TYPES',array('peer' => "Peer",'friend' => 'Friend'));
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
function principal() {

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
    $row['usa_auth'] = "no";
    $row['usa_vc'] = "no";
    $row['qualify'] = "no";
    $row['nat'] = "no";
    $row['dtmfmode'] = "rfc2833";
    $row['channel_tech'] = "SIP";
    $row['time'] = "n";

    // Monta Lista de Filas Disponiveis
    if (!isset($filas_disp) || count($filas_disp) == 0) {
        try {
            $sql_queue = "SELECT name FROM queues ORDER by name" ;
            $row_queue = $db->query($sql_queue)->fetchAll();
        } catch (Exception $e) {
            display_error($LANG['error'].$e->getMessage(),true) ;
        }
        unset($val);
        if(count($row_queue) > 0) {
            foreach ($row_queue as $val)
                $filas_disp[$val['name']] = $val['name'];
            asort($filas_disp);
        }
        else {
            $filas_disp = "";
        }
    }
    $row['group'] = "users";
    $row['type'] = "peer";
    // Variavies do Template

    $count = 20;//count($row);

    $smarty->assign("khomp_channel", False);
    $smarty->assign('FILAS_DISP',$filas_disp);
    $smarty->assign('dt_ramais',$row) ;
    $smarty->assign('COUNT',$count) ;
    $smarty->assign('ACAO',"cadastrar") ;
    display_template("ramais.tpl",$smarty,$titulo) ;
}
/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar() {
    global $LANG, $type, $password, $manual, $db, $trunk, $name, $group, $vinc, $callerid, $qualify,  $secret, $cod1, $cod2, $cod3, $cod4, $cod5,$dtmfmode, $vinculo, $email, $call_limit, $calllimit, $usa_vc, $pickupgroup, $def_campos_ramais, $canal,$nat, $peer_type, $authenticate, $usa_auth, $filas_selec, $tempo, $time_total, $time_chargeby, $khomp_boards, $khomp_channels;

    $context = "default";

    // Campos com dados identicos ao outros
    $fromuser = $name;
    $username = $name ;
    $callerid = addslashes($callerid) ;
    $fullcontact = "" ;
    $call_limit = $calllimit ;
    $callgroup  = $pickupgroup ;
    $peer_type = "R" ; // Ramais

    // monta a cadeia de codecs permitidos
    $allow="" ;
    $allow .= (strlen(trim($cod1))>0) ? $cod1 : "" ;
    $allow .= (strlen(trim($cod2))>0) ? ";$cod2" : ";" ;
    $allow .= (strlen(trim($cod3))>0) ? ";$cod3" : ";" ;
    $allow .= (strlen(trim($cod4))>0) ? ";$cod4" : ";" ;
    $allow .= (strlen(trim($cod5))>0) ? ";$cod5" : ";" ;

    // Monta a cadeia de canais
    $canal = strtoupper($canal);
    if($canal == "KHOMP") {
        $canal .= "/b" . $khomp_boards . 'c' . $khomp_channels;
    }
    else if($canal == "VIRTUAL") {
        $canal .= "/" . $trunk;
    }
    else if($canal == "MANUAL") {
        $canal .= "/" . $manual;
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
        $sql.= "name, password,callerid,context,mailbox,qualify,";
        $sql.= "secret,type,allow,fromuser,username,fullcontact,";
        $sql.= "dtmfmode,email,`call-limit`,incominglimit,";
        $sql.= "outgoinglimit, usa_vc, pickupgroup, canal,nat,peer_type, authenticate," ;
        $sql.= "trunk, `group`, callgroup, time_total, " ;
        $sql.= "time_chargeby ".$sql_fields_default ;
        $sql.= ") values (";
        $sql.=  "'$name','$password','$callerid','$context','$name','$qualify',";
        $sql.= "'$secret','$type','$allow','$fromuser','$username','$fullcontact',";
        $sql.= "'$dtmfmode','$email','$call_limit','1',";
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
            $sql.= " (fullname, email, mailbox, password, customer_id, `delete`) VALUES ";
            $sql.= " ('$callerid', '$email','$name','$password','$name', 'yes')";
            $stmt = $db->prepare($sql) ;
            $stmt->execute() ;
        }

        // Filas Relacionadas
        if ( count($filas_selec) > 0 ) {
            $stmt = $db->prepare("INSERT into queue_peers (ramal,fila) VALUES (:id, :fila)") ;
            $stmt->bindParam('id',$id) ;
            $stmt->bindParam('fila',$tmp_fila) ;
            foreach ($filas_selec as $val) {
                $tmp_fila = $val ;
                $stmt->execute() ;
            }
        }

        // Seta proprio ramal como seu vínculo
        Snep_Vinculos::setVinculos($name, 'R', $name);

        $db->commit();

        /* Gera arquivo /etc/asterisk/snep/snep-sip.conf */
        grava_conf();

        echo "<meta http-equiv='refresh' content='0;url=../src/ramais.php'>\n" ;

    } catch (Exception $ex ) {
        $db->rollBack();
        display_error($LANG['error'].$ex->getMessage(),true);
    }
}

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Altera um registro
------------------------------------------------------------------------------*/
function alterar() {
    global $LANG,$db,$smarty,$titulo, $acao, $user_groups ;

    $id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
    if (!$id) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
    }

    $sql = "SELECT id, type, name, callerid, context, mailbox, qualify, secret,";
    $sql.= " allow, dtmfmode, vinculo, email, `call-limit`, incominglimit,";
    $sql.= " outgoinglimit, usa_vc, pickupgroup, nat, canal, authenticate, " ;
    $sql.= " `group`, time_total, time_chargeby FROM peers WHERE id=".$id;

    try {
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
    $khomp_fail = false;

    $row['peer_type'] = $row['type'];

    $row['channel_tech'] = substr($row['canal'], 0, strpos($row['canal'], '/'));

    $khomp_error = false;
    $khomp_channels = null;
    if($row['channel_tech'] == "KHOMP") {
        $interface = substr($row['canal'], strpos($row['canal'], '/')+1);
        $khomp_board = substr($interface,1,1);
        $khomp_channel = substr($interface,3);
        $khompInfo = new PBX_Khomp_Info();
        try {
            $boardInfo = $khompInfo->boardInfo($khomp_board);
            $khomp_channels = range(0,$boardInfo['channels']-1);
        }
        catch( PBX_Khomp_Exception_NoSuchBoard $ex ) {
            $khomp_error = true;
            $khomp_board = false;
            $khomp_channel = true;
        }
        catch( PBX_Khomp_Exception_NoKhomp $ex ) {
            $khomp_error = true;
            $khomp_board = false;
            $khomp_channel = true;
        }
    }
    else if($row['channel_tech'] == "MANUAL") {
        $row['manual'] = substr($row['canal'], strpos($row['canal'], '/')+1);
    }
    else if($row['channel_tech'] == "VIRTUAL") {
        $row['trunk'] = substr($row['canal'], strpos($row['canal'], '/')+1);
    }

    $smarty->assign ('khomp_error',$khomp_error);

    $smarty->assign ('khomp_board',(int)$khomp_board);
    $smarty->assign ('khomp_channel',(int)$khomp_channel);
    $smarty->assign ('khomp_channels',$khomp_channels);

    // Para Verificar se mudou o nome - causa: tabela voicemail_users
    $row['old_name'] = $row['name'];

    // Para Verificar se mudou a senha do cadeado
    $row['old_authenticate'] = $row['authenticate'];

    if ($row['authenticate']) {
        $row['usa_auth'] = "yes";
    }
    else {
        $row['usa_auth'] = "no";
    }

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
function grava_alterar() {
    global $LANG, $manual, $db, $type, $id, $trunk, $name, $password, $callerid, $qualify, $secret, $cod1, $cod2, $cod3, $cod4, $cod5, $dtmfmode, $email,  $call_limit, $calllimit, $usa_vc, $old_name, $pickupgroup, $nat,$canal, $old_vinculo,$vinculo,$authenticate, $old_authenticate, $usa_auth, $filas_selec, $group,$time_total, $time_chargeby, $tempo, $khomp_boards, $khomp_links, $khomp_channels;

    $context = "default";

    // Campos com dados identicos ao outros
    $fromuser = $name;
    $username = $name ;
    $callerid = addslashes($callerid) ;
    $fullcontact = "" ;
    $call_limit = $calllimit ;
    $callgroup  = $pickupgroup ;
    $pickupgroup = $pickupgroup == "" ? 'null' : "'$pickupgroup'";
    $peer_type = 'R';

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

    $canal = strtoupper($canal);
    if($canal == "KHOMP") {
        $canal = "KHOMP/b" . $khomp_boards . 'c' . $khomp_channels;
    }
    else if($canal == "VIRTUAL") {
        $canal = "VIRTUAL/" . $trunk;
    }
    else if($canal == "MANUAL") {
        $canal = "MANUAL/" . $manual;
    }
    else {
        $canal .= "/" . $name;
    }

    $authenticate = $usa_auth == "yes"? 'true' : 'false';

    $sql = "UPDATE peers ";
    $sql.=" SET name='$name',password='$password' , callerid='$callerid', ";
    $sql.= "context='$context',mailbox='$name',qualify='$qualify',";
    $sql.= "secret='$secret',type='$type', allow='$allow', fromuser='$fromuser',";
    $sql.= "username='$username',fullcontact='$fullcontact',dtmfmode='$dtmfmode',";
    $sql.= "email='$email', `call-limit`='$call_limit',";
    $sql.= "outgoinglimit='1', incominglimit='1',";
    $sql.= "usa_vc='$usa_vc',pickupgroup=$pickupgroup,callgroup='$callgroup',";
    $sql.= "nat='$nat',canal='$canal', authenticate=$authenticate, ";
    $sql.= "`group`='$group', ";
    $sql.= "time_total=$time_total, time_chargeby='$time_chargeby'  WHERE id=$id";

    $db->beginTransaction();
    $stmt = $db->prepare($sql);
    $stmt->execute();

    // Alteracao da tabela voicemail_users
    $db->delete("voicemail_users"," mailbox='$name' ");
    if ($usa_vc == "yes") {
        $sql = "insert into voicemail_users ";
        $sql.= " (fullname, email, mailbox, password, customer_id, `delete`) values ";
        $sql.= " ('$callerid', '$email','$name','$password','$name', 'yes')";
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }

    // Filas Relacionadas
    if (count($filas_selec)>0) {
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

    try {
        $db->commit();

        /* Gera arquivo de configuração */
        grava_conf();
    } catch (Exception $ex ) {
        $db->rollBack();
        display_error($LANG['error'].$ex->getMessage(),true) ;
    }
    $pag =  ($_SESSION['pagina'] ? $_SESSION['pagina'] : 1 );
    echo "<meta http-equiv='refresh' content='0;url=../src/extensions.php?page=$pag'>\n" ;
}
