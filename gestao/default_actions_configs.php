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
ver_permissao(48);

$acoes = PBX_Rule_Actions::getInstance();
$info_acoes = array();
foreach ($acoes->getInstalledActions() as $acao) {
    $acao = new $acao;
    if($acao->getDefaultConfigXML() != "") {
        $info_acoes[] = array(
            "id"          => get_class($acao),
            "name"        => $acao->getName(),
            "description" => $acao->getDesc()
        );
    }
}

$smarty->assign("ACOES", $info_acoes);
$titulo = $LANG['menu_rules']." -> ".$LANG['default_configs'];
display_template("default_actions_configs.tpl",$smarty,$titulo);
