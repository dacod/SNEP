<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_musiconhold.php - Manipula as Secoes de Musica em Espera
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 ver_permissao(53) ;
 unset($_SESSION['secao']) ;
 $titulo = $LANG['menu_config']." -> ".$LANG['menu_musiconhold'].": ".$LANG['sections'] ;
  if (array_key_exists ('musiconhold', $_POST)) {
    gravar() ;
 } 

 // Faz Leitura do Arquivo snep-musiconhold.conf
 $row = executacmd("cat /etc/asterisk/snep/snep-musiconhold.conf","",True) ;
 $secoes = array() ;
 $secao =  "" ;
 foreach($row as $key => $value){
    if ( (substr($value,0,1) === ";" &&
          substr($value,1,4) != "SNEP") ||
          substr($value,0,1) == "[" ||
          strlen(trim($value)) == 0 )        
       continue ;     
    if (substr($value,0,5) == ';SNEP') {
       $secao=substr($value,6) ;
       $secao=substr($secao,0,strpos($secao,")"));
       $secoes[$secao]['name'] = $secao ;
       $secoes[$secao]['desc'] = substr($value,strpos($value,"=")+1) ;
       continue ;
    }
    $ind=substr($value,0,strpos($value,"=")) ;
    $secoes[$secao][$ind] = substr($value,strpos($value,"=")+1) ;
 }
 $_SESSION['secoes'] = $secoes ;
 // Define variaveis do template
 $smarty->assign ('DADOS',$secoes);
 $smarty->assign ('MUSIC_MODES',$musiconhold_modes);
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',False) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('array_include_buttom',array("url" => "../gestao/musiconhold.php", "display"  => $LANG['include']." ".$LANG['sections']));
 display_template("rel_musiconhold.tpl",$smarty,$titulo);
 ?>