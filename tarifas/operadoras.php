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

 ver_permissao(37) ;

 // Verifica quais Centros de Custos já foram atribuidos a alguma operadora.
    try {
     $sqlcc = "SELECT ccustos FROM oper_ccustos";
     $r = $db->query($sqlcc)->fetchAll();

     if(count($r) > 0) {
     $ind = '';
         foreach($r as $k => $v) {
            $ind .= "'".$v['ccustos']."',";
         }
     $ind = " WHERE codigo NOT IN(".substr($ind, 0, -1).") ";
     }

    } catch(Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
        exit ;
    }

 // Monta lista de centro de custos com exceção dos que ja foram atribuidos.
 if (!isset($ccustos) || count($ccustos) == 0) {
    try {
       $sql = "SELECT ccustos.* FROM ccustos ". ($ind ? $ind : '') ."ORDER BY ccustos.codigo" ;
       $row = $db->query($sql)->fetchAll();
    } catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
       exit ;
    }
    unset($val);
    $ccustos = array();
    if (count($row) > 0) {
       foreach ($row as $val)
          $ccustos[$val['codigo']] = $val['tipo']." : ".$val['codigo']." - ".$val['nome'] ;
       asort($ccustos);
    }
 }

 $smarty->assign('CCUSTOS',$ccustos);
 // Variaveis de ambiente do form
 $smarty->assign('ACAO',$acao) ;
 if ($acao == "cadastrar") {
    cadastrar();
 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_tarifas']." -> ".$LANG['menu_operadoras']." -> ".$LANG['change'];
    alterar() ;
 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
 } elseif ($acao ==  "excluir") {
    excluir() ;
 } else {
   $titulo = $LANG['menu_tarifas']." -> ".$LANG['menu_operadoras']." -> ".$LANG['include'];
   principal() ;
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo ;   
   $smarty->assign('ACAO',"cadastrar");
   $smarty->assign('OPER_CCUSTOS',array());
   display_template("operadoras.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $nome, $tpm, $tdm, $tbf, $tbc, $vpc, $vpf, $oper_ccustos;
   try {
      $db->beginTransaction() ;
      // Atualiza tabela operadoras
      $sql  = "INSERT INTO operadoras (nome, tpm, tdm, tbf, tbc, vpf, vpc)" ;
      $sql .= " VALUES ('$nome', $tpm, $tdm, $tbf, $tbc, $vpf, $vpc)" ;
      $stmt = $db->prepare($sql) ;
      $stmt->execute() ;
      // Pega Codigo da Operadora que esta sendo cadastrada
      $sql = "SELECT codigo FROM operadoras ORDER BY codigo DESC LIMIT 1" ;
      $tmp_oper = $db->query($sql)->fetch();
      $tmp_oper = $tmp_oper['codigo'] ;
      // Atualiza tabela oper_ccustos
      $sql = "INSERT INTO oper_ccustos (operadora,ccustos) ";
      $sql.= " VALUES (:operadora, :ccusto)" ;
      $stmt = $db->prepare($sql) ;
      $stmt->bindParam('operadora',$tmp_oper) ;
      $stmt->bindParam('ccusto',$tmp_ccusto) ;
      foreach ($oper_ccustos as $val) {
         $tmp_ccusto = $val ;
         $stmt->execute() ;
      }
      $db->commit();
      echo "<meta http-equiv='refresh' content='0;url=../tarifas/operadoras.php'>\n" ;
   } catch (Exception $e) {
      $db->rollBack();
      display_error($LANG['error'].$e->getMessage(),true) ;
   }
 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG,$db,$smarty,$titulo, $acao, $ind ;
   $id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
   if (!$id) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   // Monta lista de ccustos Disponiveis
   try {
      $sql = "SELECT ccustos.* FROM ccustos ". ($ind ? $ind : '') ." ORDER by ccustos.codigo"  ;
      $row = $db->query($sql)->fetchAll();
      $ccustos = array() ;
      if (count($row) > 0) {
         foreach ($row as $val)
             $ccustos[$val['codigo']] = $val['tipo']." : ".$val['codigo']." - ".$val['nome'] ;
         asort($ccustos) ;
      }
   } catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
       exit ;
   }

   // Monta lista de ccustos usadas pela operadora
   try {
      $sql = "SELECT oper_ccustos.*, ccustos.* FROM oper_ccustos ";
      $sql.= " INNER JOIN ccustos ON ccustos.codigo = oper_ccustos.ccustos ";
      $sql.= " WHERE operadora = ".$id;
      $row = $db->query($sql)->fetchAll();
      $oper_ccustos = array() ;
      if (count($row) > 0) {
         foreach ($row as $val)
             $oper_ccustos[$val['codigo']] = $val['tipo']." : ".$val['codigo']." - ".$val['nome'] ;
         asort($oper_ccustos) ;
      }

    } catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
       exit ;
    }

    

   // Dados da Operadora
   try {
      $sql = "SELECT * FROM operadoras WHERE codigo=".$id;
      $row = $db->query($sql)->fetch();
   } catch (PDOException $e) {
      display_error($LANG['error'].$e->getMessage(),true) ;
   }
   $smarty->assign('OPER_CCUSTOS',$oper_ccustos);
   $smarty->assign('CCUSTOS',$ccustos);
   $smarty->assign('ACAO',"grava_alterar") ;
   $smarty->assign ('dt_operadoras',$row);
   display_template("operadoras.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $codigo,$nome, $tpm, $tdm, $tbf, $tbc, $vpc, $vpf, $oper_ccustos;   
   try {
     $sql = "update operadoras set nome='$nome',tpm=$tpm,tdm=$tdm,tbf=$tbf," ;
     $sql.= "tbc=$tbc,vpf=$vpf,vpc=$vpc  where codigo='$codigo'" ;   
     $db->beginTransaction() ;
     // Atualiza tabela operadoras
     $sql = "update operadoras set nome='$nome',tpm=$tpm,tdm=$tdm,tbf=$tbf," ;
     $sql .= " tbc=$tbc,vpf=$vpf,vpc=$vpc where codigo='$codigo'" ;
     $stmt = $db->prepare($sql) ;
     $stmt->execute() ;
     // Atualiza tabela oper_ccustos - remove tudo
     $sql = "DELETE FROM oper_ccustos WHERE operadora = $codigo";
     $stmt = $db->prepare($sql) ;
     $stmt->execute() ;
     // Atualiza tabela oper_ccustos - Insere novos
     $sql = "INSERT INTO oper_ccustos (operadora,ccustos) ";
     $sql.= " VALUES (:operadora, :ccusto)" ;
     $stmt = $db->prepare($sql) ;
     $stmt->bindParam('operadora',$codigo) ;
     $stmt->bindParam('ccusto',$tmp_ccusto) ;
     foreach ($oper_ccustos as $val) {
        $tmp_ccusto = $val ;
        $stmt->execute() ;
     }     
     $db->commit();
     echo "<meta http-equiv='refresh' content='0;url=../tarifas/rel_operadoras.php'>\n" ;
   } catch (Exception $e) {
     $db->rollBack();
     display_error($LANG['error'].$e->getMessage(),true) ;
   }    
 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $db;
   $id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
   if (!$id) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }
   try {
      $db->beginTransaction() ;
      // Atualiza tabela operadoras
      $sql = "DELETE FROM operadoras WHERE codigo=".$id;
      $stmt = $db->prepare($sql) ;
      $stmt->execute() ;
      // Atualiza tabela oper_ccustos
      $sql = "DELETE FROM oper_ccustos WHERE operadora=".$id;
      $stmt = $db->prepare($sql) ;
      $stmt->execute() ;      
      $db->commit();
      display_error($LANG['msg_excluded'],true) ;
     echo "<meta http-equiv='refresh' content='0;url=../tarifas/rel_operadoras.php'>\n" ;
 } catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }  
}?>