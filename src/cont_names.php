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
 
 ver_permissao(57) ;
 
/* Variáveis de ambiente do Form */
$select = "SELECT id, name FROM contacts_group";
$raw_groups = $db->query($select)->fetchAll();

$groups = array();
foreach ($raw_groups as $row) {
    $groups[$row["id"]] = $row["name"];
}

$smarty->assign('GROUPS', $groups);

$smarty->assign('ACAO',$acao) ;

if ($acao == "cadastrar") {
    cadastrar();
}
elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_contacts']." » ".$LANG['menu_contacts']." » ".$LANG['change'];
    alterar();
} 
elseif ($acao ==  "grava_alterar") {
    grava_alterar();
} 
elseif ($acao ==  "excluir") {
    excluir();
} else {
    $titulo = $LANG['menu_contato']." » ".$LANG['menu_contacts']." » ".$LANG['include'];
    principal() ;
}

/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/

function principal() 
{
   global $smarty,$titulo, $db ;   
   
   try {
      $sql = "SELECT id FROM contacts_names " ;
      $sql.= " ORDER BY CAST(id as DECIMAL) DESC LIMIT 1" ;
      $row = $db->query($sql)->fetch();
   } catch (PDOException $e) {
      display_error($LANG['error'].$e->getMessage(),true) ;
   }  
   
   $lastID = trim($row['id']) + 1;
   $smarty->assign('LASTID', $lastID);
   $smarty->assign('PROTOTYPE', true);
   $smarty->assign('ACAO',"cadastrar");
   display_template("cont_names.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/

function cadastrar() {
   global $LANG, $db, $name, $group, $address, $city, $state, $cep, $phone_1, $cell_1, $smarty, $lastid;
   try
   {
      $db->beginTransaction();
      // Atualiza tabela operadoras
      $sql  = "INSERT INTO contacts_names (id, name, `group`, address, city, state, cep, ";
      $sql .= "phone_1, cell_1)";
      $sql .= " VALUES ('$lastid', '$name', '$group', '$address', '$city', '$state', '$cep', ";
      $sql .= " '$phone_1', '$cell_1') ";
      $stmt = $db->prepare($sql);
      $stmt->execute();
      $db->commit();
      
      echo "<meta http-equiv='refresh' content='0; url=rel_cont_names.php'>\n"; 
   } 
   catch (Exception $e) 
   {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),True);
   }    
 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
    
   global $LANG, $db, $smarty, $titulo, $acao ;   
   $id = $_GET['id'];
    
   if (!$id) {
      display_error($LANG['msg_notselect'],True) ;
      exit ;
   }
   try 
   {
      $sql = "SELECT * FROM contacts_names WHERE id='$id' ";
      $row = $db->query($sql)->fetch();
   } 
   catch (Exception $e) 
   {
       display_error($LANG['error'].$e->getMessage(),True) ;
       exit ;
   }
   $smarty->assign('LASTID', $_GET['id']);
   
   $smarty->assign('ACAO',"grava_alterar") ;
   $smarty->assign ('dt_contatos',$row) ;
   display_template("cont_names.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/

function grava_alterar()  {
   global $LANG, $db, $id, $name, $group, $address, $city, $state, $cep, $phone_1, $cell_1;
   try 
   {
     $db->beginTransaction() ;
     // Atualiza tabela oepradoras
     $sql = "UPDATE contacts_names set name='$name', address='$address', ";
     $sql.= " city='$city', state='$state', cep='$cep', phone_1='$phone_1', `group`='$group', ";
     $sql.= " cell_1='$cell_1' ";
     $sql.= " where id ='$id'" ;
     $stmt = $db->prepare($sql) ;
     $stmt->execute() ;
     $db->commit();
     echo "<meta http-equiv='refresh' content='0;url=../src/rel_cont_names.php'>\n" ;
   } 
   catch (Exception $e) 
   {
     $db->rollBack();
     display_error($LANG['error'].$e->getMessage(),true) ;
   }    
}

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/

function excluir()  {
    global $LANG, $db;

    $id = $_GET['id'];

    if (!$id) {
        display_error($LANG['msg_notselect'],True);
        exit;
    }
    try {
        $db->beginTransaction() ;
        // Atualiza tabela contacts_names

        $sql = "DELETE FROM contacts_names WHERE id='$id';";
        $pdoResource = $db->prepare($sql);
        $pdoResource->execute();
        $db->commit();
        display_error($LANG['msg_excluded'],true);
        echo "<meta http-equiv='refresh' content='0;url=rel_cont_names.php'>\n";
    }
    catch (PDOException $e) {
        $pdoResource->rollBack();
        display_error($LANG['error'].$e->getMessage(),True) ;
    }
}
