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



class ExtensionsController extends Zend_Controller_Action {

    public function indexAction() {
       
        $this->view->breadcrumb = $this->view->translate("Cadastro » Ramais");
        
        $db = Zend_Registry::get('db');
        $select = $db->select()->from("peers",array(
                "id" => "id",
                "exten" => "name",
                "name" => "callerid",
                "channel" => "canal",
                "group"
        ));
        $select->where("peer_type='R'");

        if( $this->_request->getPost('filtro') ) {
            $field = mysql_escape_string( $this->_request->getPost('campo') );
            $query = mysql_escape_string( $this->_request->getPost('filtro') );
            $select->where("`$field` like '%$query%'");
        }

        $page = $this->_request->getParam('page');
        $this->view->page = ( isset($page) && is_numeric($page)  ? $page : 1 );

        $this->view->filtro = $this->_request->getParam('filtro');
        
        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $paginator->setCurrentPageNumber( $this->view->page );
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $this->view->extensions = $paginator;
        $this->view->pages = $paginator->getPages();
        $this->view->PAGE_URL = "/snep/index.php/extensions/index/";

        $opcoes = array("name"     => $this->view->translate("Ramal"),
                        "callerid" => $this->view->translate("Nome"),
                        "group"    => $this->view->translate("Grupo")
        );

        // Formulário de filtro.
        $config_file = "./default/forms/filter.xml";
        $config = new Zend_Config_Xml($config_file, null, true);

        $filter = new Zend_Form( $config->filter );
        $filter->setAction( $this->getFrontController()->getBaseUrl() . '/extensions/index' );

        $filter_value = $filter->getElement('filtro');
        $filter_value->setValue( $this->_request->getPost('filtro') );

        $submit = new Zend_Form_Element_Submit("submit", array("label" => $this->view->translate("Procurar")));
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator('HtmlTag', array('tag' => 'dd'));

        // Botão Lista Completa
        $reset = new Zend_Form_Element_Button("buttom", array("label" => $this->view->translate("Lista Completa") ) );
        $reset->setAttrib("onclick", "location.href='{$this->getFrontController()->getBaseUrl()}/{$this->getRequest()->getControllerName()}/index/page/$page'");
        $reset->removeDecorator('DtDdWrapper');
        $reset->addDecorator('HtmlTag', array('tag' => 'dd'));    

        // Define elementos do 'campo' select
        $campo = $filter->getElement('campo');
        $campo->setMultiOptions( $opcoes );

        $filtro = $filter->getElement('filtro');
        $filtro->setValue( $this->_request->getParam('filtro') );

        $filter->addElement( $submit);
        $filter->addElement( $reset );

        $this->view->form_filter = $filter;
        $this->view->filter = array( array("url" => "/snep/src/extensions.php?action=multiadd",
                                           "display" => "Incluir Ramais",
                                           "css" => "includes"),
                                     array("url" => "/snep/src/extensions.php?action=add",
                                           "display" => "Incluir Ramal",
                                           "css" => "include") );
        
    }

    /**
     * Redireciona para a tela antiga de cadastro de ramais.
     *
     * @see ExtensionsController::editAction()
     */
    public function addAction() {
        $this->__redirect("./ramais.php");
    }

    public function multiAddAction() {
        $this->__redirect("./ramais_varios.php");
    }

    /**
     * Redireciona a edição do ramal para a tela antiga de edição.
     *
     * A remoção da tela antiga se dará no momento que o formulário de edição
     * e adição de ramais for simplificado.
     *
     * @TODO usar Zend_Form para criação do formulário de cadastro e ediçao de
     * ramais.
     */
    public function editAction() {

        $exten = isset($_GET['id']) ? $_GET['id'] : null;

        // Código legado espera id da table peers e não numero do ramal.
        $db = Zend_Registry::get("db");
        $select = $db->select()->from("peers",array("id"))->where("name='$exten' AND peer_type='R'");
        $result = $select->query()->fetchObject();

        $this->__redirect("./ramais.php?acao=alterar&id=$result->id");
    }


    public function deleteAction() {

        $LANG = Zend_Registry::get('lang');
        $db = Zend_Registry::get('db');

        $id = isset($_GET['id']) ? $_GET['id'] : false;
        if (!$id) {
            display_error($LANG['msg_notselect'],true);
            exit ;
        }

        // Fazendo procura por referencia a esse ramal em regras de negócio.
        $rules_query = "SELECT id, `desc` FROM regras_negocio WHERE origem LIKE '%R:$id%' OR destino LIKE '%R:$id%'";
        $regras = $db->query($rules_query)->fetchAll();

        $rules_query = "SELECT rule.id, rule.desc FROM regras_negocio as rule, regras_negocio_actions_config as rconf WHERE (rconf.regra_id = rule.id AND rconf.value = '$id')";
        $regras = array_merge($regras, $db->query($rules_query)->fetchAll());

        if(count($regras) > 0) {
            $msg = $LANG['extension_conflict_in_rules'].":<br />\n";
            foreach ($regras as $regra) {
                $msg .= $regra['id'] . " - " . $regra['desc'] . "<br />\n";
            }
            display_error($msg,true);
            exit(1);
        }
        $sql = "DELETE FROM peers WHERE name='".$id."'";

        $db->beginTransaction();

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $sql = "delete from voicemail_users where customer_id='$id'";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        try {
            $db->commit();
            grava_conf();
        } catch (PDOException $e) {
            $db->rollBack();
            display_error($LANG['error'].$e->getMessage(),true) ;
        }
        
        $this->_redirect("./default/extensions/");
    }



}
