<?php
/*-----------------------------------------------------------------------------
 * Programa: cont_names.php - Cadastro de Nomes para Contatos
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");  
 require_once("../configs/config.php");
 
 ver_permissao(57) ;
 
 /* Variáveis de ambiente do Form */
    
$smarty->assign('ACAO',$acao) ;

if ($acao == "cadastrar") {
    cadastrar();
}
elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_contacts']." -> ".$LANG['menu_contacts']." -> ".$LANG['change'];
    alterar();
} 
elseif ($acao ==  "grava_alterar") {
    grava_alterar();
} 
elseif ($acao ==  "excluir") {
    excluir();
} else {
    $titulo = $LANG['menu_contato']." -> ".$LANG['menu_contacts']." -> ".$LANG['include'];
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
   
   $lastID = trim($row['id'])+1 ;
   $smarty->assign('LASTID', $lastID) ;
   $smarty->assign('ACAO',"cadastrar") ;
   display_template("cont_names.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/

function cadastrar() {
   global $LANG, $db, $name, $address, $city, $state, $cep, $phone_1, $cell_1, $lastid, $smarty;
        
   try 
   {
      $db->beginTransaction() ;
      // Atualiza tabela operadoras
        
      $sql  = "INSERT INTO contacts_names (id, name, address, city, state, cep, " ;
      $sql .= "phone_1, cell_1)" ;
      $sql .= " VALUES ('$lastid', '$name', '$address', '$city', '$state', '$cep', " ;
      $sql .= " '$phone_1', '$cell_1') " ;  
      $stmt = $db->prepare($sql) ;
      $stmt->execute();
      $db->commit();
      
      echo "<meta http-equiv='refresh' content='0; url=rel_cont_names.php'>\n" ; 
   } 
   catch (Exception $e) 
   {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),True) ;
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
   $smarty->assign('LASTID', $_POST['id']);
   
   $smarty->assign('ACAO',"grava_alterar") ;
   $smarty->assign ('dt_contatos',$row) ;
   display_template("cont_names.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/

function grava_alterar()  {
   global $LANG, $db, $id, $name, $address, $city, $state, $cep, $phone_1, $cell_1;
   try 
   {
     $db->beginTransaction() ;
     // Atualiza tabela oepradoras
     $sql = "UPDATE contacts_names set name='$name', address='$address', ";
     $sql.= " city='$city', state='$state', cep='$cep', phone_1='$phone_1', ";
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
      display_error($LANG['msg_notselect'],True) ;
      exit ;
   }
   try 
   {
      $db->beginTransaction() ;
      // Atualiza tabela contacts_names

      $sql = "DELETE FROM contacts_names WHERE id='$id' " ;
      $stmt = $db->prepare($sql) ;
      $stmt->execute() ;
      display_error($LANG['msg_excluded'],true) ;
     echo "<meta http-equiv='refresh' content='0;url=rel_cont_names.php'>\n" ;
   } 
   catch (PDOException $e) 
   {
      display_error($LANG['error'].$e->getMessage(),True) ;
   }  
}
?>