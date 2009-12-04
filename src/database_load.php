<?php
 /* ---------------------------------------------------------------------------
 * Programa: database_load.php - Monitoramento de Ramais, Filas e agentes.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php");

/*-----------------------------------------------------------------------------
 * Funcao visualizar - Exibe relacao de filas
 *-----------------------------------------------------------------------------*/
  
  global $smarty, $SETUP, $filas_selec, $LANG, $view_ps;

  $titulo = $LANG['menu_status']." -> ".$LANG['menu_databaseshow']." -> ".$LANG['view'];
  $smarty->assign ('REFRESH',array('mostrar'=> True,
                                  'tempo'  => 10,
                                  'url'    => "../src/database_show.php"));
  display_template("cabecalho.tpl",$smarty,$titulo) ;
  exit ;
 
?>
