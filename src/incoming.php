<?php
/*-----------------------------------------------------------------------------
 * Programa: incoming.php - Edita extensao para entrada de ligações
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Henrique Grolli Bassotto <henrique@opens.com.br>
 * Obs: Recebe um param t (tipo) que identifica qual arquivo a ser manipulado
 *      Tipos: i=snep-incoming.conf, r=snep-ramais.conf, u=snep-ura.conf, s=snep-sip.conf
 *-----------------------------------------------------------------------------*/
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
