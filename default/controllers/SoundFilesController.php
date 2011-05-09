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

require_once 'Snep/Inspector.php';

class SoundFilesController extends Zend_Controller_Action {

    /**
     * List all sound files
     */
    public function indexAction() {
        
        $this->view->breadcrumb = $this->view->translate("Configure » Sound Files");

        $this->view->url = $this->getFrontController()->getBaseUrl() ."/". $this->getRequest()->getControllerName();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from("sounds")
                        ->where("tipo = 'AST'")
                        ->order('arquivo');
        
        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }


        $objInspector = new Snep_Inspector('Permissions');
        $inspect = $objInspector->getInspects();
        $this->view->error = $inspect['Permissions'];
        
        $stmt = $db->query($select);
        $files = $stmt->fetchAll();
        
        foreach($files as $id => $file) {
            $info = Snep_SoundFiles_Manager::verifySoundFiles($file['arquivo']);
            $_files[] = array_merge($file, $info);
        }
     
        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );
        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_Array( $_files );
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->files = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("arquivo"    => $this->view->translate("Code"),
                        "descricao"  => $this->view->translate("Name"),
                        "tipo"       => $this->view->translate("Type") );

        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Add Sound File"),
                                          "css"     => "include"));
    }

    /**
     *  Add Sound File
     */
    public function addAction() {

        $this->view->breadcrumb = $this->view->translate("Sound Files » Add");
        $form = new Snep_Form( new Zend_Config_Xml( "default/forms/sound_files.xml" ) );

        $file = new Zend_Form_Element_File('file');
        $file->setLabel( $this->view->translate('Select the file'))
             ->addValidator(new Zend_Validate_File_Extension(array('wav', 'gsm')))
             ->removeDecorator('DtDdWrapper')
             ->setIgnore(true)
            ->setRequired(true);
        $form->addElement($file);

        $form->removeElement('filename');

        if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if( $form_isValid ) {

                    $description = $_POST['description'];
                    $gsmConvert = $_POST['gsm'];
                    $type = 'AST';

                    $invalid = array('â','ã','á','à','ẽ','é','è','ê','í','ì','ó','õ','ò','ú','ù','ç'," ",'@','!');
                    $valid = array('a','a','a','a','e','e','e','e','i','i','o','o','o','u','u','c',"_",'_','_');

                    $originalName = str_replace($invalid, $valid, $_FILES['file']['name'] );
                    $uploadName = $_FILES['file']['tmp_name'];

                    $path_sound = Zend_Registry::get('config')->system->path->asterisk->sounds;

                    $arq_tmp = $path_sound . "/tmp/" . $originalName;
                    $arq_dst = $path_sound . "/" . $originalName;
                    $arq_bkp = $path_sound . "/backup/" . $originalName;
                    $arq_orig = $path_sound . "/pt_BR/" . $originalName;

                    $exist = Snep_SoundFiles_Manager::get($originalName);

                    if($exist) {
                        
                        $form->getElement('file')->addError( $this->view->translate('File already exists') );
                        $form_isValid = false;

                    }else{

                        if ( ! move_uploaded_file($uploadName, $arq_tmp)) {
                            throw new ErrorException( $this->view->translate("Unable to move file"));                            
                        }
                        
                        if( $_POST['gsm'] ) {
                            $fileNe = $path_sound .'/'. basename($arq_dst, '.wav') . '.gsm';
                            exec( "sox $arq_tmp -r 8000 {$fileNe}" );
                            $originalName = basename($originalName, '.wav') . ".gsm";

                        }else{
                            exec( "sox $arq_tmp -r 8000 -c 1 -e signed-integer -b 16 $arq_dst" );
                        }

                        if( file_exists($arq_dst) || file_exists($fileNe) ) {
                            Snep_SoundFiles_Manager::add(array('arquivo' => $originalName,
                                                               'descricao' => $description,
                                                               'tipo' => 'AST'));
                        }

                        $this->_redirect( $this->getRequest()->getControllerName() );
                    }                    
                }
        }
        $this->view->form = $form;

    }

    /**
     * Edit Carrier
     */
    public function editAction() {

        $this->view->breadcrumb = $this->view->translate("Sound Files » Edit");

        $file = $this->_request->getParam("file");
        $data = Snep_SoundFiles_Manager::get($file);

        $this->view->file = $data;

        $form = new Snep_Form( new Zend_Config_Xml( "default/forms/sound_files.xml" ) );

        $file = new Zend_Form_Element_File('file');
        $file->setLabel( $this->view->translate('Sound Files'))
             ->addValidator(new Zend_Validate_File_Extension(array('wav', 'gsm')))
             ->removeDecorator('DtDdWrapper')
             ->setIgnore(true);
        $form->addElement($file);

        $form->getElement('filename')->setValue( $data['arquivo'] )
                                     ->setAttrib('readonly', true);
        
        $form->getElement('description')->setLabel( $this->view->translate('Description') )
                                        ->setValue( $data['descricao'])
                                        ->setRequired(true);

        if($this->_request->getPost()) {

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            if($form_isValid) {

                if( $_FILES['file']['name'] != "" && $_FILES['file']['size'] > 0  ) {

                    $path_sound = Zend_Registry::get('config')->system->path->asterisk->sounds;
                    $filepath = Snep_SoundFiles_Manager::verifySoundFiles( $_POST['filename'], true );

                    exec("mv {$filepath['fullpath']} $path_sound/backup/ ");
                    exec("mv  {$_FILES['file']['tmp_name']} {$filepath['fullpath']} ");
                }

                Snep_SoundFiles_Manager::edit( $_POST ) ;

                $this->_redirect( $this->getRequest()->getControllerName() );
            }
        }
        $this->view->form = $form;
    }

    /**
     * Remove a Carrier
     */
    public function removeAction() {

       $this->view->breadcrumb = $this->view->translate("Sound Files » Delete");
       $id = $this->_request->getParam('id');

       Snep_SoundFiles_Manager::remove($id);
       exec("rm /var/lib/asterisk/sounds/pt_BR/$id");
       
       $this->_redirect( $this->getRequest()->getControllerName() );

    }


    public function restoreAction() {

        $file = $this->_request->getParam('file');

        if( $file ) {
            $result = Snep_SoundFiles_Manager::verifySoundFiles($file, true);

            if( $result['fullpath'] && $result['backuppath'] ) {
                try{
                     exec("mv {$result['backuppath']}  {$result['fullpath']} ");

                }catch(Exception $e) {
                      throw new ErrorException( $this->view->translate("Unable to restore file"));
                }
            }
        }

        $this->_redirect( $this->getRequest()->getControllerName() );
        
    }
    
}