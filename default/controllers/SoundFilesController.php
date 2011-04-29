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
 * Sound Files Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */

class SoundFilesController extends Zend_Controller_Action {

    /**
     * List all sound files
     */
    public function indexAction() {
        
        $this->view->breadcrumb = $this->view->translate("Configurações » Arquivos de Som");

        $this->view->url = $this->getFrontController()->getBaseUrl() ."/". $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from("sounds")
                        ->order('arquivo');
        
        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }

        $this->view->types = array('AST' => $this->view->translate('Padrão do Sistema'),
                                   'MOH' => $this->view->translate('Música em espera (URA)') );

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->files = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("arquivo"    => $this->view->translate("Código"),
                        "descricao"  => $this->view->translate("Nome"),
                        "tipo"       => $this->view->translate("Tipo") );

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Incluir Arquivo"),
                                          "css"     => "include"));
    }

    /**
     *  Add Sound File
     */
    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Arquivos de Som » Incluir");
        $form = new Snep_Form( new Zend_Config_Xml( "default/forms/sound_files.xml" ) );

        $file = new Zend_Form_Element_File('file');
        $file->setLabel( $this->view->translate('Arquivo de Som'))
             ->addValidator(new Zend_Validate_File_Extension(array('wav', 'gsm')))
             ->removeDecorator('DtDdWrapper')
             ->setIgnore(true);
        $form->addElement($file);

        $type = $form->getElement('type');
        $type->setMultiOptions(array('AST' => $this->view->translate('Padrão do Sistema'),
                                     'MOH' => $this->view->translate('Música em espera (URA)')))
             ->setValue('AST');

        if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if( $form_isValid ) {

                    $description = $_POST['description'];
                    $gsmConvert = $_POST['gsm'];
                    $type = $_POST['type'];

                    $originalName = str_replace(" ", "_", $_FILES['file']['name']);
                    $uploadName = $_FILES['file']['tmp_name'];

                    $path_sound = Zend_Registry::get('config')->system->path->asterisk->moh;

                    $arq_tmp = $path_sound . "tmp/" . $originalName;
                    $arq_dst = $path_sound . "/" . $originalName;
                    $arq_bkp = $path_sound . "/backup/" . $originalName;
                    $arq_orig = $path_sound . "/pt_BR/" . $originalName;

                    $exist = Snep_SoundFiles_Manager::get($originalName);

                    if($exist) {


                        exec("mv ");

                        // Backup old existent file
                        if ( ! move_uploaded_file($arq_orig, $arq_bkp)) {
                            echo "Erro movendo arquivo";
                        }

                        //update info no banco


                    }else{

                        // Move to temporary directory
                        if ( ! move_uploaded_file($uploadName, $arq_tmp)) {
                            echo "nao moveu";
                            exit;
                        }

                        if($_POST['gsm']) {

                            //sox foo.wav -r 8000 -c1 foo.gsm resample -ql

                        }

                        move_uploaded_file($arq_tmp, $arq_orig);

                        Snep_SoundFiles_Manager::add(array('arquivo' => $originalName,
                                                           'descricao' => $description,
                                                           'tipo' => $type));



                    }
                    
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }
        }
        $this->view->form = $form;

    }

    /**
     * Edit Carrier
     */
    public function editAction() {

        $this->view->breadcrumb = $this->view->translate("Arquivos de Som » Editar");

        $file = $this->_request->getParam("file");
        $data = Snep_SoundFiles_Manager::get($file);

        $this->view->file = $data;

        $form = new Snep_Form( new Zend_Config_Xml( "default/forms/sound_files.xml" ) );

        $fileEl = new Zend_Form_Element_Text('name');
        $fileEl->setValue( $data['arquivo'] )
                ->setLabel($this->view->translate('Nome do arquivo') )
                        ->setOrder(0)
                        ->setAttrib('disabled', true);
        $form->addElement($fileEl);

        $description = $form->getElement('description');
        $description->setLabel( $this->view->translate('Descrição') )
                    ->setValue( $data['descricao'])
                    ->setRequired(true);

        $gsm = $form->getElement('gsm');
        $gsm->setDescription( $this->view->translate('Converter'));

        $type = $form->getElement('type');
        $type->setLabel( $this->view->translate('Tipo de arquivo') )
             ->setMultiOptions(array('AST' => $this->view->translate('Padrão do Sistema'),
                                     'MOH' => $this->view->translate('Música em espera (URA)')))
                ->setValue( $data['tipo']);

      /* $form->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>"snep_form_element"))
        ));
       * *
       */      

        if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if($form_isValid) {
                  
                    
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }
        }
        $this->view->form = $form;
    }

    /**
     * Remove a Carrier
     */
    public function removeAction() {

       $this->view->breadcrumb = $this->view->translate("Operadoras » Remover");
       $id = $this->_request->getParam('id');

       Snep_SoundFiles_Manager::remove($id);
       exec("rm /var/lib/asterisk/sounds/pt_BR/$id");
       
       $this->_redirect( $this->getRequest()->getControllerName() );

    }
    
}