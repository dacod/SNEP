<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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

require_once("../includes/verifica.php");  
require_once("../configs/config.php");

ver_permissao(16);

class ExtensionsController extends Snep_Controller {

    /**
     * Lista todos os ramais cadastrados no sistema.
     */
    public function indexAction() {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from("peers",array(
                "id" => "id",
                "exten" => "name",
                "name" => "callerid",
                "channel" => "canal",                
                "group"
        ));
        $select->where("peer_type='R'");

        if($_POST) {
            $field = mysql_escape_string($_POST['field_filter']);
            $query = mysql_escape_string($_POST['text_filter']);
            $select->where("`$field` like '%$query%'");
        }

        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
        $_SESSION['pagina'] = $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(Zend_Registry::get('config')->ambiente->linelimit);

        $smarty = Zend_Registry::get('smarty');

        $smarty->assign("extensions",$paginator);
        $smarty->assign("pages", $paginator->getPages());
        $smarty->assign("PAGE_URL", "./extensions.php?");

        $LANG = Zend_Registry::get('lang');
        $opcoes = array(
            "name" => $LANG['ramal'],
            "callerid" => $LANG['name'],
            "group" => $LANG['group']
        );

        $smarty->assign ('view_filter',True);
        $smarty->assign ('view_include_buttom',True);
        $smarty->assign ('view_include_buttom2',True);
        $smarty->assign ('PROTOTYPE', True);
        $smarty->assign ('OPCOES', $opcoes);
        $smarty->assign ('array_include_buttom',array("url" => "./extensions.php?action=add", "display"  => $LANG['include']." ".$LANG['ramal'], "peer_type"=>"R"));
        $smarty->assign ('array_include_buttom2',array("url" => "./extensions.php?action=multiadd", "display"  => $LANG['include']." ".$LANG['menu_ramais'], "peer_type"=>"R"));

        $titulo = "Cadastro » Ramais";
        display_template("extensions/index.tpl", $smarty, $titulo);
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
     */
    public function editAction() {
        $exten = isset($_GET['id']) ? $_GET['id'] : null;
        
        // Código legado espera id da table peers e não numero do ramal.
        $db = Zend_Registry::get("db");
        $select = $db->select()->from("peers",array("id"))->where("name='$exten' AND peer_type='R'");
        $result = $select->query()->fetchObject();

        $this->__redirect("./ramais.php?acao=alterar&id=$result->id");
    }

    /**
     * Exclui um ramal do sistema.
     */
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
        $this->__redirect("../index.php/extensions/");
    }

}

$controller = new ExtensionsController();

$action = isset($_GET['action']) ? $_GET['action'] : null;

switch($action) {
    case "add":
        $controller->addAction();
        break;
    case "edit":
        $controller->editAction();
        break;
    case "delete":
        $controller->deleteAction();
        break;
    case "multiadd":
        $controller->multiAddAction();
        break;
    default:
        $controller->indexAction();
}
