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

ver_permissao(66);

$acao = isset($_GET['acao']) ? $_GET['acao'] : "";
$ta = isset($_GET['t']) ? $_GET['t'] : "" ;
if ($ta == "" & $acao == "") {
   display_error($LANG['msg_nofiledial'],true) ;
   exit();
}
if ( $acao == "") {
   switch($ta) {
      case "u" :
         $conf_file = '/etc/asterisk/snep/snep-ura.conf';
         $titulo = $LANG['menu_rules']." -> ".$LANG['menu_ura'];
         break ;
      case "r" :
         $conf_file = '/etc/asterisk/snep/snep-ramais.conf';
         $titulo = $LANG['menu_rules']." -> ".$LANG['menu_ramais_conf'];
         break ;
      case "i" :
         $conf_file = '/etc/asterisk/snep/snep-incoming.conf';
         $titulo = $LANG['menu_rules']." -> ".$LANG['menu_incoming'];
         break ;
      case "s" ;
         $conf_file = '/etc/asterisk/snep/snep-sip.conf';
         $titulo = $LANG['menu_rules']." -> ".$LANG['menu_sip_conf'];
      default :
         display_error($LANG['msg_nofiledial'],true) ;
         exit();
   }
}
if ($acao ==  "salvar") {
  salvar($_POST['text']);
} else {
  principal();
}

/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
    global $smarty, $LANG, $conf_file, $titulo;
    if(!is_writable($conf_file)){
        display_error($LANG['msg_incoming_file_error'] . $conf_file, true);
        exit();
    }
    
    try {
        $file_content = file_get_contents($conf_file, 'FILE_TEXT');
    }
    catch(Exception $e){
        display_error($LANG['error'] . $e, true);
        exit();
    }

    $smarty->assign('CONF_CONTENT', $file_content);
    $smarty->assign('SUBMIT_FILE', 'incoming.php');
    $smarty->assign('CONF_FILE', $conf_file);
    display_template("conf_editor.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
 Funcao Salvar - Salva o texto enviado no arquivo
------------------------------------------------------------------------------*/
function salvar($text) {
    global $smarty, $LANG, $conf_file;
    if(!is_writable($conf_file)) {
        display_error($LANG['msg_incoming_file_error'] . $conf_file, true);
        exit();
    }    
    try {
        $file_handler = fopen($conf_file, 'w');
        fwrite($file_handler, $text);
        fclose($file_handler);
    }
    catch(Exception $e){
        display_error($LANG['error'] . $e, true);
        exit();
    }
    ast_status("dialplan reload", "" ) ;
    display_error($LANG['file_saved'], true);
}

?>
