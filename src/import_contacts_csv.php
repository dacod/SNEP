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

class ImportContactsController {

    protected function __redirect($url) {
        header("HTTP/1.1 303 See Other");
        header("Location: $url");
        exit(0);
    }

    public function indexAction() {
        $smarty = Zend_Registry::get('smarty');

        $titulo = "Cadastro » Contatos » Importar CSV";
        display_template("contacts/import/csv/index.tpl",$smarty,$titulo);
    }

    public function processAction() {
        if(isset($_FILES['contacts_csv'])) {
            $file_info = $_FILES['contacts_csv'];
            if($file_info['type'] !== "text/csv") {
                display_error("O arquivo precisa ser do tipo CSV", true);
            }
            else {
                $handle = fopen($file_info['tmp_name'], "r");
                if ($handle) {
                    $replace_from = array("\n",'"');
                    $replace_to = array("","");
                    $csv = array();
                    $row_number = 2;
                    $first_row = explode(",",str_replace($replace_from,$replace_to,fgets($handle, 4096)));
                    $column_count = count($first_row);
                    $csv[] = $first_row;
                    
                    while (!feof($handle)) {
                        $line = fgets($handle, 4096);
                        if(strpos($line, ",")) {
                            $row = explode(",",str_replace($replace_from,$replace_to,$line));
                            if(count($row) != $column_count) {
                                display_error("Número inválido de colunas na linha: $row_number", true);
                            }
                            $csv[] = $row;
                            $row_number++;
                        }
                    }
                    fclose($handle);
                }

                // Colocando na SESSION do usuário o endereco do arquivo
                $_SESSION['contacts_csv'] = $csv;

                $smarty = Zend_Registry::get('smarty');

                $smarty->assign("fields", count($csv[0]));
                $smarty->assign("sample_data", array_slice($csv, 0, 5));

                $standard_fields = array(
                    "discard" => "Descartar Coluna",
                    "name" => "Nome",
                    "phone_1" => "Telefone",
                    "cell_1" => "Celular",
                    "address" => "Endereço",
                    "city" => "Cidade",
                    "state" => "Estado",
                    "cep" => "CEP"
                );
                $smarty->assign("contacts_fields", $standard_fields);

                /* Variáveis de ambiente do Form */
                $select = "SELECT id, name FROM contacts_group";
                $db = Zend_Registry::get("db");
                $raw_groups = $db->query($select)->fetchAll();

                $groups = array();
                foreach ($raw_groups as $row) {
                    $groups[$row["id"]] = $row["name"];
                }

                $smarty->assign('GROUPS', $groups);

                $titulo = "Contatos -> Importar CSV » Relacionar Campos";
                display_template("contacts/import/csv/process.tpl",$smarty,$titulo);
                
            }
        }
        else {
            display_error("É necessário um arquivo para processar", true);
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
$controller = new ImportContactsController();

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
