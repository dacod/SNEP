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

ver_permissao(49);

/**
 * Controlador para rotas de ligações.
 */
final class RouteController {

    /**
     * Formulários com as informações preenchidas para reimpressão em caso de
     * erro de validação.
     *
     * @var array
     */
    private $forms = null;

    /**
     * Define no smarty os valores padrões de alguns campos e preenche as
     * comboboxes.
     */
    private function populateCommomFields() {
        $db     = Zend_Registry::get("db");
        $smarty = Zend_Registry::get("smarty");

        $extra_headers = <<<HEAD
<link rel="stylesheet" href="../css/agi_rules.css" type="text/css" />
<script src="../includes/javascript/scriptaculous/lib/prototype.js" type="text/javascript"></script>
<script src="../includes/javascript/scriptaculous/src/scriptaculous.js" type="text/javascript"></script>
<script src="../includes/javascript/snep.js"></script>
<script src="../includes/javascript/agi_rules.js" type="text/javascript"></script>
HEAD;
        $smarty->assign('EXTRA_HEADERS',$extra_headers);

        /* Monta lista de  Grupos de  Usuarios */
        $groups = new Snep_GruposRamais();
        $groups = $groups->getAll();

        foreach($groups as $id => $val) {
            switch($val['name']) {
                case 'all':
                    $grupos[$val['name']] = 'Todos';
                    break;
                case 'admin':
                    $grupos[$val['name']] = 'Administrador';
                    break;
                case 'users':
                    $grupos[$val['name']] = 'Usuarios';
                    break;
                default:
                    $grupos[$val['name']] = $val['name'];
            }
        }

        $smarty = Zend_Registry::get('smarty');
        $smarty->assign('OPCOES_GRUPOS', $grupos);

        $select = "SELECT id, name FROM contacts_group";
        $raw_groups = $db->query($select)->fetchAll();

        $groups = array();
        foreach ($raw_groups as $row) {
            $groups[$row["id"]] = $row["name"];
        }

        $smarty->assign('OPCOES_CONTACTS_GROUPS', $groups);

        /* Lista de Troncos para interface */
        $trunks = array();
        foreach (PBX_Trunks::getAll() as $tronco) {
            $trunks[] = array(
                    'id'   =>$tronco->getId(),
                    'name' => $tronco->getName()
            );

        }
        $smarty->assign('OPCOES_TRONCOS', $trunks);

        /* Lista de Aliases para Expressão Regular */
        $expressions = array();
        foreach (PBX_ExpressionAliases::getInstance()->getAll() as $expression) {
            $expressions[] = array(
                    'id'   => $expression["id"],
                    'name' => $expression["name"]
            );

        }
        $smarty->assign('OPCOES_ALIAS', $expressions);

        $actions = PBX_Rule_Actions::getInstance();
        $installed_actions = array();
        foreach ($actions->getInstalledActions() as $action) {
            $action_instance = new $action();
            $installed_actions[$action] = $action_instance->getName();
        }
        asort($installed_actions);
        $smarty->assign("ACTIONS", $installed_actions);

        $smarty->assign('OPCOES_ORDER',range(0, 5));
    }

    /**
     * Popula os campos do smarty a partir de uma Regra.
     *
     * @param PBX_Rule $rule
     */
    private function populateFromRule(PBX_Rule $rule, $copia) {
        $smarty = Zend_Registry::get('smarty');
        $srcList = $rule->getSrcList();
        $src = "origObj.addItem(" . count($srcList) . ");";
        foreach ($srcList as $index => $_src) {
            $src .= "origObj.widgets[$index].type='{$_src['type']}';\n";
            $src .= "origObj.widgets[$index].value='{$_src['value']}';\n";
        }

        $dstList = $rule->getDstList();
        $dst =  "dstObj.addItem(" . count($dstList) . ");";
        foreach ($dstList as $index => $_dst) {
            $dst .=  "dstObj.widgets[$index].type='{$_dst['type']}';\n";
            $dst .=  "dstObj.widgets[$index].value='{$_dst['value']}';\n";
        }

        $timeList = $rule->getValidTimeList();
        $time = "timeObj.addItem(" . count($timeList) . ");";
        foreach ($timeList as $index => $_time) {
            $_time = explode('-', $_time);
            $time .=  "timeObj.widgets[$index].startTime='{$_time[0]}';\n";
            $time .=  "timeObj.widgets[$index].endTime='{$_time[1]}';\n";
        }

        // Tratamento do horario da regra
        $horario = $rule->getValidTimeList();
        $data = explode("-", $horario['0']);

        $smarty->assign('id', $rule->getId());

        $smarty->assign('dt_agirules',array("dst"=> $dst,
                "src"=> $src,
                "time"=>$time,
                "record" => $rule->isRecording(),
                "descricao" => $copia.$rule->getDesc(),
                "prioridade" => $rule->getPriority(),
                "ordem => 0"));
        $listaDiasSemana = $rule->getValidWeekDays();
        $smarty->assign('weekDays',array(
                "sun" => in_array("sun", $listaDiasSemana),
                "mon" => in_array("mon", $listaDiasSemana),
                "tue" => in_array("tue", $listaDiasSemana),
                "wed" => in_array("wed", $listaDiasSemana),
                "thu" => in_array("thu", $listaDiasSemana),
                "fri" => in_array("fri", $listaDiasSemana),
                "sat" => in_array("sat", $listaDiasSemana)
        ));
    }

    /**
     * Valida o $_POST para campos obrigatórios das regras de negócio.
     *
     * Esse método foi feito para fazer o parse dos campos que não podem ser
     * validados automaticamente por um Zend_Form como os dados das ações das
     * regra.
     *
     * @param array $post
     * @return boolean
     */
    private function isValidPost( $post=null) {
        $post = $post === null ? $_POST : $post;

        $assert = true;
        if(!isset($post['actions_order'])) {
            display_error("Campo 'actions_order' faltando na requisição.",true);
        }

        parse_str($post['actions_order'], $actions_order);
        $forms = array();
        foreach ($actions_order['actions_list'] as $action) {
            $real_action = new $post["action_$action"]["action_type"]();
            $action_config = new Snep_Rule_ActionConfig($real_action->getConfig());
            $action_config->setActionId("action_$action");

            $form = $action_config->getForm();

            $action_type_element = new Zend_Form_Element_Hidden("action_type");
            $action_type_element->setValue(get_class($action));
            $form->addElement($action_type_element);

            if(!$form->isValid($post["action_$action"])) {
                $assert = false;
                $status = "error";
            }
            else {
                $status = "success";
            }

            $form->setView(new Zend_View);
            $forms["action_$action"] = array(
                    "type" => $post["action_$action"]["action_type"],
                    "formData" => $form->render(),
                    "status" => $status
            );
        }

        if(!$assert) {
            $this->forms = $forms;
            return false;
        }
        else {
            $this->forms = null;
            return true;
        }
    }

    /**
     * Faz o parset de uma regra a partir do POST.
     *
     * Assume-se aqui que todos os campos são válidos.
     *
     * @param array $postData optional for ovewrite post data
     * @return PBX_Rule
     */
    private function parseRuleFromPost( $post=null ) {
        $post = $post === null ? $_POST : $post;

        $rule = new PBX_Rule();

        // Adicionando dias da semana
        $weekDays = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
        $rule->cleanValidWeekList();
        foreach ($weekDays as $day) {
            if( key_exists($day, $post) ) {
                $rule->addWeekDay($day);
            }
        }

        // Adicionando Origens
        foreach (explode(',', $post['srcValue']) as $src) {
            if(!strpos($src, ':')) {
                $rule->addSrc(array("type" => $src, "value" => ""));
            }
            else {
                $info = explode(':', $src);
                if(!is_array($info) OR count($info) != 2) {
                    throw new PBX_Exception_BadArg("Valor errado para origem da regra de negocio.");
                }

                if( $info[0] == "T" ) {
                    try {
                        PBX_Trunks::get( $info[1] );
                    }
                    catch( PBX_Exception_NotFound $ex ) {
                        display_error("Tronco inválido para origem da regra", true);
                    }
                }

                $rule->addSrc(array("type" => $info[0], "value" => $info[1]));
            }
        }

        // Adicionando Destinos
        foreach (explode(',', $post['dstValue']) as $dst) {
            if(!strpos($dst, ':')) {
                $rule->addDst(array("type" => $dst, "value" => ""));
            }
            else {
                $info = explode(':', $dst);
                if(!is_array($info) OR count($info) != 2) {
                    throw new PBX_Exception_BadArg("Valor errado para destino da regra de negocio.");
                }

                if( $info[0] == "T" ) {
                    try {
                        PBX_Trunks::get( $info[1] );
                    }
                    catch( PBX_Exception_NotFound $ex ) {
                        display_error("Tronco inválido para destino da regra", true);
                    }
                }

                $rule->addDst(array("type" => $info[0], "value" => $info[1]));
            }
        }

        // Adicionando tempos
        $rule->cleanValidTimeList();
        foreach (explode(',', $post['timeValue']) as $time_period) {
            $rule->addValidTime($time_period);
        }

        // Adiciona Descricao
        $rule->setDesc($post['descricao']);

        // Definindo ordem de gravação
        if(isset($post['record']) && $post['record']) {
            $rule->record();
        }

        // Define prioridade
        $rule->setPriority($post['prioridade']);

        if(isset($post['actions_order'])) {
            parse_str($post['actions_order'], $actions_order);
            foreach ($actions_order['actions_list'] as $action) {
                $real_action = new $post["action_$action"]["action_type"]();
                $action_config = new Snep_Rule_ActionConfig($real_action->getConfig());
                $real_action->setConfig($action_config->parseConfig($post["action_$action"]));
                $rule->addAction($real_action);
            }
        }

        return $rule;
    }

    private function parseConditions($conditions) {
        $LANG = Zend_Registry::get('lang');
        $parsed_conditions = "";
        foreach($conditions as $condition) {
            switch($condition['type']) {
                case "X" :
                    $parsed_conditions .= "{$LANG['any']}<br />";
                    break;
                case "R" :
                    $parsed_conditions .= $condition['value'] . "<br />";
                    break;
                case "RX" :
                    $parsed_conditions .= $condition['value'] . "<br />";
                    break;
                case "T" :
                    $trunk = PBX_Trunks::get($condition['value']);
                    $parsed_conditions .= "{$LANG['trunk']} {$trunk->getName()}<br />";
                    break;
                case "CG" :
                    $db = Zend_Registry::get('db');
                    $select = "SELECT id, name FROM contacts_group";
                    $raw_groups = $db->query($select)->fetchAll();

                    $groups = array();
                    foreach ($raw_groups as $row) {
                        $groups[$row["id"]] = $row["name"];
                    }
                    $parsed_conditions .= "{$LANG['contacts_group']}: {$groups[$condition['value']]}<br />";
                    break;
                case "AL":
                    $aliases = PBX_ExpressionAliases::getInstance();
                    $alias = $aliases->get((int)$condition['value']);
                    $parsed_conditions .= "Alias {$alias['name']}<br />";
                    break;
                case "G" :
                    switch ($condition['value']) {
                        case 'all':
                            $groupname = $LANG['all'];
                            break;
                        case 'users':
                            $groupname = $LANG['user'];
                            break;
                        case 'admin':
                            $groupname = $LANG['admin'];
                            break;
                        default:
                            $groupname = $condition['value'];
                            break;
                    }
                    $parsed_conditions .= "{$LANG['group']} {$groupname}<br />";
                    break;
            }
        }
        return $parsed_conditions;
    }

    public function indexAction() {

        global $LANG;
        $smarty = Zend_Registry::get('smarty');
        $db = Zend_Registry::get('db');
        $titulo = "Regras de Negócio » Rotas";

        // Opcoes de Filtros
        $opcoes = array(
                "desc" => "Descrição",
                "origem"  => $LANG['origin'],
                "destino"  => $LANG['destination']
        );

        // Se aplicar Filtro ....
        if (array_key_exists ('filtrar', $_POST) && $_POST['field_filter'] == "desc") {
            $where = "`".$_POST['field_filter']."` like '%".$_POST['text_filter']."%'";
        }
        else {
            $where = null;
        }

        // Executa acesso ao banco de Dados
        $regras = PBX_Rules::getAll($where);

        $dados = array();
        foreach ($regras as $rule) {

            $list_src = $this->parseConditions($rule->getSrcList());
            $list_dst = $this->parseConditions($rule->getDstList());

            if ($_POST['field_filter'] == "origem"){
               if(stristr($list_src, $_POST['text_filter'])){
                   $dados[] = array(
                            "codigo"    => $rule->getId(),
                            "ativa"     => $rule->isActive(),
                            "src"       => $list_src,
                            "dst"       => $list_dst,
                            "descricao" => $rule->getDesc(),
                            "ordem"     => $rule->getPriority(),
                    );
               }
            }
             else if ($_POST['field_filter'] == "destino"){
               if(stristr($list_dst, $_POST['text_filter'])){
                   $dados[] = array(
                            "codigo"    => $rule->getId(),
                            "ativa"     => $rule->isActive(),
                            "src"       => $list_src,
                            "dst"       => $list_dst,
                            "descricao" => $rule->getDesc(),
                            "ordem"     => $rule->getPriority(),
                    );
               }
            }
            else{
                 $dados[] = array(
                            "codigo"    => $rule->getId(),
                            "ativa"     => $rule->isActive(),
                            "src"       => $list_src,
                            "dst"       => $list_dst,
                            "descricao" => $rule->getDesc(),
                            "ordem"     => $rule->getPriority(),
                    );
            }




                }



        // Define variaveis do template
        $smarty->assign ('DADOS',$dados);
        // Variaveis Relativas a Barra de Filtro/Botao Incluir
        $smarty->assign ('view_filter',True);
        $smarty->assign ('view_include_buttom',True);
        $smarty->assign ('debugger_btn',True);
        $smarty->assign ('OPCOES', $opcoes);
        $smarty->assign ('array_include_buttom',array("url" => "../gestao/agi_rules.php?acao=cadastrar", "display"  => "Nova Regra"));
        // Exibe template

        display_template("rel_agi_rules.tpl",$smarty,$titulo);
    }

    /**
     * Adição/Cadastro de novas rotas na central.
     */
    public function addAction() {
        global $LANG;
        $smarty = Zend_Registry::get('smarty');

        $this->populateCommomFields();

        $smarty->assign('ACAO',"cadastrar");
        $titulo = "Regras de Negócio » Regra » Adicionar";

        if($_POST) {
            if($this->isValidPost()) {
                $rule = $this->parseRuleFromPost();
                PBX_Rules::register($rule);
                header("HTTP/1.1 303 See Other");
                header("Location: ./agi_rules.php");
            }
            else {
                $actions = "";
                foreach ($this->forms as $id => $form) {
                    $actions .= "addAction(" . json_encode(array(
                            "id"     => $id,
                            "status" => $form['status'],
                            "type"   => $form['type'],
                            "form"   => $form['formData']
                            )) . ")\n";
                }
                $actions .= "setActiveAction($('actions_list').firstChild)\n";
                $smarty->assign('RULE_ACTIONS', $actions);
                $smarty->assign('ERROR', true);

                /*
                 * Removendo configurações das acoes para popular somente os
                 * campos especifico da regra.
                */
                $post = $_POST;
                unset($post['actions_order']);

                $rule = $this->parseRuleFromPost($post);
                $this->populateFromRule($rule, false);
            }
        }
        else {
            $smarty->assign('weekDays',array(
                    "sun" => true,
                    "mon" => true,
                    "tue" => true,
                    "wed" => true,
                    "thu" => true,
                    "fri" => true,
                    "sat" => true
            ));

            $smarty->assign('dt_agirules',array("dst"=>"dstObj.addItem();\n",
                    "src"=>"origObj.addItem();\n",
                    "time"=>"timeObj.addItem();\n",
                    "autorizado"=>"S",
                    "ordem" => 0));
        }

        display_template("agi_rules.tpl",$smarty,$titulo) ;
    }

    public function editAction() {
        global $LANG, $grupos;

        $this->populateCommomFields();
        $smarty = Zend_Registry::get('smarty');

        $id = isset($post['id']) ? $post['id'] : $_GET['id'];
        if (!$id) {
            display_error($LANG['msg_notselect'],true);
            exit;
        }

        try {
            $rule = PBX_Rules::get($id);
        }
        catch(PBX_Exception_NotFound $ex) {
            display_error("Não existe regra com o id '$id'");
            exit(1);
        }

        if($_POST) {
            if($this->isValidPost()) {
                $new_rule = $this->parseRuleFromPost();
                $new_rule->setId($id);
                $new_rule->setActive($rule->isActive());
                PBX_Rules::update($new_rule);
                header("HTTP/1.1 303 See Other");
                header("Location: ./agi_rules.php");
            }
            else {
                $actions = "";
                foreach ($this->forms as $form_id => $form) {
                    $actions .= "addAction(" . json_encode(array(
                            "id"     => $form_id,
                            "status" => $form['status'],
                            "type"   => $form['type'],
                            "form"   => $form['formData']
                            )) . ")\n";
                }
                $actions .= "setActiveAction($('actions_list').firstChild)\n";
                $smarty->assign('RULE_ACTIONS', $actions);
                $smarty->assign('ERROR', true);
            }
        }

        $this->populateFromRule($rule, false);
        $smarty->assign('ACAO',"alterar&id=$id");

        if(!isset($actions)) {
            $actions = "getRuleActions({$rule->getId()});\n";
            $smarty->assign('RULE_ACTIONS',$actions);
        }

        $titulo = "Regras de Negócio » Regra $id » Alterar";
        display_template("agi_rules.tpl",$smarty,$titulo);
    }

    public function duplicarAction() {

        global $LANG, $grupos;

        $this->populateCommomFields();
        $smarty = Zend_Registry::get('smarty');

        $id = isset($post['id']) ? $post['id'] : $_GET['id'];
        if (!$id) {
            display_error($LANG['msg_notselect'],true);
            exit;
        }

        try {
            $rule = PBX_Rules::get($id);
        }
        catch(PBX_Exception_NotFound $ex) {
            display_error("Não existe regra com o id '$id'");
            exit(1);
        }

        if($_POST) {
            if($this->isValidPost()) {
                $new_rule = $this->parseRuleFromPost();
                $new_rule->setId($id);
                $new_rule->setActive($rule->isActive());

                PBX_Rules::register($new_rule);
                header("HTTP/1.1 303 See Other");
                header("Location: ./agi_rules.php");
            }
            else {
                $actions = "";
                foreach ($this->forms as $form_id => $form) {
                    $actions .= "addAction(" . json_encode(array(
                            "id"     => $form_id,
                            "status" => $form['status'],
                            "type"   => $form['type'],
                            "form"   => $form['formData']
                            )) . ")\n";
                }
                $actions .= "setActiveAction($('actions_list').firstChild)\n";
                $smarty->assign('RULE_ACTIONS', $actions);
                $smarty->assign('ERROR', true);
            }
        }

        $copia = "Cópia de ";

        $this->populateFromRule($rule,$copia);
        $smarty->assign('ACAO',"duplicar&id=$id");

        if(!isset($actions)) {
            $actions = "getRuleActions({$rule->getId()});\n";
            $smarty->assign('RULE_ACTIONS',$actions);
        }

        $titulo = "Regras de Negócio -> Regra $id -> Duplicar";
        display_template("agi_rules.tpl",$smarty,$titulo);
    }

    public function deleteAction() {
        if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            display_error("ID Inválido para exclusão de regra",true);
        }

        try {
            $rule = PBX_Rules::get($_GET['id']);
        }
        catch(PBX_Exception_NotFound $ex) {
            display_error("Não existe regra com o id '$id'");
            exit(1);
        }

        PBX_Rules::delete($_GET['id']);

        header("HTTP/1.1 303 See Other");
        header("Location: ./agi_rules.php");
    }

}

// Variaveis de ambiente do form
$smarty->assign('ACAO',$acao);

$routeController = new RouteController();

if ($acao == "cadastrar") {
    $routeController->addAction();
} elseif ($acao ==  "alterar") {
    $routeController->editAction();
} elseif ($acao ==  "duplicar") {
    $routeController->duplicarAction();
} elseif ($acao ==  "grava_alterar") {
    $routeController->editAction();
} elseif ($acao ==  "excluir") {
    $routeController->deleteAction();
} else {
    $routeController->indexAction();
}
