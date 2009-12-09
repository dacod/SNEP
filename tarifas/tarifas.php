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
 ver_permissao(45) ;

// Monta lista de Operadoras
 if (!isset($operadora) || count($operadoras) == 0) {
    try {
       $sql_oper = "SELECT * FROM operadoras ORDER by nome" ;
       $row_oper = $db->query($sql_oper)->fetchAll();
    } catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
    }
    unset($val);
    $operadoras = array('' => $LANG['undef']);
    foreach ($row_oper as $val)
       $operadoras[$val['codigo']] = $val['nome'] ;
    asort($operadoras) ;
 }
 
 $smarty->assign('OPERADORAS',$operadoras);
 $smarty->assign('ESTADOS',$uf_brasil);
 
   
 // Variaveis de ambiente do form
 if(!isset($acao)) $acao = "";
 $smarty->assign('ACAO',$acao) ;
     if ($acao == "cadastrar") {
        cadastrar();
     } elseif ($acao ==  "alterar") {
        $titulo = $LANG['menu_tarifas']." -> ".$LANG['change'];
        alterar() ;
     } elseif ($acao ==  "grava_alterar") {
        grava_alterar() ;
     } elseif ($acao ==  "excluir") {
        excluir() ;
     } else {
       $titulo = $LANG['menu_tarifas']." -> ".$LANG['include'];
       principal() ;
     }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo ;
   $smarty->assign('TARIFAS', 'TRUE');
   $smarty->assign('ACAO',"cadastrar");
   display_template("tarifas.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $operadora, $ddi, $pais, $ddd, $cidade, $estado, $prefixo, $vcel, $vfix, $vpf, $vpc;
   $nome     = addslashes($nome) ;
   try {
      $db->beginTransaction() ;
      // Atualiza Tabela tarifas
      $sql  = "INSERT INTO tarifas " ;
      $sql .= " (operadora,ddi,pais,ddd,cidade,estado,prefixo)" ;
      $sql .= " VALUES ($operadora,$ddi,'$pais',$ddd,'$cidade',";
      $sql .= "'$estado','$prefixo')" ;
      $db->exec($sql) ;
       // Pega Codigo da Tarifa que esta sendo cadastrada
      $sql = "SELECT codigo FROM tarifas ORDER BY codigo DESC LIMIT 1" ;
      $codigo = $db->query($sql)->fetch();
      $codigo = $codigo['codigo'] ;
      // Atualiza tabelsa Valores das Tarifas
      $sql = "INSERT INTO tarifas_valores " ;
      $sql.= " VALUES ($codigo,NOW(),$vcel, $vfix, $vpf, $vpc)" ;
      $db->exec($sql) ;
      $db->commit();
      
      echo "<meta http-equiv='refresh' content='0;url=../tarifas/tarifas.php'>\n" ;
   } catch (Exception $e) {
      $db->rollBack();
      display_error($LANG['error']. $LANG['errortarifa'],true) ;
   }    
 }
 exit;

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG,$db,$smarty,$titulo, $acao;
   $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];
   if (!$codigo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
    $sql = "SELECT tarifas.* FROM tarifas " ;
    $sql.= " WHERE codigo = $codigo" ;
    $row = $db->query($sql)->fetch();
    $sql = "SELECT codigo,date_format(data,'%d/%m/%Y %H:%i:%s') as data_f," ;
    $sql.= "data,vfix,vcel, vpf, vpc FROM tarifas_valores " ;
    $sql.= " WHERE codigo = $codigo ORDER by data" ;
    $row_vlr = $db->query($sql)->fetchAll();
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
 
 $smarty->assign('ACAO',"grava_alterar") ;
 $smarty->assign ('dt_tarifas',$row);
 $smarty->assign ('ESTADO',$row['estado']);
 $smarty->assign ('CIDADE',$row['cidade']);
 $smarty->assign ('CITY', "Selecione");
 $smarty->assign ('dt_valores',$row_vlr);
 display_template("tarifas.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado somente para tarifas_valores
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $codigo, $data, $vfix, $vcel, $avfix, $avcel, $action, $vpf, $vpc;
   if (!$_POST['codigo']) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }

   try {
     $db->beginTransaction() ;     
     $sql = "REPLACE INTO tarifas_valores (codigo,data,vcel, vfix, vpf, vpc)" ;
     $sql.= "VALUES (:codigo, :data, :vcel, :vfix, :vpf, :vpc)" ;
     $stmt = $db->prepare($sql) ;
     $stmt->bindParam('codigo',$codigo) ;
     $stmt->bindParam('data',$tmp_data) ;
     $stmt->bindParam('vcel',$tmp_vcel) ;
     $stmt->bindParam('vfix',$tmp_vfix) ;
     $stmt->bindParam('vpc',$tmp_vpc) ;
     $stmt->bindParam('vpf',$tmp_vpf) ;
     
         foreach ($action as $val) {
            $tmp_data    = $data[$val] ;
            $tmp_vcel   = $vcel[$val];
            $tmp_vfix   = $vfix[$val] ;
            $tmp_vpc    = $vpc[$val];
            $tmp_vpf    = $vpf[$val];
            if ($tmp_vcel > 0 && $tmp_vfix > 0)
               $stmt->execute() ;
         }
     $db->commit();
     echo "<meta http-equiv='refresh' content='0;url=../tarifas/rel_tarifas.php'>\n" ;
   } catch (Exception $e) {
     $db->rollBack();
     display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $db, $codigo;
   $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];
   if (!$codigo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
      $db->beginTransaction() ;
      $sql = "DELETE FROM tarifas WHERE codigo=$codigo " ;
      $db->exec($sql) ;
      $sql = "DELETE FROM tarifas_valores WHERE codigo=$codigo " ;
      $db->exec($sql) ;
      $db->commit();
      display_error($LANG['msg_excluded'],true) ;
      echo "<meta http-equiv='refresh' content='0;url=../tarifas/rel_tarifas.php'>\n" ;
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
}?>