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

$action_id = isset($_GET['id']) ? $_GET['id'] : "";

if(!class_exists($action_id)) {
    display_error($LANG['invalid_action'], true);
}
else {
    $acao = new $action_id();

    $registry = PBX_Registry::getInstance($action_id);

    if($acao->getDefaultConfigXML() != "") {
        $action_config = new PBX_Rule_ActionConfig($acao->getDefaultConfigXML());

        if( count($_POST) > 0 ) {
            $new_config = $action_config->parseConfig($_POST);

            foreach ($new_config as $key => $value) {
                $registry->{$key} = $value;
            }

            // Limpando valores que não são mais usados
            $previous_values = $registry->getAllValues();
            foreach ($previous_values as $key => $value) {
                if( !key_exists($key, $new_config) ) {
                    unset( $registry->{$value} );
                }
            }

            $smarty->assign("success", true);
        }
        
        $acao->setDefaultConfig( $registry->getAllValues() );
        $action_config = new PBX_Rule_ActionConfig($acao->getDefaultConfigXML());
        
        $form = $action_config->getForm()
                ->setView(new Zend_View())
                ->setAction("./configure_action.php?id=$action_id");

        $smarty->assign("action_form", $form);

        $titulo = $LANG['menu_rules'] . " -> " . $LANG['default_configs'] . " -> " . $acao->getName();
        display_template("configure_action.tpl",$smarty,$titulo);
    }
    else {
        display_error($LANG['no_configurable_action'], true);
    }
}
