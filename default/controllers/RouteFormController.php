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

/**
 * Controller for REST services that aid RouteController
 *
 * @see RouteController
 *
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class RouteFormController extends Snep_Rest_Controller {

    /**
     * Returns a action form.
     *
     * @param Object $data
     * @return array $response
     */
    public function get($data) {
        if (!isset($data->mode) || ($data->mode != "new_action" && $data->mode != "get_rule_actions")) {
            throw new Snep_Rest_Exception_BadRequest("This service expects 'mode' parameter to be new_action or get_rule_actions.");
        }

        if ($data->mode === "new_action") {
            return $this->new_action($data);
        } else {
            return $this->get_rule_actions($data);
        }
    }

    protected function new_action($data) {
        if (!isset($data->id)) {
            throw new Snep_Rest_Exception_BadRequest("Missing or wrong parameter 'id'. You must specify an id for the new action form.");
        }

        $id = $data->id;

        if (!isset($data->type)) {
            throw new Snep_Rest_Exception_BadRequest("Missing parameter 'type'. You must specify the classname for the action.");
        }
        $classname = $data->type;

        $actions = PBX_Rule_Actions::getInstance();

        if ($actions->isRegistered($classname)) {
            $action = new $classname;
        } else {
            throw new Snep_Rest_Exception_BadRequest("$classname is not a registered action");
        }

        $config = new Snep_Rule_ActionConfig($action->getConfig());
        $config->setActionId($id);

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

        return array(
            "id" => $id,
            "status" => "success",
            "type" => get_class($action),
            "label" => $action->getName(),
            "form" => $form_html
        );
    }

    protected function get_rule_actions($data) {
        if (!isset($data->rule_id)) {
            throw new Snep_Rest_Exception_BadRequest("Missing or wrong parameter 'rule_id'.");
        }

        $rule_id = $data->rule_id;

        try {
            $rule = PBX_Rules::get($rule_id);
        } catch (PBX_Exception_NotFound $ex) {
            throw new Snep_Rest_Exception_NotFound("Cant find a rule with id '$rule_id'");
        }

        $actions = array();
        foreach ($rule->getActions() as $id => $action) {
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
                "form" => $form_html
            );
        }
        return $actions;
    }

}
