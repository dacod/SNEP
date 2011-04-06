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
 * Controlador de regras de rotas.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class RouteController extends Zend_Controller_Action {

    /**
     * Add/Edit form for routes
     *
     * @var Zend_Form
     */
    protected $form;
    /**
     * Sub-form for Action Rules
     *
     * @var array
     */
    protected $forms;

    protected function cleanSrcDst($string) {
        $item = explode(",",$string);

        $search = array(
            "/^G:/",
            "/^S$/",
            "/^X$/",
            "/^T:/",
            "/^RX:/",
            "/^R:/"
        );
        $replace = array(
            $this->view->translate("Grupo") . " ",
            $this->view->translate("Sem Destino"),
            $this->view->translate("Qualquer"),
            $this->view->translate("Tronco") . " ",
            "",
            $this->view->translate("Ramal") . " ",
        );

        foreach ($item as $key => $entry) {
            
            if(substr($entry, 0, 1) == "T") {
                $entry = "T:" . PBX_Trunks::get(substr($entry, 2))->getName();
            }

            $item[$key] = preg_replace($search, $replace, $entry);
        }

        return implode("<br />", $item);
    }

    /**
     * List all Routes of the system
     */
    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("Regras de Negócio » Rotas");

        $db = Zend_Registry::get('db');
        $select = $db->select()->from("regras_negocio",
            array("id", "origem", "destino", "desc", "ativa", "prio")
        );

        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }

        $select->order("prio DESC");
        $select->order("id ASC");

        $routes = $db->query($select)->fetchAll();
        foreach ($routes as $key => $route) {
            $routes[$key]['origem'] = $this->cleanSrcDst($route['origem']);
            $routes[$key]['destino'] = $this->cleanSrcDst($route['destino']);
        }

        $this->view->routes = $routes;

        $this->view->filtro = $this->_request->getParam('filtro');

        $options = array(
            "id" => $this->view->translate("Código"),
            "origem" => $this->view->translate("Origem"),
            "destino" => $this->view->translate("Destino"),
            "desc" => $this->view->translate("Descrição")
        );

        // Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/route');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($options);
        $filter->setFieldValue($this->_request->getParam('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/");

        $this->view->form_filter = $filter;
        $this->view->filter = array(
            array(
                "url" => "{$this->view->baseUrl()}/index.php/simulator/",
                "display" => "Simulador",
                "css" => "debugger"
            ),
            array(
                "url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                "display" => "Incluir Regra",
                "css" => "include"
            )
        );
    }

    /**
     * Generate the form for routes
     *
     * @return Zend_Form
     */
    protected function getForm() {
        if ($this->form === Null) {
            $form_xml = new Zend_Config_Xml(Zend_Registry::get("config")->system->path->base . "/default/forms/route.xml");
            $form = new Snep_Form($form_xml);

            $actions = PBX_Rule_Actions::getInstance();
            $installed_actions = array();
            foreach ($actions->getInstalledActions() as $action) {
                $action_instance = new $action();
                $installed_actions[$action] = $action_instance->getName();
            }
            asort($installed_actions);
            $this->view->actions = $installed_actions;

            $src = new Snep_Form_Element_Html("route/elements/src.phtml", "src", false);
            $src->setLabel($this->view->translate("Origem"));
            $src->setOrder(1);
            $form->addElement($src);

            $dst = new Snep_Form_Element_Html("route/elements/dst.phtml", "dst", false);
            $dst->setLabel($this->view->translate("Destino"));
            $dst->setOrder(2);
            $form->addElement($dst);

            $time = new Snep_Form_Element_Html("route/elements/time.phtml", "time", false);
            $time->setOrder(4);
            $time->setLabel($this->view->translate("Horário de Incidência"));
            $form->addElement($time);

            $form->addElement(new Snep_Form_Element_Html("route/elements/actions.phtml", "actions"));

            $this->form = $form;
        }

        return $this->form;
    }

    /**
     * Edit Route
     */
    public function editAction() {
        $form = $this->getForm();
        $this->view->form = $form;

        $id = $this->getRequest()->getParam('id');

        $this->view->breadcrumb = $this->view->translate("Regras de Negócio » Rotas » Editar Rota %s", $id);

        try {
            $rule = PBX_Rules::get(mysql_escape_string($id));
        } catch (PBX_Exception_NotFound $ex) {
            throw new Zend_Controller_Action_Exception('Page not found.', 404);
        }

        if ($_POST) {
            if ($this->isValidPost()) {
                $new_rule = $this->parseRuleFromPost();
                $new_rule->setId($id);
                $new_rule->setActive($rule->isActive());
                PBX_Rules::update($new_rule);
                $this->_redirect("route");
            } else {
                $actions = "";
                foreach ($this->forms as $form_id => $form) {
                    $actions .= "addAction(" . json_encode(array(
                                "id" => $form_id,
                                "status" => $form['status'],
                                "type" => $form['type'],
                                "form" => $form['formData']
                            )) . ")\n";
                }
                $actions .= "setActiveAction($('actions_list').firstChild)\n";
                $this->view->rule_actions = $actions;
            }
        }

        $this->populateFromRule($rule);

        if (!isset($actions)) {
            $actions = "getRuleActions({$rule->getId()});\n";
            $this->view->rule_actions = $actions;
        }

        $this->renderScript('route/add_edit.phtml');
    }

    /**
     * Duplicate Route
     */
    public function duplicateAction() {
        $form = $this->getForm();
        $this->view->form = $form;

        $id = $this->getRequest()->getParam('id');
        $this->view->breadcrumb = $this->view->translate("Regras de Negócio » Rotas » Duplicar Rota %s", $id);

        try {
            $rule = PBX_Rules::get(mysql_escape_string($id));
        } catch (PBX_Exception_NotFound $ex) {
            throw new Zend_Controller_Action_Exception('Page not found.', 404);
        }

        if ($_POST) {
            if ($this->isValidPost()) {
                $new_rule = $this->parseRuleFromPost();
                $new_rule->setActive($rule->isActive());
                PBX_Rules::register($new_rule);
                $this->_redirect("route");
            } else {
                $actions = "";
                foreach ($this->forms as $form_id => $form) {
                    $actions .= "addAction(" . json_encode(array(
                                "id" => $form_id,
                                "status" => $form['status'],
                                "type" => $form['type'],
                                "form" => $form['formData']
                            )) . ")\n";
                }
                $actions .= "setActiveAction($('actions_list').firstChild)\n";
                $this->view->rule_actions = $actions;
            }
        }

        $rule->setDesc($this->view->translate("Cópia de %s", $rule->getDesc()));

        $this->populateFromRule($rule);

        if (!isset($actions)) {
            $actions = "getRuleActions({$rule->getId()});\n";
            $this->view->rule_actions = $actions;
        }

        $this->renderScript('route/add_edit.phtml');
    }

    /**
     * Action for adding a route
     */
    public function addAction() {
        $this->view->breadcrumb = $this->view->translate("Regras de Negócio » Rotas » Incluir");

        $form = $this->getForm();
        $form->getElement('week')->setValue(true);
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            if ($this->isValidPost()) {
                $rule = $this->parseRuleFromPost();
                PBX_Rules::register($rule);
                $this->_redirect("route");
            } else {
                $actions = "";
                foreach ($this->forms as $id => $form) {
                    $actions .= "addAction(" . json_encode(array(
                                "id" => $id,
                                "status" => $form['status'],
                                "type" => $form['type'],
                                "form" => $form['formData']
                            )) . ")\n";
                }
                $actions .= "setActiveAction($('actions_list').firstChild)\n";
                $this->view->rule_actions = $actions;

                unset($_POST['actions_order']);
                $rule = $this->parseRuleFromPost($_POST);
                $this->populateFromRule($rule);
            }
        } else {
            $this->view->dt_agirules = array(
                "dst" => "dstObj.addItem();\n",
                "src" => "origObj.addItem();\n",
                "time" => "timeObj.addItem();\n",
            );
        }

        $this->renderScript('route/add_edit.phtml');
    }

    /**
     * Validates $_POST for the required fields of the form.
     *
     * This method is implemented to validate the fields that can't be validated by
     * Zend_Form like the fields of Action Rules.
     * 
     *
     * @param array $post
     * @return boolean
     */
    protected function isValidPost($post=null) {
        $post = $post === null ? $_POST : $post;

        $assert = true;

        parse_str($post['actions_order'], $actions_order);
        $forms = array();
        foreach ($actions_order['actions_list'] as $action) {
            $real_action = new $post["action_$action"]["action_type"]();
            $action_config = new Snep_Rule_ActionConfig($real_action->getConfig());
            $action_config->setActionId("action_$action");

            $form = $action_config->getForm();
            $form->removeElement("submit");
            $form->removeElement("cancel");

            $action_type_element = new Zend_Form_Element_Hidden("action_type");
            $action_type_element->setValue(get_class($real_action));
            $action_type_element->setDecorators(array("ViewHelper"));
            $form->addElement($action_type_element);

            if (!$form->isValid($post["action_$action"])) {
                $assert = false;
                $status = "error";
            } else {
                $status = "success";
            }

            $form->setView(new Zend_View);
            $forms["action_$action"] = array(
                "type" => $post["action_$action"]["action_type"],
                "formData" => $form->render(),
                "status" => $status
            );
        }

        if (!$this->form->isValid($_POST)) {
            $assert = false;
            $status = "error";
        }

        if (!$assert) {
            $this->forms = $forms;
            return false;
        } else {
            $this->forms = null;
            return true;
        }
    }

    /**
     * Populate the fields based on a specific route
     *
     * @param PBX_Rule $rule
     */
    protected function populateFromRule(PBX_Rule $rule) {
        $srcList = $rule->getSrcList();
        $src = "origObj.addItem(" . count($srcList) . ");";
        foreach ($srcList as $index => $_src) {
            $src .= "origObj.widgets[$index].type='{$_src['type']}';\n";
            $src .= "origObj.widgets[$index].value='{$_src['value']}';\n";
        }

        $dstList = $rule->getDstList();
        $dst = "dstObj.addItem(" . count($dstList) . ");";
        foreach ($dstList as $index => $_dst) {
            $dst .= "dstObj.widgets[$index].type='{$_dst['type']}';\n";
            $dst .= "dstObj.widgets[$index].value='{$_dst['value']}';\n";
        }

        $timeList = $rule->getValidTimeList();
        $time = "timeObj.addItem(" . count($timeList) . ");";
        foreach ($timeList as $index => $_time) {
            $_time = explode('-', $_time);
            $time .= "timeObj.widgets[$index].startTime='{$_time[0]}';\n";
            $time .= "timeObj.widgets[$index].endTime='{$_time[1]}';\n";
        }

        // Treatment of the active time of the route
        $horario = $rule->getValidTimeList();
        $data = explode("-", $horario['0']);

        $this->view->dt_agirules = array(
            "dst" => $dst,
            "src" => $src,
            "time" => $time
        );

        $form = $this->getForm();

        $form->getElement('desc')->setValue($rule->getDesc());
        $form->getElement('record')->setValue($rule->isRecording());
        $form->getElement('prio')->setValue("p" . $rule->getPriority());

        $form->getElement('week')->setValue($rule->getValidWeekDays());
    }

    /**
     * Parse a route based on it's POST.
     * It's assumed here that all fields are already validated
     *
     * @param array $postData optional for ovewrite post data
     * @return PBX_Rule
     */
    protected function parseRuleFromPost($post=null) {
        $post = $post === null ? $_POST : $post;

        $rule = new PBX_Rule();

        // Adicionando dias da semana
        $weekDays = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
        $rule->cleanValidWeekList();
        foreach ($weekDays as $day) {
            if (in_array($day, $post['week'])) {
                $rule->addWeekDay($day);
            }
        }

        // Adicionando Origens
        foreach (explode(',', $post['srcValue']) as $src) {
            if (!strpos($src, ':')) {
                $rule->addSrc(array("type" => $src, "value" => ""));
            } else {
                $info = explode(':', $src);
                if (!is_array($info) OR count($info) != 2) {
                    throw new PBX_Exception_BadArg("Valor errado para origem da regra de negocio.");
                }

                if ($info[0] == "T") {
                    try {
                        PBX_Trunks::get($info[1]);
                    } catch (PBX_Exception_NotFound $ex) {
                        throw new PBX_Exception_BadArg("Tronco inválido para origem da regra");
                    }
                }

                $rule->addSrc(array("type" => $info[0], "value" => $info[1]));
            }
        }

        // Adding destinys
        foreach (explode(',', $post['dstValue']) as $dst) {
            if (!strpos($dst, ':')) {
                $rule->addDst(array("type" => $dst, "value" => ""));
            } else {
                $info = explode(':', $dst);
                if (!is_array($info) OR count($info) != 2) {
                    throw new PBX_Exception_BadArg("Valor errado para destino da regra de negocio.");
                }

                if ($info[0] == "T") {
                    try {
                        PBX_Trunks::get($info[1]);
                    } catch (PBX_Exception_NotFound $ex) {
                        throw new PBX_Exception_BadArg("Tronco inválido para destino da regra");
                    }
                }

                $rule->addDst(array("type" => $info[0], "value" => $info[1]));
            }
        }

        // Adding time
        $rule->cleanValidTimeList();
        foreach (explode(',', $post['timeValue']) as $time_period) {
            $rule->addValidTime($time_period);
        }

        // Adding Description
        $rule->setDesc($post['desc']);

        // Defining recording order
        if (isset($post['record']) && $post['record']) {
            $rule->record();
        }

        // Defining priority
        $rule->setPriority(substr($post['prio'], 1));

        if (isset($post['actions_order'])) {
            parse_str($post['actions_order'], $actions_order);
            foreach ($actions_order['actions_list'] as $action) {
                $real_action = new $post["action_$action"]["action_type"]();
                $action_config = new Snep_Rule_ActionConfig($real_action->getConfig());
                $real_action->setConfig($action_config->parseConfig($post["action_$action"]));
                $rule->addAction($real_action);
            }
        }

        return $rule;
    }

    public function deleteAction() {
        $id = mysql_escape_string($this->getRequest()->getParam('id'));

        try {
            $rule = PBX_Rules::get($id);
        }
        catch(PBX_Exception_NotFound $ex) {
            throw new Zend_Controller_Action_Exception('Page not found.', 404);
        }

        PBX_Rules::delete($id);

        $this->_redirect("route");
    }

}
