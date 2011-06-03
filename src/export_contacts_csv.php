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
ver_permissao(59);

class ExportContactsController {

    protected function __redirect($url) {
        header("HTTP/1.1 303 See Other");
        header("Location: $url");
        exit(0);
    }

    public function indexAction() {
        $smarty = Zend_Registry::get('smarty');
        
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from('contacts_group');
        $stmt = $db->query($select);
        $_grupos = $stmt->fetchAll();

        $grupos = array('all' => 'Todos Grupos');
        foreach($_grupos as $grupo) {
            $grupos[$grupo['id']] = $grupo['name'];            
        }

        $smarty->assign('GRUPOS', $grupos);

        $titulo = "Cadastro » Contatos » Exportar CSV";
        display_template("contacts/export/csv/index.tpl",$smarty,$titulo);
    }

    public function processAction() {

        $grupo = $_POST['grupo'];
        $db = Zend_Registry::get('db');

        if($grupo == 'all') {

            $select = $db->select()
                    ->from('contacts_group', array('id'));
            $stmt = $db->query($select);
            $_groupId = $stmt->fetchAll();
            $groupId = array();
            foreach($_groupId as $id) {
                $groupId[$id['id']] = $id['id'];
            }
           

        }else{
            $groupId = array($grupo);
        }
      
        $select = $db->select()
                ->from('contacts_names', array('id', 'name as nome', 'address as endereço', 'city as cidade', 'state as estado', 'phone_1 as fone', 'cell_1 as celular' ))
                ->from('contacts_group', array('name as grupo'))
                ->where('contacts_names.group = contacts_group.id')
                ->where("contacts_names.group  IN (?)", $groupId)
                ->order('contacts_names.group');

        $stmt = $db->query($select);
        $contacts = $stmt->fetchAll();
        if(count($contacts) < 1 ) {
             display_error("Nenhum registro encontrado.", true);
        }else{
            $dateNow = new Zend_Date();
            $fileName = 'contacts_csv_' . $dateNow->toString(" dd-MM-yyyy_hh'h'mm'm' ") . '.csv';
            $csv_output = Snep_Csv::generate($contacts, true);
             header('Content-type: application/octet-stream');
             header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo $csv_output;

        }
    }

    public function finishAction() {
        if($_POST) {
            $group = isset($_POST['group']) ? $_POST['group'] : null;
            $assoc = isset($_POST['assoc']) ? $_POST['assoc'] : null;

            if($assoc === null || $group === null) {
                display_error("Parâmetros Inválidos", true);
            }

            $csv = $_SESSION['contacts_csv'];

            if( isset($_POST['discard_first_row']) && $_POST['discard_first_row'] == "on" ) {
                array_shift($csv);
            }
            //unset($_SESSION['contacts_csv']);

            $db = Zend_Registry::get("db");
            $db->beginTransaction();

            $select = "SELECT id FROM contacts_names ORDER BY id DESC LIMIT 1";
            $contact_id = $db->query($select)->fetchObject();
            $id = $contact_id->id + 1;

            foreach ($csv as $key => $row) {
                $new_row = array(
                    "id" => $id,
                    "group" => $group
                );

                foreach ($assoc as $column_key => $column) {
                    if($column !== "discard") {
                        $new_row[$column] = $row[$column_key];
                    }
                }
                $db->insert("contacts_names", $new_row);
                $id++;
            }

            try {
                $db->commit();
            }
            catch(Exception $ex) {
                $db->rollback();
                throw $ex;
            }

            $this->__redirect(Zend_Registry::get("config")->system->path->web . "/index.php/contacts/");
        }
        else {
            display_error("ERRO INTERNO", true);
        }
    }

}

$action = isset($_GET['action']) ? $_GET['action'] : "index";
$controller = new ExportContactsController();

switch ($action) {
    case "process":
        $controller->processAction();
        break;
    case "finish":
        $controller->finishAction();
        break;
    default:
        $controller->indexAction();
}
