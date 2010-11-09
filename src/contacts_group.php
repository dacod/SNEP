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
ver_permissao(49);

// Variaveis de ambiente do form
$smarty->assign('ACAO',$acao);
if ($acao == "cadastrar") {
    cadastrar();
} elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_register']." » ".$LANG['contacts_group']." » ".$LANG['change'];
    alterar();
} elseif ($acao ==  "grava_alterar") {
    grava_alterar();
} elseif ($acao ==  "excluir") {
    excluir();
} elseif ($acao == "incluir") {
    incluir();
} elseif ($acao ==  "excluir_def") {
    excluir_def();
} else {
    $titulo = $LANG['menu_register']." » ".$LANG['contacts_group']." » ".$LANG['include'];
    principal();
}
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal() {
    global $smarty, $titulo, $LANG, $db;

    try {
        $sql = "SELECT c.id as id, c.name as name, g.name as `group` FROM contacts_names as c, contacts_group as g  WHERE (c.group = g.id) ";
        $contacts_result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }catch(Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true);
    }

    $contacts = array();
    foreach($contacts_result as $key => $val) {
        $contacts[$val['id']] = $val['name'] ." (". $val['group'] .")";
    }

    $smarty->assign('CONTACTS', $contacts);
    $smarty->assign('ACAO',"cadastrar");

    display_template("contacts_group.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar() {
    global $LANG, $db, $nome;
    $sql  = "INSERT INTO contacts_group (name) ";
    $sql .= "VALUES ('$nome')";
    $db->beginTransaction();
    $db->exec($sql);

    $group_id = $db->lastInsertId();

    // Inclusão dos ramais selecionados no grupo recém criado.
    $contacts = ( isset($_POST['lista2']) ? $_POST['lista2'] : null );

    if( $contacts ) {
        foreach($contacts as $id) {
            $sql = "UPDATE contacts_names SET `group`='$group_id' WHERE id='$id' ";
            $db->exec($sql);
        }
    }
    
    try {
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        display_error($LANG['error'].$e->getMessage(),true);
    }

    echo "<meta http-equiv='refresh' content='0;url=../index.php/contactsgroups'>\n";
}

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar() {
    global $LANG,$db,$smarty,$titulo, $acao;
    $id = isset($_GET['cod_grupo']) ? $_GET['cod_grupo'] : null;
    if ($id === null) {
        display_error($LANG['msg_notselect'],true);
        exit;
    }

    try {
        $sql = "SELECT * FROM contacts_group WHERE id='$id'";
        $row = $db->query($sql)->fetch();
    } catch (PDOException $e) {
        display_error($LANG['error'].$e->getMessage(),true);
    }

    /* Busca ramais pertencentes ao grupo */
    try {
        $sql = "SELECT c.id as id, c.name as name, g.name as `group`, g.id as group_id FROM contacts_names as c, contacts_group as g  WHERE (c.group = g.id) ";
        $contacts = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }catch(Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true);
    }

    $all_contacts = array();
    $pertence = array();
    foreach($contacts as $key => $val) {
        if($val['group'] == $row['name'] ) {
            $pertence[$val['id']] = $val['name'] ." (". $val['group'] .")";
        }else {
            $all_contacts[$val['id']] = $val['name'] ." (". $val['group'] .")";
        }
    }

    $smarty->assign('PERTENCE', $pertence);
    $smarty->assign('CONTACTS', $all_contacts);
    $smarty->assign('EDITAR', 1);
    $smarty->assign('ACAO',"grava_alterar");
    $smarty->assign ('dt_grupos',$row);
    display_template("contacts_group.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar() {
    global $LANG, $db, $nome, $type, $lista1, $lista2;

    $id = isset($_GET['cod_grupo']) ? $_GET['cod_grupo'] : null;
    if ($id === null) {
        display_error($LANG['msg_notselect'],true);
        exit;
    }

    $sql = "UPDATE contacts_group SET name='$nome' where id='$id'";
    $db->beginTransaction();
    $db->exec($sql);

    foreach ($lista2 as $contact_id) {
        $sql_contacts = "UPDATE contacts_names SET `group`='$id' where id='$contact_id'";
        echo $sql_contacts;
        $db->exec($sql_contacts);
    }

    try {
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        display_error($LANG['error'].$e->getMessage(),true);
    }
    echo "<meta http-equiv='refresh' content='0;url=../index.php/contactsgroups'>\n";
}

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir() {
    global $LANG, $db, $smarty, $titulo, $acao;

    $id = isset($_GET['cod_grupo']) ? $_GET['cod_grupo'] : null;
    if ($id === null) {
        display_error($LANG['msg_notselect'],true);
        exit;
    }

    // Fazendo procura por referencia a esse grupo em regras de negócio.
    $rules_query = "SELECT id, `desc` FROM regras_negocio WHERE origem LIKE '%CG:$id%' OR destino LIKE '%CG:$id%'";
    $regras = $db->query($rules_query)->fetchAll();
    if(count($regras) > 0) {
        $msg = $LANG['group_conflict_in_rules'].":<br />\n";
        foreach ($regras as $regra) {
            $msg .= $regra['id'] . " - " . $regra['desc'] . "<br />\n";
        }
        display_error($msg,true);
    }

    $sql = "SELECT id, name FROM contacts_group WHERE id != '$id'";
    $row = $db->query($sql)->fetchAll();

    $grupos = array();
    foreach ($row as $key => $group) {
        $grupos[$group['id']] = $group['name'];
    }

    $smarty->assign('back_button', '../index.php/contactsgroups');
    $smarty->assign('ACAO',"grava_alterar");
    $smarty->assign ('name',$id);
    $smarty->assign ('dt_grupos',$grupos);
    display_template("groups_delete.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao EXCLUIR_DEF - Excluir definitivamente o registro selecionado
------------------------------------------------------------------------------*/
function excluir_def() {
    global $LANG, $db;
    $codigo = isset($_POST['cod_grupo']) ? $_POST['cod_grupo'] : $_GET['cod_grupo'];
    if (!$codigo) {
        display_error($LANG['msg_notselect'],true);
        exit;
    }
    $new_group = isset($_POST['group']) ? $_POST['group'] : 'users';
    $db->beginTransaction();

    $sql = "UPDATE contacts_names SET `group`='$new_group' WHERE `group`='$codigo'";
    $db->exec($sql) ;

    $sql = "DELETE FROM contacts_group WHERE id='".$codigo."'";
    $db->exec($sql);

    try {
        $db->commit();
        echo "<meta http-equiv='refresh' content='0;url=../index.php/contactsgroups'>\n";
    } catch (PDOException $e) {
        $db->rollBack();
        display_error($LANG['error'].$e->getMessage(),true);
    }
}
