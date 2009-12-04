<?php
/*-----------------------------------------------------------------------------
 * Programa: ajuda.php - Exibe tela de Ajuda sensivel ao Contexto
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Henrique Grolli Bassotto <henrique@opens.com.br>
 *        Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php"); 
// Parsing the received variable
$script = isset($_GET['script']) ? mysql_escape_string($_GET['script']) : "default";
// Passando o nome do texto a ser exibido
$script = basename($script, ".php").".html";
if(file_exists("../doc/manual/$script"))
    $smarty->assign('texto',$script);
else {
    $smarty->assign('texto','index.html');
    $smarty->assign('aviso',$LANG['warning_doc'].$script );
}
display_template("ajuda.tpl",$smarty) ;


?>