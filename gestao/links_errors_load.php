<?php
 /* ---------------------------------------------------------------------------
 * Programa: links_errors_load.php - Passa parametros p/sistema mostrar erros links
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");
 require_once("../configs/config.php");
 ver_permissao(105);

 $titulo = $LANG['menu_links_erros'] ;
 $smarty->assign ('REFRESH',array('mostrar'=> True,
                                  'tempo'  => $SETUP['ambiente']['tempo_refresh'],
                                  'url'    => "../gestao/links_errors.php"));
 $titulo = $LANG['menu_links_erros'];
 display_template("cabecalho.tpl",$smarty,$titulo) ;

?>
