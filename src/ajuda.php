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
