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
 * Expression Alias Controller. 
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Lucas Ivan Seidenfus
 */
class ExpressionAliasController extends Zend_Controller_Action {

    protected $form;

    public function indexAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Routing"),
            $this->view->translate("Expression Alias"),
        ));

        $aliases = PBX_ExpressionAliases::getInstance();
        $this->view->aliases = $aliases->getAll();

        $this->view->filter = array(array("url" => $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/add',
                "display" => $this->view->translate("Add Expression Alias"),
                "css" => "include"),
        );
    }

    protected function getForm() {

        if ($this->form === Null) {
            $form_xml = new Zend_Config_Xml(Zend_Registry::get("config")->system->path->base . "/modules/default/forms/expression_alias.xml");
            $form = new Snep_Form($form_xml);


            $exprField = new Snep_Form_Element_Html("expression-alias/elements/expr.phtml", "expr", false);
            $exprField->setLabel($this->view->translate("Expressions"));
            $exprField->setOrder(1);
            $form->addElement($exprField);

            $this->form = $form;
        }

        return $this->form;
    }

    public function addAction() {
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Routing"),
            $this->view->translate("Expression Alias"),
            $this->view->translate("Add Expression Alias"),
        ));
        
        $form = $this->getForm();

        if ($this->getRequest()->isPost()) {

            $isValid = $form->isValid($_POST);

            if($isValid) {
                $expression = array(
                    "name" => $_POST['name'],
                    "expressions" => explode(",", $_POST['exprValue'])
                );
                $aliasesPersistency = PBX_ExpressionAliases::getInstance();
                try {

                    $aliasesPersistency->register($expression);
                    $exprList = $expression['expressions'];
                    $expr = "exprObj.addItem(" . count($exprList) . ");\n";

                    foreach ($exprList as $index => $value) {
                        $expr .= "exprObj.widgets[$index].value='{$value}';\n";
                    }

                    $this->view->dataExprAlias = $expr;
                    $form = $this->getForm();
                    $form->getElement('name')->setValue($_POST['name']);

                    $this->_redirect ($this->getRequest()->getControllerName());

                } catch (Exception $ex) {
                    throw new PBX_Exception_BadArg("Invalid Argument");
                }
            }
        }
        
        $this->view->dataExprAlias = "exprObj.addItem();\n";
        $this->view->form = $form;
        $this->renderScript('expression-alias/add_edit.phtml');

    }

    public function editAction() {
        $id = (int) $this->getRequest()->getParam('id');
        
        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Routing"),
            $this->view->translate("Expression Alias"),
            $this->view->translate("Edit Expression Alias %s", $id),
        ));

        $form = $this->getForm();
        $this->view->form = $form;
        $aliasesPersistency = PBX_ExpressionAliases::getInstance();

        if ($this->getRequest()->isPost()) {

            $isValid = $form->isValid($_POST);

            if($isValid) {
                $expression = array(
                    "id" => $id,
                    "name" => $_POST['name'],
                    "expressions" => explode(",", $_POST['exprValue'])
                );

                try {
                    $aliasesPersistency->update($expression);
                } catch (Exception $ex) {
                    throw $ex;
                }

                $this->_redirect ($this->getRequest()->getControllerName());
            }
        }
            
        $alias = $aliasesPersistency->get($id);
        $exprList = $alias['expressions'];

        $expr = "exprObj.addItem(" . count($exprList) . ");\n";

        foreach ($exprList as $index => $value) {
            $expr .= "exprObj.widgets[$index].value='{$value}';\n";
        }
        $this->view->dataExprAlias = $expr;
        $form = $this->getForm();
        $form->getElement('name')->setValue($alias['name']);

        $this->renderScript('expression-alias/add_edit.phtml');

    }

    public function deleteAction() {

        if ($this->getRequest()->isGet()) {
            $id = (int) $this->getRequest()->getParam('id');

            $aliasesPersistency = PBX_ExpressionAliases::getInstance();
            if (($alias = $aliasesPersistency->get($id)) !== null) {
                $aliasesPersistency->delete($id);
            }
            $this->_redirect ($this->getRequest()->getControllerName());
        }
    }

}
