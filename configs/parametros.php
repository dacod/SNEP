<?php
/* ----------------------------------------------------------------------------
 * Programa: parametros.php - Altera arquivo de parametros do sistema
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php");
 ver_permissao(61);
 $titulo = $LANG['menu_config']." -> ".$LANG['params'];
 if (array_key_exists ('parametros', $_POST)) {
    gravar() ;
 }
 $config = Zend_Registry::get('config');
 $smarty->assign('OPCOES_TF',$tipos_tf) ;
 $smarty->assign('OPCOES_TIME',$tipos_time) ;
 $smarty->assign('OPCOES_CONF',$tipos_conference) ;
 $smarty->assign('record_app',strtolower($config->general->record->application));
 $smarty->assign('record_flags',$config->general->record->flags);
 
 display_template("parametros.tpl",$smarty,$titulo) ;
    
/*------------------------------------------------------------------------------
 Funcao gravar - Converte e grava o arquivo
 ------------------------------------------------------------------------------*/
function gravar()  {
   global $db, $LANG; 
   $config_file = Zend_Registry::get('configFile');
   $config_tmp = "/tmp/setup.conf"; 
   $erro = FALSE ;
   if (count($_POST['alterar']) > 0 ) {
      foreach ( $_POST["alterar"] as $chave => $option ) {
         $value = str_replace(",","\,", $_POST["new_$option"] );
         $option_sed = $option == "record_flags"? "record\.flags" : $option;
         $option_sed = $option == "record_app"? "record\.application" : $option;

         // Pesquisa e faz a troca do valor da variavel - salva em arquivo temporario
         $comando = 'sed "s,^'.$option_sed.'.*=.*,'.$option_sed.' = \"'.$value.'\"'.'", < "'.$config_file.'" > "'.$config_tmp.'"';
         if (executacmd($comando,$LANG['msg_err_sed'])) {
            // Ajusta permissoes do arquivo temporario
            $comando = 'mv '.$config_tmp.' '.$config_file ;
            if (executacmd($comando,$LANG['msg_err_move'])) {
               $erro = TRUE ;
            }
         }
      } // Fim do foreach
      if (!$erro)
         display_error($LANG['msg_ok_config'],true) ;
   } // Fim do If
  //echo "<meta http-equiv='refresh' content='0;url=../configs/parametros.php'>\n" ;
}
?>