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

require_once("../includes/verifica.php");  
require_once("../configs/config.php");

ver_permissao(114);

class InspectorController extends Snep_Controller {

    public function indexAction() {

        // cria objeto da classe Snep_Inspector
        $obj = new Snep_Inspector();

        // Pega array de informacoes gerados no construtor
        $inspect = $obj->getInspects();

        // Pega instancia do Smarty do Zend_Registry
        $smarty = Zend_Registry::get('smarty');

        // Instancia variavel do smarty
        $smarty->assign('inspects', $inspect);

        // Titulo da rotina
        $titulo = "Configuração » Diagnóstico do Sistema";

        // Chama template da rotina
        display_template("inspector/index.tpl", $smarty, $titulo);
    }
}

$controller = new InspectorController();
$controller->indexAction();

