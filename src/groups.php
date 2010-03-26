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
 ver_permissao(12) ;
 
  // Variaveis de ambiente do form
 $smarty->assign('ACAO',$acao) ;
 if ($acao == "cadastrar") {
    cadastrar();
 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_register']." -> ".$LANG['menu_grupos_ramais']." -> ".$LANG['change'];
    alterar() ;
 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
 } elseif ($acao ==  "excluir") {
    excluir() ; 
 } elseif ($acao == "incluir") {
    incluir();
 } elseif ($acao == "incluir_ao_grupo") {
     incluir_ao_grupo();
 }
   elseif ($acao ==  "excluir_def") {
    excluir_def();
 } else {
   $titulo = $LANG['menu_register']." -> ".$LANG['menu_grupos_ramais']." -> ".$LANG['include'];
   principal() ;
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty, $titulo, $LANG, $db ;

   try{
        $sql = "SELECT id, name, peers.group FROM peers WHERE id !=1 ";
        $ramais = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }catch(Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }

    $ram = array();
    foreach($ramais as $key => $val) {
        $ram[$val['name']] = $val['name'] ." (". $val['group'] .")";
    }

    $smarty->assign('RAMAIS', $ram);
    $smarty->assign('ACAO',"cadastrar");
    
    display_template("groups.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $nome, $type;
   $sql  = "INSERT INTO groups " ;
   $sql .= "VALUES ('$nome', '$type')" ;
   try {
      $db->beginTransaction() ;
      $db->exec($sql) ;
      $db->commit();
      echo "<meta http-equiv='refresh' content='0;url=../src/rel_groups.php'>\n" ;
   } catch (Exception $e) {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),true) ;
   }

   // Inclusão dos ramais selecionados no grupo recém criado.
   
    $ramais = $_POST['lista2'];

    foreach($ramais as $id => $val) {
        $sql  = " UPDATE peers SET peers.group='$nome' WHERE name='$val' ";
           try {
               $db->beginTransaction() ;
               $stmt = $db->prepare($sql);
               $stmt->execute() ;
               $db->commit();

           } catch (Exception $e) {
               $db->rollBack();
               display_error($LANG['error'].$e->getMessage(),true) ;
           }
    }



 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
    global $LANG,$db,$smarty,$titulo, $acao ;
    $codigo = isset($_POST['cod_grupo']) ? $_POST['cod_grupo'] : $_GET['cod_grupo'];
    if (!$codigo) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
    }

    try {
        $sql = "SELECT * FROM groups WHERE name='$codigo' AND name != 'all' AND name!='admin' AND name!='users'";
        $row = $db->query($sql)->fetch();
    } catch (PDOException $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }

    /* Busca ramais pertencentes ao grupo */
    try{
        $sql = "SELECT id, name, peers.group FROM peers WHERE id !=1 ";
        $ramais = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }catch(Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }

    $ram = array();
    $pertence = array();
    foreach($ramais as $key => $val) {
        if($val['group'] == $row['name'] ) {

            $pertence[$val['name']] = $val['name'] ." (". $val['group'] .")";
        }else{
            $ram[$val['name']] = $val['name'] ." (". $val['group'] .")";
        }
    }

    $smarty->assign('PERTENCE', $pertence);
    $smarty->assign('RAMAIS', $ram);
    $smarty->assign('EDITAR', 1);
    $smarty->assign('ACAO',"grava_alterar") ;
    $smarty->assign ('dt_grupos',$row);
    display_template("groups.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {
    global $LANG, $db, $cod_grupo, $nome, $type, $lista2;

    // Fazendo procura por referencia a esse grupo em regras de negócio.
    $rules_query = "SELECT id FROM regras_negocio WHERE origem LIKE '%G:$cod_grupo%' OR destino LIKE '%G:$cod_grupo%'";
    $regras = $db->query($rules_query)->fetchAll();
    if(count($regras) > 0) {
        foreach ($regras as $regra_resource) {
            $regra = PBX_Rules::get($regra_resource['id']);

            // Atualizando origens
            $srcs = $regra->getSrcList();
            foreach ($srcs as $index => $src) {
                if($src['type'] == "G" && $src['value'] == $cod_grupo) {
                    $srcs[$index]['value'] = $nome;
                }
            }
            $regra->setSrcList($srcs);

            // Atualizando destinos
            $dsts = $regra->getDstList();
            foreach ($dsts as $index => $dst) {
                if($dst['type'] == "G" && $dst['value'] == $cod_grupo) {
                    $dsts[$index]['value'] = $nome;
                }
            }
            $regra->setDstList($dsts);

            PBX_Rules::update($regra);
        }
    }

    $sql = "UPDATE groups SET name='$nome', inherit='$type' where name='$cod_grupo'";
    try {
        $db->beginTransaction();
        $db->exec($sql);
        $db->commit();
       
    } catch (Exception $e) {
        $db->rollBack();
        display_error($LANG['error'].$e->getMessage(),true);
    }

    $query = "SELECT name from peers where peers.group='$nome'";
    $atuais = $db->query($query)->fetchAll();

    foreach($atuais as $id => $ramal) {

        $sql_reset = "UPDATE peers SET peers.group='users' where name='{$ramal['name']}' ";
        $db->beginTransaction();
        $db->exec($sql_reset);
        $db->commit();
    }

    foreach ($lista2 as $id => $wal) {
        
        $sql_peers = "UPDATE peers SET peers.group='$nome' where name='$wal' ";
        $db->beginTransaction();
        $db->exec($sql_peers);
        $db->commit();
    }
    echo "<meta http-equiv='refresh' content='0;url=../src/rel_groups.php'>\n" ;
}

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
    global $LANG,$db,$smarty,$titulo, $acao;
    $codigo = isset($_POST['cod_grupo']) ? $_POST['cod_grupo'] : $_GET['cod_grupo'];
    if (!$codigo) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
    }
    try {
        // Fazendo procura por referencia a esse grupo em regras de negócio.
        $rules_query = "SELECT id, `desc` FROM regras_negocio WHERE origem LIKE '%G:$codigo%' OR destino LIKE '%G:$codigo%'";
        $regras = $db->query($rules_query)->fetchAll();
        if(count($regras) > 0) {
            $msg = $LANG['group_conflict_in_rules'].":<br />\n";
            foreach ($regras as $regra) {
                $msg .= $regra['id'] . " - " . $regra['desc'] . "<br />\n";
            }
            display_error($msg,true);
        }

        $sql = "SELECT name FROM groups WHERE name != '$codigo' AND name != 'all'";
        $row = $db->query($sql)->fetchAll();
    }
    catch (PDOException $e) {
        display_error($LANG['error'].$e->getMessage(),true);
    }
    $grupos = array();
    foreach ($row as $key => $group) {
        switch($group['name']) {
            case 'admin':
                $name = "Administradores";
                break;
            case 'users':
                $name = "Usu&aacute;rios";
                break;
            default:
                $name = $group['name'];
        }
        $grupos[$group['name']] = $name;
    }
    $smarty->assign('back_button', '../src/rel_groups.php');
    $smarty->assign('ACAO',"grava_alterar");
    $smarty->assign ('name',$codigo);
    $smarty->assign ('dt_grupos',$grupos);
    display_template("groups_delete.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao EXCLUIR_DEF - Excluir definitivamente o registro selecionado
------------------------------------------------------------------------------*/
function excluir_def()  {
    global $LANG, $db;
    $codigo = isset($_POST['cod_grupo']) ? $_POST['cod_grupo'] : $_GET['cod_grupo'];
    if (!$codigo) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
    }
    $new_group = isset($_POST['group']) ? $_POST['group'] : 'users';
    $db->beginTransaction() ;
    try {
        $sql = "UPDATE peers SET `group`='$new_group' WHERE `group`='$codigo'";
        $db->exec($sql) ;
        $sql = "DELETE FROM groups WHERE name='".$codigo."'";
        $db->exec($sql) ;
        $db->commit();
        echo "<meta http-equiv='refresh' content='0;url=../src/rel_groups.php'>\n" ;
    } catch (PDOException $e) {
        $db->rollBack();
        display_error($LANG['error'].$e->getMessage(),true) ;
    }
}
