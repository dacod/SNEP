<?php
/*----------------------------------------------------------------------------
 * Programa: compacta_arquivos.php - Compacta arquivos em arquivos/backup
 *
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Rafael Bozzetti <rafael@opens.com.br>
 *---------------------------------------------------------------------------*/
 require_once("../configs/config.php");
$save_dir = $SETUP['ambiente']['path_voz_bkp'];
if(strlen($_REQUEST['arquivos']) > 1) {
    $strArquivos = substr($_REQUEST['arquivos'],0,strlen($_REQUEST['arquivos'])-1);
    $strListaArquivos = str_replace(","," ",$strArquivos);
    $strArquivo = $save_dir."/".date("d-m-Y-h-i").".zip";
    // Grava arquivo em arquivos/backup !! verifique as permissões desta pasta.
    exec("zip $strArquivo $strListaArquivos");

    echo $strArquivo;
}else{
    echo '0';
}
?>