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
 * Music on Hold Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti
 */

class MusicOnHoldController extends Zend_Controller_Action {

    /**
     * List all Music on Hold sounds
     */
    public function indexAction() {

        $objInspector = new Snep_Inspector('Sounds');
        $this->view->error = array_pop( $objInspector->getInspects() );

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Configure"),
            $this->view->translate("Music on Hold Sessions")
        ));

        $this->view->url = $this->getFrontController()->getBaseUrl() ."/".
                           $this->getRequest()->getControllerName();

        $this->view->modes = array('files' => $this->view->translate('Directory'),
                                   'mp3' => $this->view->translate('MP3'),
                                   'quietmp3' => $this->view->translate('Normal'),
                                   'mp3nb' => $this->view->translate('Without buffer'),
                                   'quietmp3nb' => $this->view->translate('Without buffer quiet'),
                                   'custom' => $this->view->translate('Custom application') );

        Snep_SoundFiles_Manager::syncFiles();
        
        $this->view->sections = Snep_SoundFiles_Manager::getClasses();
        
        $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add/",
                                          "display" => $this->view->translate("Add Session"),
                                          "css"     => "include"));        
    }

    /**
     *  Add Sound File
     */
    public function addAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Music on Hold Sessions"),
            $this->view->translate("Add")
        ));

        $form = new Snep_Form( new Zend_Config_Xml( "modules/default/formsmusic_on_hold.xml" ) );       
        $form->getElement('base')->setAttrib('readonly', true );
        
        if($this->_request->getPost()) {

                $class = $_POST;
                $classes = Snep_SoundFiles_Manager::getClasses();

                $form_isValid = $form->isValid($_POST);

                if($class['base'] != '/var/lib/asterisk/moh/') {
                    $form->getElement('name')->addError( 
                            $this->view->translate('Invalid Path') );

                    $form_isValid = false;
                }
                if ( file_exists($class['directory'])) {
                    $form->getElement('directory')->addError( 
                            $this->view->translate('Directory already exists') );

                    $form_isValid = false;
                }

                foreach($classes as $name => $item) {

                    if($item['name'] == $class['name']) {
                       $form->getElement('name')->addError( 
                               $this->view->translate('Music on hold class already exists') );

                       $form_isValid = false;
                    }
                    $fullPath = $class['base'] . $class['directory'];
                    if($item['directory'] == $fullPath) {
                            $form->getElement('directory')->addError( 
                                    $this->view->translate('Directory already exists') );

                            $form_isValid = false;
                    }
                }

                if( $form_isValid ) {
                     $_POST['directory'] = $_POST['base'] . $_POST['directory'];
                     
                     Snep_SoundFiles_Manager::addClass($_POST);
                     
                     $this->_redirect( $this->getRequest()->getControllerName() );
                }
                
        }
        $this->view->form = $form;

    }

    /**
     * Edit Carrier
     */
    public function editAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Music on Hold Sessions"),
            $this->view->translate("Edit")
        ));

        $file = $this->_request->getParam("file");
        $data = Snep_SoundFiles_Manager::getClasse($file);
        
        $form = new Snep_Form( new Zend_Config_Xml( "modules/default/formsmusic_on_hold.xml" ) );        
        $form->getElement('name')->setValue( $data['name']);
        $form->getElement('mode')->setValue($data['mode']);

        $directory =  explode("/", $data['directory']) ;
        $directoryName = array_pop( $directory );

        $form->getElement('base')->setAttrib('readonly', true )->setValue( implode("/", $directory) . '/' );
        $form->getElement('directory')->setValue( $directoryName )->setRequired(true);

        $originalName = new Zend_Form_Element_Hidden('originalName');
        $originalName->setValue( $data['name'] );

        $form->addElement( $originalName );

      /* $form->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('elementTd' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'th')),
            array(array('elementTr' => 'HtmlTag'), array('tag' => 'tr', 'class'=>"snep_form_element"))
        ));      
       */

        if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if($form_isValid) {
                    
                    $class = array('name' => $_POST['name'],
                                   'mode' => $_POST['mode'],
                                   'directory' =>  $_POST['base'] . $_POST['directory'] );

                    $originalName = $_POST['originalName'];

                    Snep_SoundFiles_Manager::editClass($originalName, $class);
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }
        }

        $this->view->file = $data;
        $this->view->form = $form;
    }

    /**
     * Remove a Carrier
     */
    public function removeAction() {

       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Music on Hold Sessions"),
            $this->view->translate("Remove")
        ));

       $file = $this->_request->getParam('file');

       $this->view->class = Snep_SoundFiles_Manager::getClasse($file);
       
       $this->view->message = $this->view->translate("You are removing a music on hold class, it has some audio files attached to it.");

       $form = new Snep_Form();

       $name = new Zend_Form_Element_Hidden('name');
       $name->setValue( $file );
       $form->addElement($name);

       $check = new Zend_Form_Element_Checkbox('remove');
       $check->setLabel( $this->view->translate("Delete Sound Files?"))->setDescription( $this->view->translate('Yes'));
       $form->addElement($check);

       if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();

                if($form_isValid) {
                    if($_POST['remove']) {

                        $class = Snep_SoundFiles_Manager::getClasse($_POST['name']);
                        Snep_SoundFiles_Manager::removeClass($class);
                    }          
                    $this->_redirect( $this->getRequest()->getControllerName() );
                }
        }
       $this->view->form = $form;
       
    }

    public function fileAction() {

       $file = $this->_request->getParam('class');

       $this->view->url = $this->getFrontController()->getBaseUrl() ."/".
                          $this->getRequest()->getControllerName();

       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Music on Hold Sessions"),
            $this->view->translate("File"),
            $file
        ));

       $class = Snep_SoundFiles_Manager::getClasse($file);

       $this->view->files = Snep_SoundFiles_Manager::getClassFiles($class);

       $arrayInf = array('data'    => null,
                         'descricao'  => $this->view->translate('Not Found'),
                         'secao'    => $class['name'] );
       
       if( isset($this->view->files) ) {
           foreach( $this->view->files as $file => $list ) {
               if( ! isset( $list['arquivo'] ) ) {
                    $arrayInf['arquivo'] = $file;
                    $this->view->files[$file] = $arrayInf;
                    (! isset($errors) ? $errors = "" : false);
                    $errors .= $this->view->translate("File {$file} not found") . "<br/>" ;
               }
           }
       }

       ( isset($errors) ? $this->view->error = array('error' => true, 'message' => $errors) : false);
       
       $this->view->filter = array(array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/",
                                         "display" => $this->view->translate("Back"),
                                         "css" => "back" ),
                                   array("url"     => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/addfile/class/{$class['name']}",
                                         "display" => $this->view->translate("Add File"),
                                         "css"     => "include"),
                                   );
    }

    public function addfileAction() {

       $className = $this->_request->getParam('class');

       $class = Snep_SoundFiles_Manager::getClasse($className);

       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Music on Hold Sessions"),
            $this->view->translate("Add File"),
            $className
        ));

       $form = new Snep_Form( new Zend_Config_Xml( "modules/default/formssound_files.xml" ) );

       $file = new Zend_Form_Element_File('file');
       $file->setLabel( $this->view->translate('Sound File'))
             ->addValidator(new Zend_Validate_File_Extension(array('wav', 'gsm')))
             ->removeDecorator('DtDdWrapper')
             ->setIgnore(true);

       $form->addElement($file);

       $section = new Zend_Form_Element_Hidden('section');
       $section->setValue( $class['name'] );
       $form->addElement( $section );

       $form->removeElement('type');

       if($this->_request->getPost()) {

                $class = Snep_SoundFiles_Manager::getClasse($_POST['section']);
             
                $form_isValid = $form->isValid($_POST);
                $dados = $this->_request->getParams();
                
                $invalid = array('â','ã','á','à','ẽ','é','è','ê','í','ì','ó','õ','ò','ú','ù','ç'," ",'@','!');
                $valid = array('a','a','a','a','e','e','e','e','i','i','o','o','o','u','u','c',"_",'_','_');

                $originalName = str_replace($invalid, $valid, $_FILES['file']['name'] );
                $files = Snep_SoundFiles_Manager::get($originalName);
                
                if( $files ) {
                    $file->addError( $this->view->translate("File already exists"));
                    $form_isValid = false;
                }

                if( $form_isValid ) {

                    $uploadName = $_FILES['file']['tmp_name'];
                    $arq_tmp = $class['directory'] . "/tmp/" . $originalName;
                    $arq_dst = $class['directory'] . "/" . $originalName;
                    $arq_bkp = $class['directory'] . "/backup/" . $originalName;
                    $arq_orig = $class['directory'] . "/" . $originalName;

                    exec("mv $uploadName $arq_tmp");

                    if( $_POST['gsm'] ) {
                        $fileNe = basename($arq_dst, '.wav');
                        exec( "sox $arq_tmp -r 8000 {$fileNe}.gsm" );
                        $originalName = basename($originalName, '.wav') . ".gsm";
                        
                    }else{
                        exec( "sox $arq_tmp -r 8000 -c 1 -e signed-integer -b 16 $arq_dst" );
                    }

                    if( file_exists($arq_dst) || file_exists($fileNe) ) {
                        Snep_SoundFiles_Manager::
                                    addClassFile(array('arquivo'   => $originalName,
                                                       'descricao' => $dados['description'],
                                                       'data'      => new Zend_Db_Expr('NOW()'),
                                                       'tipo'      => 'MOH',
                                                       'secao'     => $dados['section'] ) );
                    }
                    
                    $this->_redirect( $this->getRequest()->getControllerName() . "/file/class/$className/"  );
                }
                
       }

       $this->view->form = $form;

    }

    public function editfileAction() {

       $fileName = $this->_request->getParam('file');
       $class = $this->_request->getParam('class');

       $className = Snep_SoundFiles_Manager::getClasse($class);
       $files = Snep_SoundFiles_Manager::getClassFiles($className);
       $_files = array('arquivo' => '', 'descricao' => '',
                       'tipo'    => '', 'secao' => $class, 'full' => '');

       foreach($files as $name => $file) {
           if($name == $fileName) {
               if( isset( $file['arquivo'] ) ) {
                    $_files = $file;      
               }
           }
       }

       $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Music on Hold Sessions"),
            $this->view->translate("Edit File"),
            $className
        ));

       $form = new Snep_Form( new Zend_Config_Xml( "modules/default/formssound_files.xml" ) );
       $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . "/editfile/file/$fileName/class/$class");

       $file = new Zend_Form_Element_File('file');
       $file->setLabel( $this->view->translate('Sound File'))
             ->addValidator(new Zend_Validate_File_Extension(array('wav', 'gsm')))
             ->removeDecorator('DtDdWrapper')
             ->setIgnore(true);

       $form->addElement($file);

       $form->getElement('description')->setValue($_files['descricao']);
       
       $section = new Zend_Form_Element_Hidden('section');
       $section->setValue( $_files['secao'] );
       $form->addElement( $section );

       $originalName = new Zend_Form_Element_Hidden('originalName');
       $originalName->setValue( $fileName );
       $form->addElement( $originalName );

       $originalPath = new Zend_Form_Element_Hidden('originalPath');
       $originalPath->setValue( $_files['full'] );
       $form->addElement( $originalPath );


       $form->removeElement('type');

       if($this->_request->getPost()) {
           
            $class = Snep_SoundFiles_Manager::getClasse($_POST['section']);

            $form_isValid = $form->isValid($_POST);
            $dados = $this->_request->getParams();

            $invalid = array('â','ã','á','à','ẽ','é','è','ê','í','ì','ó','õ','ò','ú','ù','ç'," ",'@','!');
            $valid = array('a','a','a','a','e','e','e','e','i','i','o','o','o','u','u','c',"_",'_','_');

            if( $_FILES['file']['size'] > 0 ) {

                $oldName = $_POST['originalName'];

                $originalName = str_replace( $invalid, $valid, $_FILES['file']['name'] );
                $files = Snep_SoundFiles_Manager::get($originalName);

                if( $files ) {
                    $file->addError( $this->view->translate("The file already exists"));
                    $form_isValid = false;
                }
                if( $form_isValid ) {
                    $uploadName = $_FILES['file']['tmp_name'];
                    $arq_tmp = $class['directory'] . "/tmp/" . $originalName;
                    $arq_dst = $class['directory'] . "/" . $originalName;
                    $arq_bkp = $class['directory'] . "/backup/" . $originalName;
                    $arq_orig = $class['directory'] . "/" . $originalName;

                    exec("mv $uploadName $arq_tmp");

                    $fileNe = basename($arq_dst, 'wav');

                    if( $_POST['gsm'] ) {
                        exec( "sox $arq_tmp -r 8000 {$fileNe}.gsm" );
                        $exists = file_exists( $fileNe ."gsm");
                    }else{
                        exec( "sox $arq_tmp -r 8000 -c 1 -e signed-integer -b 16 $arq_dst" );
                        $exists = file_exists( $arq_dst);
                    }
                }

                if($exists) {
                    exec("rm -f {$_POST['originalPath']}");

                    Snep_SoundFiles_Manager::remove($oldName);
                    Snep_SoundFiles_Manager::
                            add(array('arquivo'   => $originalName,
                                      'descricao' => $dados['description'],
                                      'data'      => new Zend_Db_Expr('NOW()'),
                                      'tipo'      => 'MOH',
                                      'secao'     => $dados['section'] ) );
                }

            }else{
                $originalName = $_POST['originalName'];
                Snep_SoundFiles_Manager::
                        editClassFile(array('arquivo'   => $originalName,
                                            'descricao' => $dados['description'],
                                            'data'      => new Zend_Db_Expr('NOW()'),
                                            'tipo'      => 'MOH',
                                            'secao'     => $dados['section'] ) );

            }

            
            
            $this->_redirect( $this->getRequest()->getControllerName() . "/file/class/{$className['name']}/"  );
       
       }

       $this->view->form = $form;
    }


    public function removefileAction() {

         $file = $this->_request->getParam('file');
         $class = $this->_request->getParam('class');

         $className = Snep_SoundFiles_Manager::getClasse($class);       
         $files = Snep_SoundFiles_Manager::getClassFiles($className);

         foreach($files as $name =>$path) {
             if($file == $name) {

                 exec("rm {$path['full']} ");

                 if(! file_exists( $path['full'] ) ) {
                    Snep_SoundFiles_Manager::remove($name, $path['secao']);
                 }
             }
         }

         $this->_redirect( $this->getRequest()->getControllerName() . "/file/class/$class");
    }
    
}