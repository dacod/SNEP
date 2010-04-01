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

$estado = isset($_POST['uf']) && $_POST['uf']!= "" ? $_POST['uf'] : display_error($LANG['msg_nostate'],true);
$municipios = Snep_Cnl::get($estado);

$options = '';
if(count($municipios > 0)) {
    foreach($municipios as $cidades) {
        $options .= "<option  value='{$cidades['municipio']}' > {$cidades['municipio']} </option> " ;
    }    
}else{
        $options = "<option> {$LANG['select']} </option>";
}

echo $options;
?>
