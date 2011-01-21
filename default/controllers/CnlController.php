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
class CnlController extends Zend_Controller_Action {

    public function indexAction() {

        $this->view->breadcrumb = $this->view->translate("Configurações » Atualização CNL");

        $config = Zend_Registry::get('config');
        $this->view->pathweb =  $config->system->path->web;
        
        $form = new Snep_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() . "/default/cnl/index");
        $this->view->formAction = $this->getFrontController()->getBaseUrl() . "/default/cnl/index";

        $element = new Zend_Form_Element_File('cnl');
        $element->setLabel( $this->view->translate('Arquivo CNL') )
                ->setDestination('/tmp/');
        
        $element->addValidator('Extension', false, 'bz2');
        $element->removeDecorator('DtDdWrapper');
        $form->addElement($element, 'cnl');

        $form->setButton();
        $form->getElement("submit")->setLabel($this->view->translate("Enviar"));

        $form->setAttrib('enctype', 'multipart/form-data');

        $this->view->form = $form;
        $this->view->valid = true;

        if ($this->_request->getPost()) {
           
            $form_isValid = $form->isValid($_POST);
            $this->view->valid = $form_isValid;

            if ($form_isValid) {
                $data = $_POST;

                $adapter = new Zend_File_Transfer_Adapter_Http();

                if (!$adapter->isValid()) {
                    echo "Formato de arquivo invalido";
                    exit;
                } else {
                    $adapter->receive();

                    $fileName = $adapter->getFileName();
                    exec("tar xjvf {$fileName} -C /tmp");

                    $json = file_get_contents(substr($fileName,0,-8));
                    $cnl = (Zend_Json_Decoder::decode($json, Zend_Json::TYPE_ARRAY));

                    $data = $cnl["operadoras"];
                    unset($cnl["operadoras"]);

                    Snep_Cnl::delPrefixo();
                    Snep_Cnl::delDDD();
                    Snep_Cnl::delCidade();
                    Snep_Cnl::delOperadora();

                    foreach ($data as $operadora => $id) {

                        Snep_Cnl::addOperadora($id,$operadora);

                    }

                    foreach ($cnl as $data => $id ) {

                        foreach ($id as $estado => $es) {

                            foreach ($es as $ddd => $d) {

                                foreach ($d as $cidade => $pre) {

                                    $idCidade = Snep_Cnl::addCidade($cidade);
                                    Snep_Cnl::addDDD($ddd,$estado,$idCidade);

                                    foreach ($pre as $prefixo => $op) {

                                        Snep_Cnl::addPrefixo($prefixo,$idCidade,$op);

                                    }
                                }
                            }
                        }
                    }

                    $this->_redirect("/default/cnl/");
                }
                    
            }
        }
    }

}
