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
 * controller  extensions groups.
 */
class ExtensionsGroupsController extends Zend_Controller_Action {

    /**
     *
     * @var Zend_Form
     */
    protected $form;
    /**
     *
     * @var array
     */
    protected $forms;

    public function indexAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Extension Groups")
        ));

        $db = Zend_Registry::get('db');

        $this->view->tra = array("admin" => $this->view->translate("Administrators"),
            "users" => $this->view->translate("Users"),
            "all" => $this->view->translate("All"));
        

        $select = $db->select()
                        ->from("groups", array("name", "inherit"))
                        ->where("name not in ('all','users','administrator') ");
        
        if ($this->_request->getPost('filtro')) {
            $field = mysql_escape_string($this->_request->getPost('campo'));
            $query = mysql_escape_string($this->_request->getPost('filtro'));
            $select->where("`$field` like '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page) ? $page : 1 );

        $this->view->filtro = $this->_request->getParam('filtro');

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->extensionsgroups = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/";
        $this->view->PAGE_URL = "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/";

        $opcoes = array("name" => $this->view->translate("Name"));

        // Formulário de filtro.
        $filter = new Snep_Form_Filter();
        $filter->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $filter->setValue($this->_request->getPost('campo'));
        $filter->setFieldOptions($opcoes);
        $filter->setFieldValue($this->_request->getPost('filtro'));
        $filter->setResetUrl("{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page");

        $this->view->form_filter = $filter;
        $this->view->filter = array(array("url" => "{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/add",
                "display" => $this->view->translate("Add Extension Group"),
                "css" => "include"),
        );
    }

    /**
     * Adds a group and their extensions in the database.
     * Adiciona um grupo e seus ramais no banco de dados.
     */
    public function addAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Extension Groups"),
            $this->view->translate("Add Extension Groups"),
        ));

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $form_xml = new Zend_Config_Xml("modules/default/forms/extensions_groups.xml");
        $form = new Snep_Form($form_xml);
        $form->setAction($this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName().'/add');

        $form->getElement('name')
             ->setLabel($this->view->translate('Name'));

        $form->getElement('type')
            ->setRequired(true)
             ->setLabel($this->view->translate('Type'))
             ->setMultiOptions(array('administrator' => $this->view->translate('Administrator'),
                                     'users' => $this->view->translate('User')) );
        
        try {
            $extensionsAllGroup = Snep_ExtensionsGroups_Manager::getExtensionsAll();

        }catch(Exception $e) {

            display_error($LANG['error'].$e->getMessage(),true);
        }

        $extensions = array();
        foreach($extensionsAllGroup as $key => $val) {

            $extensions[$val['id']] = $val['name'] ;
        }

        $this->view->objSelectBox = "extensions";

        $form->setSelectBox( $this->view->objSelectBox, $this->view->translate('Extensions'), $extensions);

        if ($this->getRequest()->getPost()) {

            $form_isValid = $form->isValid($_POST);

            $dados = $this->_request->getParams();

            if ($form_isValid) {

                $group = array('name' => $dados['name'],
                                         'inherit' => $dados['type']
                                        );

                $this->view->group = Snep_ExtensionsGroups_Manager::addGroup($group);

                if( $dados['box_add'] && $this->view->group ) {

                    foreach($dados['box_add'] as $id => $extensions) {

                        $extensionsGroup = array    ( 'group' => $dados['name'],
                                                      'extensions' => $extensions
                                                    );

                        $this->view->extensions = Snep_ExtensionsGroups_Manager::addExtensionsGroup($extensionsGroup);

                    }
                }

                $this->_redirect("/".$this->getRequest()->getControllerName()."/");
            }
        }

        $this->view->form = $form;

    }

    public function editAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Extension Groups"),
            $this->view->translate("Edit Extension Groups"),
        ));

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $xml = new Zend_Config_Xml( "modules/default/forms/extensions_groups.xml" );
        $form = new Snep_Form( $xml );
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/edit');

        $id = $this->_request->getParam('id');

        $group = Snep_ExtensionsGroups_Manager::getGroup($id);

        $groupId = $form->getElement('id')->setValue($id);
        $groupName = $form->getElement('name')->setValue($group['name'])->setLabel($this->view->translate('Name'));;

        $groupType = $form->getElement('type');
        $groupType ->setRequired(true)
             ->setLabel($this->view->translate('Type'))
             ->setMultiOptions(array('administrator' => $this->view->translate('Administrator'),
                                     'users' => $this->view->translate('User')) )
             ->setValue($group['inherit']);

        $groupExtensions = array();
        foreach(Snep_ExtensionsGroups_Manager::getExtensionsOnlyGroup($id) as $data) {

            $groupExtensions[$data['name']] = "{$data['name']}";
        }

        $groupAllExtensions = array();
        foreach(Snep_ExtensionsGroups_Manager::getExtensionsAll() as $data) {

                if( ! isset($groupExtensions[$data['name']]) ) {

                    $groupAllExtensions[$data['name']] = "{$data['name']}";
                }
        }

        $this->view->objSelectBox = "extensions";

        $form->setSelectBox( $this->view->objSelectBox, $this->view->translate('Extensions'), $groupAllExtensions, $groupExtensions);

        if($this->_request->getPost()) {

                $form_isValid = $form->isValid($_POST);

                $dados = $this->_request->getParams();
                $idGroup = $dados['id'];

                $this->view->group = Snep_ExtensionsGroups_Manager::editGroup(array('name' => $dados['name'],'type' => $dados['type'],'id' => $idGroup));

                foreach(Snep_ExtensionsGroups_Manager::getExtensionsOnlyGroup($id) as $extensionsGroup) {

                    Snep_ExtensionsGroups_Manager::addExtensionsGroup(array('extensions' => $extensionsGroup['name'], 'group' => 'all'));
                }

                if( $dados['box_add'] ) {

                    foreach($dados['box_add'] as $id => $dados['name']) {

                        $this->view->extensions = Snep_ExtensionsGroups_Manager::addExtensionsGroup(array('extensions' => $dados['name'], 'group' => $idGroup));
                    }
                }

                $this->_redirect( $this->getRequest()->getControllerName() );
        }

        $this->view->form = $form;
    }


    /**
     * Remove a Extensions Group
     */
    public function deleteAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Extension Groups"),
            $this->view->translate("Delete Extension Groups"),
        ));


        $id = $this->_request->getParam('id');
        $confirm = $this->_request->getParam('confirm');

        if( $confirm == 1 ) {
             Snep_ExtensionsGroups_Manager::delete($id);
             $this->_redirect( $this->getRequest()->getControllerName() );
        }

        $extensions = Snep_ExtensionsGroups_Manager::getExtensionsGroup($id);

        if( count($extensions) > 0 )  {
            $this->_redirect($this->getRequest()->getControllerName().'/migration/id/'. $id);

        }else{

            $this->view->message = $this->view->translate("The extension group will be deleted. Are you sure?.");
            $form = new Snep_Form();
            $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/delete/id/'.$id.'/confirm/1');

            $form->getElement('submit')->setLabel($this->view->translate('Yes'));

            $this->view->form = $form;
        }
    }

    /**
     * Migrate extensions to other Extensions Group
     */
    public function migrationAction() {

        $this->view->breadcrumb = Snep_Breadcrumb::renderPath(array(
            $this->view->translate("Manage"),
            $this->view->translate("Extension Groups"),
            $this->view->translate("Migrate Extension Group"),
        ));


        $id = $this->_request->getParam('id');

        $_allGroups = Snep_ExtensionsGroups_Manager::getAllGroup();

        foreach($_allGroups as $group) {

            if($group['name'] != $id){

                $allGroups[$group['name']] = $group['name'];
            }
        }

        Zend_Registry::set('cancel_url', $this->getFrontController()->getBaseUrl() . '/' . $this->getRequest()->getControllerName() . '/index');
        $form = new Snep_Form();
        $form->setAction( $this->getFrontController()->getBaseUrl() .'/'. $this->getRequest()->getControllerName() . '/migration/');

        if(isset($allGroups)) {

            $groupSelect = new Zend_Form_Element_Select('select');
            $groupSelect->setMultiOptions( $allGroups );
            $groupSelect->setLabel( $this->view->translate( $this->view->translate("New Group")  ) );
            $form->addElement($groupSelect);
            $this->view->message = $this->view->translate("This groups has extensions associated. Select another group for these extensions. ");

        }else{

            $groupName = new Zend_Form_Element_Text('new_group');
            $groupName->setLabel( $this->view->translate( $this->view->translate("New Group")  ) );
            $form->addElement($groupName);
            $this->view->message = $this->view->translate("This is the only group and it has extensions associated. You can migrate these extensions to a new group.");
        }

        $id_exclude = new Zend_Form_Element_Hidden("id");
        $id_exclude->setValue($id);

        $form->addElement($id_exclude);

        if($this->_request->getPost()) {

                if( isset( $_POST['select'] ) ) {

                    $toGroup = $_POST['select'];

                }else{

                    $new_group = array('group' => $_POST['new_group']);
                    $toGroup = Snep_ExtensionsGroups_Manager::addGroup( $new_group );
                }

                $extensions = Snep_ExtensionsGroups_Manager::getExtensionsOnlyGroup($id);

                foreach($extensions as $extension) {
                    Snep_ExtensionsGroups_Manager::addExtensionsGroup(array('extensions' => $extension['name'], 'group' => $toGroup));
                }

                Snep_ExtensionsGroups_Manager::delete($id);

                $this->_redirect( $this->getRequest()->getControllerName() );
        }

        $this->view->form = $form;
    }
}
