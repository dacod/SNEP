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

//require_once("../includes/verifica.php");
require_once("../configs/config.php");

header("Content-Type: application/json");
function http_error($code, $message) {
    switch($code) {
        case 400:
            header('HTTP/1.1 400 Bad Request');
            break;
        case 404:
            header('HTTP/1.1 404 Not Found');
            break;
        default:
            header('HTTP/1.1 500 Internal Server Error');
    }
    echo json_encode(array("status"=>"error","message"=>$message));
    exit(1);
}

//ver_permissao(49);

if(!isset($_GET['mode']) || ($_GET['mode'] != "new_action" && $_GET['mode'] != "get_rule_actions")) {
    http_error(400, "Você precisa especificar um modo. new_action ou get_rule_actions");
}

if($_GET['mode'] == "new_action") {
    if(isset($_GET['id'])) {
        $custom_id =  $_GET['id'];
    }
    else {
        http_error(400, "You must specify an id for the action");
    }

    if(isset($_GET['type'])) {
        $classname =  $_GET['type'];
    }
    else {
        http_error(400, "You need to specify 'type'");
    }

    $actions = PBX_Rule_Actions::getInstance();

    if($actions->isRegistered($classname)) {
        $action = new $classname;
    }
    else {
        http_error(400, "$classname is not a registered action");
    }

    $config = new Snep_Rule_ActionConfig($action->getConfig());
    $config->setActionId($custom_id);

    $form = $config->getForm();
    $action_type_element = new Zend_Form_Element_Hidden("action_type");
    $action_type_element->setValue(get_class($action));
    $action_type_element->setDecorators(array("ViewHelper"));
    $form->addElement($action_type_element);

    $form->setView(new Zend_View());
    $form->removeDecorator('form');
    $form->removeElement('submit');
    $form->removeElement('cancel');

    $form_html = $form->render();

    echo json_encode(array(
        "id" => $custom_id,
        "status" => "success",
        "type" => get_class($action),
        "label" => $action->getName(),
        "form"   => $form_html
    ));
}
else {
    if(isset($_GET['rule_id']) && !is_numeric('rule_id')) {
        $rule_id =  $_GET['rule_id'];
    }
    else {
        http_error(400, "Você precisa especificar um rule_id com um id de rota válido.");
    }

    try {
        $regra = PBX_Rules::get($rule_id);
    }
    catch(PBX_Exception_NotFound $ex) {
        http_error(404, "Não encontrada rota com id $rule_id");
    }

    $actions = array();
    foreach ($regra->getActions() as $id => $action) {
        $config = new Snep_Rule_ActionConfig($action->getConfig());
        $config->setActionId("action_$id");

        $form = $config->getForm();
        $action_type_element = new Zend_Form_Element_Hidden("action_type");
        $action_type_element->setDecorators(array("ViewHelper"));
        $action_type_element->setValue(get_class($action));
        $form->addElement($action_type_element);

        $form->setView(new Zend_View());
        $form->removeDecorator('form');
        $form->removeElement('submit');
        $form->removeElement('cancel');

        $form_html = $form->render();

        $actions["action_$id"] = array(
            "id" => "action_$id",
            "status" => "success",
            "type" => get_class($action),
            "label" => $action->getName(),
            "form"   => $form_html
        );
    }
    echo json_encode($actions);
}
