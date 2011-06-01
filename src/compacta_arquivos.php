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

require_once("../configs/config.php");
$save_dir = $SETUP['ambiente']['path_voz_bkp'];
if(strlen($_REQUEST['arquivos']) > 1) {
    $strArquivos = substr($_REQUEST['arquivos'],0,strlen($_REQUEST['arquivos'])-1);
    $strListaArquivos = str_replace(","," ",$strArquivos);
    $strNomeArquivo = date("d-m-Y-h-i").".tar.bz2";
    $strArquivo = $save_dir."/".$strNomeArquivo;
    // Grava arquivo em arquivos/backup !! verifique as permissões desta pasta.
    exec("tar -jcvf $strArquivo $strListaArquivos");

    echo $SETUP['system']['path.web']."/arquivos/".$strNomeArquivo;
}else{
    echo '0';
}
