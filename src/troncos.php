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
ver_permissao(31);

// Testa conexao com Asterisk
try {
    $asterisk = PBX_Asterisk_AMI::getInstance();
    $asterisk->Command("core show version");
}
catch( Asterisk_Exception_CantConnect $ex ) {
    display_error("Falha ao conectar com o servidor Asterisk: {$ex->getMessage()}", true, -1);
}


// Monta lista de Troncos existentes - para Redundancia
// ----------------------------------------------------
$sql = "SELECT id,name,callerid FROM trunks " ;
$sql.= " ORDER BY name" ;
$trunks_disp = array("0"=>"") ;
try {
    $row = $db->query($sql)->fetchAll() ;
    foreach ($row as $val) {
        $trunks_disp[$val['id']] = $val['name']."-".$val['callerid'] ;
    }
} catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
}

// Informações de placas khomp
$khomp_info = new PBX_Khomp_Info();
$khomp_boards = array();
if($khomp_info->hasWorkingBoards()) {
    foreach ($khomp_info->boardInfo() as $board) {
        if(!preg_match("/FXS/", $board['model'])) {
            $khomp_boards["b" . $board['id']] = "{$board['id']} - Placa {$board['model']}";
            $id = "b" . $board['id'];
            if(preg_match("/E1/", $board['model'])) {
                for($i = 0; $i < $board['links']; $i++)
                    $khomp_boards["b" . $board['id'] . "l$i"] = $board['model']. " - Link $i";
            }
            else {
                for($i = 0; $i < $board['channels']; $i++)
                    $khomp_boards["b" . $board['id'] . "c$i"] = $board['model']. " - Canal $i";
            }
        }
    }
}

// Tecnologias para Troncos IP
// ---------------------------
$technos_ip = array("IAX2"=>"IAX2", "SIP"=>"SIP");

// Variaveis de ambiente do form
// -----------------------------
$smarty->assign('khomp_boards',$khomp_boards) ;
$smarty->assign('ACAO',$acao) ;
$smarty->assign('PROTOTYPE',true);
$smarty->assign('OPCOES_DTMF',$tipos_dtmf) ;
$smarty->assign('OPCOES_CODECS',$tipos_codecs) ;
$smarty->assign('TRUNKS_DISP',$trunks_disp) ;
$smarty->assign('TECHNOS',$technos_ip);

if ($acao == "cadastrar") {
    cadastrar();
} elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_register']." » ".$LANG['menu_troncos']." » ".$LANG['change'];
    alterar() ;
} elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
} elseif ($acao ==  "excluir") {
    excluir() ;
} elseif ($acao ==  "pesquisar") {
    pesquisa_canal() ;
} else {
    $titulo = $LANG['menu_register']." » ".$LANG['menu_troncos']." » ".$LANG['include'];
    principal() ;
}

/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
 ------------------------------------------------------------------------------*/
function principal() {
    global $db,$smarty,$titulo,$codecs_default,$SETUP ;
    // Sugestao de numero proximo Tronco
    // ---------------------------------
    try {
        $sql = "SELECT name FROM trunks " ;
        $sql.= " ORDER BY CAST(name as DECIMAL) DESC LIMIT 1" ;
        $row = $db->query($sql)->fetch();
    } catch (PDOException $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }
    $row['name'] = trim($row['name'] + 1) ;
    $row['trunktype'] = 'SIP';
    $row['nat'] = true;
    $row['reverseAuth'] = true;

    $time['time'] =  "n";
    // Codecs Default
    // --------------
    $row = $row + $codecs_default ;

    // Variavies do Template
    // ---------------------
    $smarty->assign('dt_troncos',$row) ;

    $smarty->assign('dt_troncos_tempos',$time) ;
    $smarty->assign('ACAO',"cadastrar") ;
    $smarty->assign('PROTOTYPE',true) ;
    display_template("troncos.tpl",$smarty,$titulo) ;
} 
/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar() {
    global $LANG, $db, $dtmf_dial, $extensionMapping, $name, $snep_host, $fromdomain, $fromuser, $khomp_board, $id_regex, $trunktype, $callerid, $username, $secret,
    $insecure, $cod1, $cod2, $cod3, $cod4, $cod5, $dtmfmode, $channel, $host_trunk, $trunk_redund, $def_campos_troncos, $time_total, $time_chargeby, $tempo, $dialmethod;
    global $nat, $snep_cod1, $dtmf_dial_number, $snep_cod2, $snep_cod3, $snep_cod4, $snep_cod5, $snep_dtmf, $snep_username, $reverseAuth, $qualify, $qualify_time;

    if($trunktype == "SNEPSIP" || $trunktype == "SNEPIAX2") {
        $cod1 = $snep_cod1;
        $cod2 = $snep_cod2;
        $cod3 = $snep_cod3;
        $cod4 = $snep_cod4;
        $cod5 = $snep_cod5;
        $nat = "no";
    }

    // verifica tipo de Qualify, (yes|no|specify)
    if($qualify == 'specify') {
        $qualify = trim($qualify_time);
    }

    // monta a cadeia de codecs permitidos
    $allow="" ;
    $allow .= (strlen(trim($cod1))>0) ? $cod1 : "" ;
    $allow .= (strlen(trim($cod2))>0) ? ";$cod2" : ";" ;
    $allow .= (strlen(trim($cod3))>0) ? ";$cod3" : ";" ;
    $allow .= (strlen(trim($cod4))>0) ? ";$cod4" : ";" ;
    $allow .= (strlen(trim($cod5))>0) ? ";$cod5" : ";" ;

    if ($tempo == "s") {
        $time_chargeby = $time_total > 0? "'$time_chargeby'": "NULL";
        $time_total = $time_total*60;
        $time_total = $time_total == 0? "NULL": "'$time_total'";
    } else {
        $time_chargeby = "NULL";
        $time_total = "NULL";
    }

    try {
        $sql = "SELECT name FROM trunks " ;
        $sql.= " ORDER BY CAST(name as DECIMAL) DESC LIMIT 1" ;
        $row = $db->query($sql)->fetch();
    } catch (PDOException $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }
    $name = trim($row['name'] + 1) ;

    $trunk_redund = $trunk_redund == "" ? 'NULL': $trunk_redund;
    $type = $trunktype;

    // Monta a cadeia de canais
    if ($trunktype == "SIP" || $trunktype == "IAX2") {
        if($dialmethod == 'NOAUTH') {
            $host_trunk = $_POST['host'];
            $channel = $trunktype . "/@" . $host_trunk;
        }
        else {
            $channel= $trunktype . "/" . $username;
        }

        $id_regex = $trunktype . "/" . $username;

        $sql_fields_default = "";
        $sql_values_default = "";
        if($fromdomain != "") {
            $sql_fields_default = ",fromdomain";
            $sql_values_default = ",'$fromdomain'";
        }
        if($fromuser != "") {
            $sql_fields_default = ",fromuser";
            $sql_values_default = ",'$fromuser'";
        }

        if($nat) {
            $nat = 'yes';
        }
        else {
            $nat = 'no';
        }
        
        // Monta lista campos Default
        foreach( $def_campos_troncos as $key => $value ) {
            $sql_fields_default .= ",$key";
            $sql_values_default .= ",$value";
        }
        $trunktype = "I";
    }
    else if( $trunktype == "SNEPSIP" ) {
        $trunktype  = 'SIP';
        $username   = $snep_host;
        $host_trunk = $snep_host;
        $channel    = $trunktype . "/" . $snep_host;
        $id_regex   = $trunktype . "/" . $snep_host;

        $dtmfmode = $snep_dtmf;

        $trunktype  = "I";
    }
    else if( $trunktype == "SNEPIAX2" ) {
        $trunktype  = 'IAX2';
        $username   = $snep_username;
        $host_trunk = $snep_host;
        $channel    = $trunktype . "/" . $snep_username;
        $id_regex   = $trunktype . "/" . $snep_username;

        $dtmfmode = $snep_dtmf;

        $trunktype  = "I";
    }
    else if($trunktype == "KHOMP") {
        $channel= 'KHOMP/' . $khomp_board;
        $b = substr($khomp_board, 1, 1);
        if(substr($khomp_board, 2, 1) == 'c') {
            $config = array(
                    "board" => $b,
                    "channel" => substr($khomp_board, 3)
            );
        }
        else if( substr($khomp_board, 2, 1) == 'l' ) {
            $config = array(
                    "board" => $b,
                    "link" => substr($khomp_board, 3)
            );
        }
        else {
            $config = array(
                    "board" => $b
            );
        }
        $trunk = new PBX_Asterisk_Interface_KHOMP($config);
        $id_regex = $trunk->getIncomingChannel();
        $trunktype = "T";
    }
    else { // VIRTUAL
        $trunktype = "T";
        $id_regex = $id_regex == "" ? $channel : $id_regex;
    }

    $dtmf_dial = $dtmf_dial ? 'TRUE' : 'FALSE';

    $context = "default";

    $extensionMapping = $extensionMapping ? 'True': 'False';
    $reverseAuth = $reverseAuth ? "True": "False";

    try {
        $db->beginTransaction() ;
        $sql = "INSERT INTO trunks (" ;
        $sql.= "name, type, callerid, context, dtmfmode, insecure, secret,id_regex,";
        $sql.= "username, allow, channel, trunktype, host, trunk_redund, time_total,";
        $sql.= "time_chargeby, dialmethod, map_extensions, reverse_auth, dtmf_dial, dtmf_dial_number) values (";
        $sql.= "'$name','$type','$callerid','$context','$dtmfmode','$insecure',";
        $sql.= "'$secret','$id_regex','$username','$allow','$channel','$trunktype'," ;
        $sql.= "'$host_trunk',$trunk_redund, $time_total, $time_chargeby, '$dialmethod',";
        $sql.= "$extensionMapping, $reverseAuth, $dtmf_dial, '$dtmf_dial_number')" ;
        $db->exec($sql) ;
        // Se for tronco IP, Cadastra tabela peers
        if ($trunktype == "I") {
            $sql = "INSERT INTO peers (" ;
            $sql.= "name,callerid,context,secret,type,allow,username,";
            $sql.= "dtmfmode,canal,host,peer_type, trunk, qualify, nat ".$sql_fields_default ;
            $sql.= ") values (";
            $sql.=  "'$name','$callerid','$context','$secret','peer','$allow',";
            $sql.= "'$username','$dtmfmode','$channel','$host_trunk', 'T', 'yes', '$qualify', '$nat' ";
            $sql.= $sql_values_default.")" ;
            $db->exec($sql) ;
        }
        $db->commit();
    } catch (Exception $ex) {
        $db->rollBack();
        display_error($LANG['error'].$ex->getMessage().$sql,true) ;
    }
    grava_conf();// Mantenha após o commit
    echo "<meta http-equiv='refresh' content='0;url=../index.php/trunks'>\n" ;
}

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Altera um registro
------------------------------------------------------------------------------*/
function alterar() {
    global $LANG,$db,$smarty,$titulo, $acao, $canais_disp, $trunks_disp ;
    $id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
    if (!$id) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
    }

    try {
        $sql = "SELECT * FROM trunks WHERE id=$id";
        $trunk = $db->query($sql)->fetch();
    } catch (PDOException $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }

    try {
        $sql = "SELECT * FROM peers WHERE name='{$trunk['name']}'";
        $peer = $db->query($sql)->fetch();
    } catch (PDOException $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }

    $trunk['fromdomain'] = $peer['fromdomain'];
    $trunk['fromuser'] = $peer['fromuser'];

    // Variavel host
    $trunk['host_trunk'] = $trunk['host'] ;

    $trunk['time'] = isset($trunk['time_total'])? "s" : "n";
    $trunk['time_total'] = round($trunk['time_total']/60);

    // Desmembra campo allow
    $cd = explode(";",$trunk['allow']);
    $trunk['cod1']=$cd[0] ;
    $trunk['cod2']=$cd[1] ;
    $trunk['cod3']=$cd[2] ;
    $trunk['cod4']=$cd[3] ;
    $trunk['cod5']=$cd[4] ;

    $trunk['techno'] = substr($trunk['channel'],0,strrpos($trunk['channel'],"/"));

    $trunk['trunktype'] = $trunk['type'];
    $trunk['extensionMapping'] = $trunk['map_extensions'] ? true : false;
    $trunk['reverseAuth'] = $trunk['reverse_auth'] ? true : false;

    if($trunk['type'] == 'KHOMP') {
        $trunk['khomp_board'] = substr($trunk['channel'],strrpos($trunk['channel'],"/")+1);
    }

    // Faz uma verificação e instancia uma variavel de controle do Smarty
    if($peer['qualify'] == "no" ||$peer['qualify'] == "yes") {
        $smarty->assign('qualify', 's');
    } else {
        $smarty->assign('qualify', 'e');
    }
    $trunk['qualify'] = $peer['qualify'];

    $trunk['nat'] = $peer['nat'] == "yes" ? true : false;

    // Retira o tronco atual da lista de troncos para redundancia
    unset($trunks_disp[$id]) ;

    // Variaveis do template
    $smarty->assign ('dt_troncos',$trunk);
    $smarty->assign('ACAO',"grava_alterar") ;
    $smarty->assign('TRUNKS_DISP',$trunks_disp) ;
    display_template("troncos.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar() {
    global $LANG, $db, $extensionMapping, $snep_host, $name, $fromdomain, $fromuser, $trunktype, $callerid, $username, $secret, $insecure, $cod1, $cod2, $cod3, $cod4, $cod5, $dtmfmode, $channel, $host_trunk, $trunk_redund, $techno, $time_total, $time_chargeby, $tempo, $dialmethod;
    global $nat, $dtmf_dial_number, $snep_cod1, $dtmf_dial, $snep_cod2, $snep_cod3, $snep_cod4, $snep_cod5, $snep_dtmf, $snep_username,$khomp_board, $reverseAuth, $qualify, $qualify_time;


    if($trunktype == "SNEPSIP" || $trunktype == "SNEPIAX2") {
        $cod1 = $snep_cod1;
        $cod2 = $snep_cod2;
        $cod3 = $snep_cod3;
        $cod4 = $snep_cod4;
        $cod5 = $snep_cod5;
        $nat = "no";
    }

    if (!$_POST['id']) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
    }

    // verifica tipo de Qualify, (yes|no|specify)
    if($qualify == 'specify') {
        $qualify = $qualify_time;
    }

    // monta a cadeia de codecs permitidos
    $allow="" ;
    $allow .= (strlen(trim($cod1))>0) ? $cod1 : "" ;
    $allow .= (strlen(trim($cod2))>0) ? ";$cod2" : ";" ;
    $allow .= (strlen(trim($cod3))>0) ? ";$cod3" : ";" ;
    $allow .= (strlen(trim($cod4))>0) ? ";$cod4" : ";" ;
    $allow .= (strlen(trim($cod5))>0) ? ";$cod5" : ";" ;

    $trunk_redund = $trunk_redund == "" ? 'NULL': $trunk_redund;
    $type = $trunktype;

    // Monta a cadeia de canais
    if ($trunktype == "SIP" || $trunktype == "IAX2") {
        if($dialmethod == 'NOAUTH') {
            $host_trunk = $_POST['host'];
            $channel = $trunktype . "/@" . $host_trunk;
        }
        else {
            $channel= $trunktype . "/" . $username;
        }
        $id_regex = $trunktype . "/" . $username;
        $sql_fields_default = ",qualify, type";
        $sql_values_default = ",'yes', 'peer'";
        
        if($nat) {
            $nat = 'yes';
        }
        else {
            $nat = 'no';
        }

        // Monta lista campos Default
        $def_campos_troncos = isset($def_campos_troncos) ? $def_campos_troncos : array();
        foreach( $def_campos_troncos as $key => $value ) {
            $sql_fields_default .= ",$key";
            $sql_values_default .= ",$value";
        }
        $trunktype = "I";
    }
    else if( $trunktype == "SNEPSIP" ) {
        $trunktype  = 'SIP';
        $username   = $snep_host;
        $host_trunk = $snep_host;
        $channel    = $trunktype . "/" . $snep_host;
        $id_regex   = $trunktype . "/" . $snep_host;

        $dtmfmode = $snep_dtmf;

        $trunktype  = "I";
    }
    else if( $trunktype == "SNEPIAX2" ) {
        $trunktype  = 'IAX2';
        $username   = $snep_username;
        $host_trunk = $snep_host;
        $channel    = $trunktype . "/" . $snep_username;
        $id_regex   = $trunktype . "/" . $snep_username;

        $dtmfmode = $snep_dtmf;

        $trunktype  = "I";
    }
    else if($trunktype == "KHOMP") {
        $channel= 'KHOMP/' . $khomp_board;
        $b = substr($khomp_board, 1, 1);
        if(substr($khomp_board, 2, 1) == 'c') {
            $config = array(
                    "board" => $b,
                    "channel" => substr($khomp_board, 3)
            );
        }
        else if( substr($khomp_board, 2, 1) == 'l' ) {
            $config = array(
                    "board" => $b,
                    "link" => substr($khomp_board, 3)
            );
        }
        else {
            $config = array(
                    "board" => $b
            );
        }
        $trunk = new PBX_Asterisk_Interface_KHOMP($config);
        $id_regex = $trunk->getIncomingChannel();
        $trunktype = "T";
    }
    else { // VIRTUAL
        $trunktype = "T";
        $id_regex = $id_regex == "" ? $channel : $id_regex;
    }

    if ($tempo === "n") {
        $time_chargeby = "NULL";
        $time_total = "NULL";
    } else {
        $time_chargeby = $time_total != ""? $time_chargeby: "NULL";
        $time_total = $time_total*60;
    }

    $dtmf_dial = $dtmf_dial ? 'TRUE' : 'FALSE';

    $context = "default";

    $extensionMapping = $extensionMapping ? "True" : "False";
    $reverseAuth = $reverseAuth ? "True": "False";

    try {
        $db->beginTransaction() ;
        $sql = "UPDATE trunks SET ";
        $sql.= "callerid='$callerid',secret='$secret',type='$type',";
        $sql.= "host='$host_trunk',context='$context',insecure='$insecure',";
        $sql.= "allow='$allow',dtmfmode='$dtmfmode',channel='$channel', dtmf_dial = $dtmf_dial,";
        $sql.= "username='$username',trunk_redund=$trunk_redund,map_extensions=$extensionMapping,reverse_auth=$reverseAuth,";
        $sql.= "time_total=$time_total, dtmf_dial_number='$dtmf_dial_number', time_chargeby='$time_chargeby', dialmethod='$dialmethod', id_regex='$id_regex'";
        $sql.= "  WHERE name=$name" ;
        $db->exec($sql) ;
        if ($trunktype == "I") {
            $sql = "UPDATE peers ";
            $sql.=" SET fromdomain='$fromdomain', fromuser='$fromuser' ,callerid='$callerid', context='$context',secret='$secret',";
            $sql.= "type='peer', nat='$nat', allow='$allow',host='$host_trunk'," ;
            $sql.= "username='$username',dtmfmode='$dtmfmode',canal='$channel',qualify='$qualify'" ;
            $sql.= " WHERE name='$name'" ;
            $db->exec($sql) ;
        }
        $db->commit();
    } catch (Exception $ex) {
        $db->rollBack();
        display_error($LANG['error'].$ex->getMessage(),true) ;
    }
    grava_conf(); // Mantenha após o commit.
    echo "<meta http-equiv='refresh' content='0;url=../index.php/trunks'>\n" ;
}

/*------------------------------------------------------------------------------
 * Funcao EXCLUIR - Excluir registro selecionado da Tabela RAMAIS
 *                  Excluir registro correspondente da tabela voicemail_users
 *                  Excluir registro correspondente da tabela vinculos
------------------------------------------------------------------------------*/
function excluir() {
    global $LANG, $db;
    $id = $_GET['id'];
    $name = $_GET['name'];

    if (!$id) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
    }
    try {
        // Procurando por conflito com regras de negócio
        $rules_query = "SELECT id, `desc` FROM regras_negocio WHERE origem LIKE '%T:$id%' OR destino LIKE '%T:$id%'";
        $regras = $db->query($rules_query)->fetchAll();

        $rules_query = "SELECT rule.id, rule.desc FROM regras_negocio as rule, regras_negocio_actions_config as rconf WHERE (rconf.regra_id = rule.id AND rconf.value like '%$id%' AND (rconf.key = 'tronco' OR rconf.key = 'trunk'))";
        $regras = array_merge($regras, $db->query($rules_query)->fetchAll());

        if(count($regras) > 0) {
            $msg = $LANG['extension_conflict_in_rules'].":<br />\n";
            foreach ($regras as $regra) {
                $msg .= $regra['id'] . " - " . $regra['desc'] . "<br />\n";
            }
            display_error($msg,true);
            exit(1);
        }

        $sql = "DELETE FROM trunks WHERE id=$id" ;
        $db->beginTransaction() ;
        $db->exec($sql);
        $sql = "DELETE FROM peers WHERE name='$name'" ;
        $db->exec($sql);
        $db->commit();
        grava_conf();


        echo "<meta http-equiv='refresh' content='0;url=../index.php/trunks'>\n" ;
    } catch (PDOException $ex) {
        $db->rollBack();
        display_error($LANG['error'].$ex->getMessage(),true) ;
    }
}
