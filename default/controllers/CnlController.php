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

ini_set("max_execution_time", 180);

class CnlController extends Zend_Controller_Action {

    public function indexAction() {

       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Configure"),
            $this->view->translate("CNL Update"),
            $className
        ));

        $config = Zend_Registry::get('config');
        $this->view->pathweb = $config->system->path->web;

        // verification procedure
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from('ars_estado');
        $stmt = $select->query();
        $result = $stmt->fetchAll();

        // insert state data
        if( count($result ) < 26 ) {

            $brStates = array( 'AC'=>'Acre', 'AL'=>'Alagoas','AM'=>'Amazonas','AP'=>'Amapá',
                    'BA'=>'Bahia','CE'=>'Ceará','DF'=>'Distrito Federal',
                    'ES'=>'Espírito Santo','GO'=>'Goiás','MA'=>'Maranhão',
                    'MG'=>'Minas Gerais','MS'=>'Mato Grosso do Sul',
                    'MT'=>'Mato Grosso','PA'=>'Pará','PB'=>'Paraíba',
                    'PE'=>'Pernambuco','PI'=>'Piauí','PR'=>'Paraná','RJ'=>'Rio de Janeiro',
                    'RN'=>'Rio Grande do Norte','RO'=>'Rondônia','RR'=>'Roraima',
                    'RS'=>'Rio Grande do Sul','SC'=>'Santa Catarina','SE'=>'Sergipe',
                    'SP'=>'São Paulo', 'TO'=>'Tocantins'  );

            foreach($brStates as $uf => $state) {

                $db->beginTransaction();
                try {
                    $_state = array('cod' => $uf, 'name' => $state);
                    $db->insert('ars_estado', $_state);
                    $db->commit();

                } catch (Exception $ex) {
                    $db->rollBack();
                    throw $ex;
                }
            }
        }

        $form = new Snep_Form();
        $form->setAction($this->getFrontController()->getBaseUrl() . "/default/cnl/index");
        $this->view->formAction = $this->getFrontController()->getBaseUrl() . "/default/cnl/index";

        $element = new Zend_Form_Element_File('cnl');
        $element->setLabel($this->view->translate('CNL File'))
                ->setDestination('/tmp/')
                ->setRequired(true) ;

        $element->addValidator('Extension', false, array('bz2','tar.bz2'));
        $element->removeDecorator('DtDdWrapper');
        $form->addElement($element, 'cnl');

        $form->getElement("submit")->setLabel($this->view->translate("Save"));

        $form->setAttrib('enctype', 'multipart/form-data');

        $this->view->valid = true;

        if ($this->_request->getPost()) {

            $form_isValid = $form->isValid($_POST);
            $this->view->valid = $form_isValid;

            if ($form_isValid) {
                $data = $_POST;

                $adapter = new Zend_File_Transfer_Adapter_Http();

                if ($adapter->isValid()) {

                    $adapter->receive();

                    $fileName = $adapter->getFileName();
                    exec("tar xjvf {$fileName} -C /tmp");

                    $json = file_get_contents(substr($fileName, 0, -8));
                    $cnl = (Zend_Json_Decoder::decode($json, Zend_Json::TYPE_ARRAY));

                    $data = $cnl["operadoras"];
                    unset($cnl["operadoras"]);

                    Snep_Cnl::delPrefixo();
                    Snep_Cnl::delDDD();
                    Snep_Cnl::delCidade();
                    Snep_Cnl::delOperadora();

                    foreach ($data as $carrier => $id) {

                            Snep_Cnl::addOperadora($id, $carrier);
                    }

                    foreach ($cnl as $data => $id) {

                        foreach ($id as $state => $es) {

                            foreach ($es as $ddd => $d) {

                                foreach ($d as $city => $pre) {

                                    $cityId = Snep_Cnl::addCidade($city);
                                    Snep_Cnl::addDDD($ddd, $state, $cityId);

                                    foreach ($pre as $prefix => $op) {
                                        Snep_Cnl::addPrefixo($prefix, $cityId, $op);
                                    }
                                }
                            }
                        }
                    }
                } else {

                    throw new ErrorException( $this->view->translate("File format is not valid") );
                }
                $this->_redirect ($this->getRequest()->getControllerName());
            }
        }
        $this->view->form = $form;
    }

}
