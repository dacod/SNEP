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

 ver_permissao(37) ;
if(isset ($_GET['id'])){
    $id = $_GET['id'];
}

 // Monta lista de centro de custos com exceção dos que ja foram atribuidos.
 if (!isset($ccustos) || count($ccustos) == 0) {
    $stmt = $db->query("select * from ccustos as cc left join oper_ccustos as oc on cc.codigo=oc.ccustos where oc.operadora is NULL;");
    $op_ccustos = $stmt->fetchAll();
     if (count($op_ccustos) > 0) {
       foreach ($op_ccustos as $val)
          $ccustos[$val['codigo']] = $val['tipo']." : ".$val['codigo']." - ".$val['nome'] ;
          asort($ccustos);
    }
 }

 $smarty->assign('CCUSTOS', $ccustos);
 $smarty->assign('ACAO', $acao) ;

 if ($acao == "cadastrar") {
    cadastrar();

 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_tarifas']." » ".$LANG['menu_operadoras']." » ".$LANG['change'];
    alterar();

 } elseif ($acao ==  "grava_alterar") {
    grava_alterar();

 } elseif ($acao ==  "excluir") {
    excluir();

 } else {
   $titulo = $LANG['menu_tarifas']." » ".$LANG['menu_operadoras']." » ".$LANG['include'];
   principal();
   
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo ;   
   $smarty->assign( 'ACAO', "cadastrar" );
   $smarty->assign( 'OPER_CCUSTOS', array() );
   display_template("operadoras.tpl", $smarty, $titulo );
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {

    global $LANG, $db, $oper_ccustos;

    // Cria objeto Snep_Operadoras e seta valores
    $operadora = new Snep_Operadoras();    
    $operadora->nome = $_POST['nome'];
    $operadora->tpm  = $_POST['tpm'];
    $operadora->tdm  = $_POST['tdm'];
    $operadora->tbf  = $_POST['tbf'];
    $operadora->tbc  = $_POST['tbc'];
    $operadora->vpf  = 0 ; //$_POST['vpf'] ;
    $operadora->vpc  = 0 ; //$_POST['vpc'];

    // Registra objeto, o mesmo retorna id de cadastro no banco
    $id = Snep_Operadoras::register($operadora);

    // Registra Centro de Custos da Operadora
    Snep_Operadoras::setCcustoOperadora($id, $oper_ccustos);

    // Redireciona para cadastro de Operadoras
    echo "<meta http-equiv='refresh' content='0;url=../tarifas/operadoras.php'>\n" ;
 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {

    global $LANG, $db, $smarty, $titulo, $acao;

    $id = ( isset($_POST['id'] ) ? $_POST['id'] : $_GET['id'] );

    if (!$id) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
    }
    
    // Relaciona Centros de Custo desta Operadora
    $row = Snep_Operadoras::getCcustoOperadora($id);

    // Organiza Array de Centro de Custos.
    $oper_ccustos = array() ;
    if (count($row) > 0) {
         foreach ($row as $val) {
             $oper_ccustos[$val['codigo']] = $val['tipo']." : ".$val['codigo']." - ".$val['nome'] ;
         }
     asort($oper_ccustos) ;
    }

    // Dados da Operadora
    $row = Snep_Operadoras::get($id);

    $smarty->assign('OPER_CCUSTOS', $oper_ccustos);
    $smarty->assign('ACAO',"grava_alterar") ;
    $smarty->assign ('dt_operadoras', $row[0]);

    display_template("operadoras.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {

    global $LANG, $db, $codigo, $oper_ccustos;

    // Cria objeto Snep_Operadoras e seta valores
    $operadora = new Snep_Operadoras();
    $operadora->codigo = $codigo;
    $operadora->nome   = $_POST['nome'];
    $operadora->tpm    = $_POST['tpm'];
    $operadora->tdm    = $_POST['tdm'];
    $operadora->tbf    = $_POST['tbf'];
    $operadora->tbc    = $_POST['tbc'];
    $operadora->vpf    = 0 ; //$_POST['vpf'] ;
    $operadora->vpc    = 0 ; //$_POST['vpc'];

    // Atualiza banco com novas informações
    Snep_Operadoras::update($operadora);

    // Atualiza Centro de Custos desta Operadora
    Snep_Operadoras::setCcustoOperadora($codigo, $oper_ccustos);

    // Redireciona para relação de Operadoras
    echo "<meta http-equiv='refresh' content='0;url=../tarifas/rel_operadoras.php'>\n" ;
 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG;

   $id = ( isset($_POST['id']) ? $_POST['id'] : $_GET['id'] );

   if (!$id) {
        display_error($LANG['msg_notselect'],true) ;
        exit ;
   }

   Snep_Operadoras::remove($id);
   echo "<meta http-equiv='refresh' content='0;url=../tarifas/rel_operadoras.php'>\n" ;

}
