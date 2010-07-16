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
 ver_permissao(61);
 $titulo = $LANG['menu_config']." » ".$LANG['params'];
 if (array_key_exists ('parametros', $_POST)) {
    gravar() ;
 }
 $config = Zend_Registry::get('config');

 $smarty->assign( 'OPCOES_TF',      $tipos_tf) ;
 $smarty->assign( 'OPCOES_TIME',    $tipos_time) ;
 $smarty->assign( 'OPCOES_CONF',    $tipos_conference) ;
 $smarty->assign( 'record_mp3',     $config->system->record_mp3);
 $smarty->assign( 'record_flags',   $config->general->record->flags);
 $smarty->assign( 'record_app',     strtolower($config->general->record->application));
 display_template("parametros.tpl", $smarty,$titulo) ;
    
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
         $option_sed = $option;

         if($option == "record_flags") {
            $option_sed =  "record\.flags";
         }

         if($option == "record_app") {
            $option_sed = "record\.application";
         }
         

         // Pesquisa e faz a troca do valor da variavel - salva em arquivo temporario
         if($option == "debug") {
            $comando = "sed \"s,^$option_sed.*=.*,$option_sed = $value\", < \"$config_file\" > \"$config_tmp\"";

         }
         elseif($option == "record_mp3") {             
            if($value == 'true') {
                $comando = "sed \"s,^$option_sed.*=.*,$option_sed=true\", < \"$config_file\" > \"$config_tmp\"";
            }else{
                $comando = "sed \"s,^$option_sed.*=.*,$option_sed=false\", < \"$config_file\" > \"$config_tmp\"";
            }
         }elseif($option == "record_compact") {
            if($value == 'true') {
                $comando = "sed \"s,^$option_sed.*=.*,$option_sed=true\", < \"$config_file\" > \"$config_tmp\"";
            }else{
                $comando = "sed \"s,^$option_sed.*=.*,$option_sed=false\", < \"$config_file\" > \"$config_tmp\"";
            }
         }

         else {
            $comando = 'sed "s,^'.$option_sed.'.*=.*,'.$option_sed.' = \"'.$value.'\"'.'", < "'.$config_file.'" > "'.$config_tmp.'"';
         }


         if (executacmd($comando,$LANG['msg_err_sed'])) {
            // Ajusta permissoes do arquivo temporario
            $comando = 'mv '. $config_tmp .' '. $config_file ;
      
            if (executacmd($comando,$LANG['msg_err_move'])) {
               $erro = TRUE ;
            }
      
         }

      } // Fim do foreach

      if (!$erro) {
          display_error($LANG['msg_ok_config'],true) ;
      }
         
   } // Fim do If
  echo "<meta http-equiv='refresh' content='0;url=../configs/parametros.php'>\n" ;
}
