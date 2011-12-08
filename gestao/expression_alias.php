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
ver_permissao(12);

class ExprAliasController extends Snep_Controller {

    public function __construct() {
        $smarty = Zend_Registry::get('smarty');
        $extra_headers = <<<HEAD
<script src="../includes/javascript/scriptaculous/lib/prototype.js" type="text/javascript"></script>
<script src="../includes/javascript/snep.js"></script>
HEAD;
        $smarty->assign('EXTRA_HEADERS',$extra_headers);
    }

    public function indexAction() {
        $smarty = Zend_Registry::get('smarty');
        $LANG = Zend_Registry::get('lang');
        
        $titulo = $LANG['menu_rules']." » Alias Expressão Regular";

        $aliases = PBX_ExpressionAliases::getInstance();

        $smarty->assign('ALIASES',$aliases->getAll());

        $opcoes = array("name"=>"Nome");
        
        // Variaveis Relativas a Barra de Filtro/Botao Incluir
        $smarty->assign ('view_filter',false);
        $smarty->assign ('view_include_buttom',True);
        $smarty->assign ('OPCOES', $opcoes);
        $smarty->assign ('array_include_buttom',array("url" => "../gestao/expression_alias.php?action=add", "display"  => "Incluir Alias"));


        display_template("rel_expression_alias.tpl",$smarty,$titulo);
    }

    public function addAction() {
        $smarty = Zend_Registry::get('smarty');
        $LANG = Zend_Registry::get('lang');
        $smarty->assign('ACAO',"add");
        $smarty->assign('expressions',"exprObj.addItem(1);");
        $titulo = $LANG['menu_rules']." -> Alias Expressão Regular -> ".$LANG['include'];


        if($_POST) {
            $expression = array(
                "name" => $_POST['name'],
                "expressions" => explode(",",$_POST['exprValue'])
            );
            $aliasesPersistency = PBX_ExpressionAliases::getInstance();

 		if ($_POST['name']== "" || $_POST['exprValue']== "") { 
			display_error('Voce deve preencher todos os campos.', true); 
			exit; 
		}

            try {
                $aliasesPersistency->register($expression);
            }
            catch(Exception $ex) {
                display_error($ex->getMessage(), true);
            }
            $this->__redirect("./expression_alias.php");
        }

        display_template("expression_alias.tpl",$smarty,$titulo);
    }

    public function editAction() {
        $smarty = Zend_Registry::get('smarty');
        $LANG = Zend_Registry::get('lang');

        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $smarty->assign('ACAO',"edit&id=$id");
        $aliasesPersistency = PBX_ExpressionAliases::getInstance();

        if($_POST) {
            $expression = array(
                "id" => $id,
                "name" => $_POST['name'],
                "expressions" => explode(",",$_POST['exprValue'])
            );

            try {
                $aliasesPersistency->update($expression);
            }
            catch(Exception $ex) {
                display_error($ex->getMessage(), true);
            }
            $this->__redirect("./expression_alias.php");
        }
        
        $alias = $aliasesPersistency->get($id);

        $smarty->assign("alias", $alias);

        $titulo = $LANG['menu_rules']." -> Alias Expressão Regular -> Editar Alias {$alias['id']}";
        display_template("expression_alias.tpl",$smarty,$titulo);
    }

    public function deleteAction() {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        $aliasesPersistency = PBX_ExpressionAliases::getInstance();
        $alias = $aliasesPersistency->get($id);
        if($alias !== null) {
            $aliasesPersistency->delete($id);
        }
        $this->__redirect("./expression_alias.php");
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : null;
$controller = new ExprAliasController();

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
    default:
        $controller->indexAction();
}
