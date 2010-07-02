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
 ver_permissao(17) ;
 
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
    foreach ($row_grp as $val) 
       $grupos[$val['cod_grupo']] = $val['nome'] ;
    asort($grupos) ;  
 }


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

$canais = array(
    "SIP" => "SIP",
    "IAX2" => "IAX2",
    "KHOMP" => "Khomp",
    "VIRTUAL" => "Virtual",
);

/* ----------------------------------------------------------------- */
/* Lista de troncos */
/* ----------------------------------------------------------------- */
$trunks = array();
foreach (PBX_Trunks::getAll() as $tronco) {
    $trunks[$tronco->getId()] = $tronco->getId() . " - " . $tronco->getName();

}
$smarty->assign('TRUNKS', $trunks);


$khompInfo = new PBX_Khomp_Info();


$fxs_list = array();
if( $khompInfo->hasWorkingBoards() ) {
    $khomp_boards = $khompInfo->boardInfo();
    foreach ($khomp_boards as $board) {
        if( preg_match("/FXS/", $board['model']) ) {
            $fxs_list[] = $board;
        }
    }
}

$smarty->assign('FXSS',$fxs_list);
   
 // Variaveis de ambiente do form
 $smarty->assign('ACAO',$acao);
 $smarty->assign('PROTOTYPE',true);
 $smarty->assign('OPCOES_YN',$tipos_yn) ;
 $smarty->assign('OPCOES_DTMF',$tipos_dtmf) ;
 $smarty->assign('OPCOES_CODECS',$tipos_codecs) ;
 $smarty->assign('OPCOES_GRUPOS',$grupos);
 $smarty->assign('OPCOES_CANAL',$canais) ;
 $smarty->assign('OPCOES_USERGROUPS',$user_groups);
 
 if ($acao == "cadastrar") {
    cadastrar();
 } else {
   $titulo = $LANG['menu_register']." -> ".$LANG['menu_ramais']." -> ".$LANG['include']." ".$LANG['various'];
   principal() ;
 }
 
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
 ------------------------------------------------------------------------------*/
 function principal()  {
   global $db,$smarty,$titulo,$codecs_default,$SETUP ;
   // Codecs padrao
   $row = array() ;
   $row = $row + $codecs_default ;

   // Authenticate
   $row['usa_auth'] = "No" ;

   $row['group'] = "users";
   
   // Variavies do Template
   $smarty->assign('dt_ramais',$row) ;
   $smarty->assign('ACAO',"cadastrar") ;
   display_template("ramais_varios.tpl",$smarty,$titulo) ;
} 
/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
 /**
  * Insere extensão no banco de dados.
  *
  * @param int $number numero do ramal
  * @param array $khompChannels lista de canais khomp para inserção em fxs
  */
function insert_exten($number, &$khompChannels) {
    global $SETUP, $LANG, $db;
    $system_range = explode("-", $SETUP['canais']['peers_range']);
    if( $number < (int) $system_range[0] || $number > (int) $system_range[1] ) {
        throw new Exception($LANG['msg_channeloutinterval']);
    }
    else {
        $pickupGroup = $_POST['pickupgroup'] != "" ? $_POST['pickupgroup'] : null;
        $group       = $_POST['group'];
        
        $options = array(
            // Opções gerais para qualquer tecnologia.
            "name" => $number,
            "username" => $number,
            "fromuser" => $number,
            "mailbox" => $number,
            "secret" => "$number$number",
            "callerid" => "Ramal $number <$number>",
            "context" => "default",
            "type" => "peer",
            "peer_type" => "R",
            "authenticate" => 0,
            "group" => $group,
            "pickupgroup" => $pickupGroup,
            "host" => "dynamic",
            "disallow" => "all",
            // Opções padrões para tecnologias não IP
            "nat" => "no",
            "qualify" => "no",
            "dtmfmode" => "rfc2833",
            "allow" => "ulaw;alaw;gsm;g729;"
        );

        $tech = $_POST['tech'];

        if( $tech == 'VIRTUAL' ) {
            $trunk = $_POST['trunk'];
            $options["canal"] = "VIRTUAL/$trunk";
        }
        else if( $tech == 'KHOMP' ) {
            $options['canal'] = array_pop($khompChannels);
        }
        else {
            $options['canal'] = "$tech/$number";
            
            if(isset($_POST['nat'])) {
                $options["nat"] = $_POST['nat'];
            }
            if(isset($_POST['qualify'])) {
                $options["qualify"] = $_POST['qualify'];
            }
            if(isset($_POST['dtmfmode'])) {
                $options["dtmfmode"] = $_POST['dtmfmode'];
            }

            $allow="" ;
            $allow .= (strlen(trim($_POST['cod1']))>0) ? $_POST['cod1'] : "" ;
            $allow .= (strlen(trim($_POST['cod2']))>0) ? ";{$_POST['cod2']}" : ";" ;
            $allow .= (strlen(trim($_POST['cod3']))>0) ? ";{$_POST['cod3']}" : ";" ;
            $allow .= (strlen(trim($_POST['cod4']))>0) ? ";{$_POST['cod4']}" : ";" ;
            $allow .= (strlen(trim($_POST['cod5']))>0) ? ";{$_POST['cod5']}" : ";" ;

            $options['allow'] = $allow;
        }


        $fields = "";
        $values = "";
        foreach ($options as $field => $value) {
            $fields .= ",`$field`";
            if($value === null) {
                $values .= ",NULL";
            }
            else {
                $values .= ",'$value'";
            }
        }

        $fields = trim($fields, ',');
        $values = trim($values, ',');

        $sql = "INSERT INTO peers($fields) VALUES($values);";

        $db->exec($sql);



    }
}

function cadastrar()  {
    global $SETUP, $LANG, $db;

    // Contando ramais para serem incluídos.
    $range = explode(";", $_POST['extensions_range']);

    $khompInfo = new PBX_Khomp_Info();
    $khompChannels = array();
    
    if($_POST['tech'] == "KHOMP") {
        foreach ($_POST['fxs'] as $id => $trash) {
            $boardInfo = $khompInfo->boardInfo($id);
            for( $i = 0; $i < $boardInfo['channels']; $i++ ) {
                $khompChannels[] = "KHOMP/b{$boardInfo['id']}c$i";
            }
        }

        // Gerando lista de interfaces khomp disponíveis para associação de ramais.
        $sql = "SELECT `canal` FROM peers WHERE `canal` like 'KHOMP%'";
        $khompPeers = $db->query($sql)->fetchAll();

        foreach ($khompPeers as $khompPeer) {
            if( in_array($khompPeer['canal'], $khompChannels) ) {
                unset($khompChannels[array_search($khompPeer['canal'], $khompChannels)]);
            }
        }

        $khompChannels = array_reverse($khompChannels);
    }

    $db->beginTransaction();
    try {
        foreach ($range as $number) {
            if( is_numeric($number)) {
                try {
                    PBX_Usuarios::get($number);
                }
                catch( PBX_Exception_NotFound $ex ) {
                    insert_exten($number, $khompChannels);
                }

                Snep_Vinculos::setVinculos($number, 'R', $number);
            }
            else {
                $number = explode("-",$number);
                foreach (range($number[0], $number[1]) as $number) {
                    try {
                        PBX_Usuarios::get($number);
                    }
                    catch( PBX_Exception_NotFound $ex ) {
                        insert_exten($number, $khompChannels);
                    }
                    
                    Snep_Vinculos::setVinculos($number, 'R', $number);
                }
            }
        }
        $db->commit();
    }
    catch(Exception $ex) {
        $db->rollBack();
        display_error($ex->getMessage(), true);
        exit();
    }


    // Gravando mudanças no arquivo e recarregando configurações no asterisk.
    grava_conf();
    ast_status("sip reload","");
    ast_status("iax2 reload","");

    echo "<meta http-equiv='refresh' content='0;url=../src/extensions.php'>\n";
}
