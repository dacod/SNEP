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

 $row_oper = Snep_Operadoras::getAll();

 unset($val);
 $operadoras = array('' => $LANG['undef']);

 foreach ($row_oper as $val) {
      $operadoras[$val['codigo']] = $val['nome'] ;
 }
 asort($operadoras) ;

 $acao = (isset($acao) ? $acao : $acao = "");

 $smarty->assign('OPERADORAS',$operadoras);
 $smarty->assign('ESTADOS',$uf_brasil);
 $smarty->assign('PROTOTYPE', true); 
 $smarty->assign('ACAO',$acao);

 if ($acao == "cadastrar") {
    cadastrar();
 }
 elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_tarifas'] ." » ". $LANG['change'];
    alterar() ;
 }
 elseif($acao ==  "grava_alterar") {
    grava_alterar() ;
 }
 elseif ($acao ==  "excluir") {
    excluir() ;
 }
 else {
    $titulo = $LANG['menu_tarifas'] ." » ". $LANG['include'];
    principal() ;
 }

/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo,$LANG ;

   $smarty->assign('TARIFAS', 'TRUE');
   $smarty->assign('ACAO',"cadastrar");
   $smarty->assign ('CITY', $LANG['select']);
   display_template("tarifas.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
    global $LANG;
   
    $tarifa = new Snep_Tarifas();
    $tarifa->operadora = $_POST['operadora'];
    $tarifa->ddi       = $_POST['ddi'];
    $tarifa->pais      = $_POST['pais'];
    $tarifa->ddd       = $_POST['ddd'];
    $tarifa->cidade    = $_POST['cidade'];
    $tarifa->estado    = $_POST['estado'];
    $tarifa->prefixo   = $_POST['prefixo'];
    $tarifa->vcel      = $_POST['vcel'];
    $tarifa->vfix      = $_POST['vfix'];
    $tarifa->vpf       = 0 ; //$_POST['vpf'];
    $tarifa->vpc       = 0 ; //$_POST['vpc'];

    Snep_Tarifas::register($tarifa);
        
    echo "<meta http-equiv='refresh' content='0;url=../tarifas/tarifas.php'>\n" ;   
 }
 exit;

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
   global $LANG, $smarty, $titulo, $acao;
   
   $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];

   if (!$codigo) {
      display_error($LANG['msg_notselect'],true);
      exit;
   }
   
   $row_vlr = Snep_Tarifas::getValor($codigo);
   $row = Snep_Tarifas::get($codigo);

   $smarty->assign ('ACAO',"grava_alterar") ;
   $smarty->assign ('dt_tarifas',$row[0]);
   $smarty->assign ('ESTADO',$row[0]['estado']);
   $smarty->assign ('CIDADE',$row[0]['cidade']);
   $smarty->assign ('dt_valores',$row_vlr);
   $smarty->assign ('id_tarifa', $row_vlr[0]['codigo']);
   $smarty->assign ('CITY', $LANG['select']);
   display_template("tarifas.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado somente para tarifas_valores
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $action;
  
   if (!$_POST['codigo']) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }

   foreach($action as $id => $tar) {        

       $tarifa = new Snep_Tarifas();
       $tarifa->data      = $_POST['data'][$action[$tar]];
       $tarifa->vcel      = $_POST['vcel'][$action[$tar]];
       $tarifa->vfix      = $_POST['vfix'][$action[$tar]];
       $tarifa->vpf       = 0 ; //$_POST['vpf'][$action[$tar]];
       $tarifa->vpc       = 0 ; //$_POST['vpc'][$action[$tar]];
        
       Snep_Tarifas::registerValores($tarifa, $_POST['codigo']);

       unset($tarifa);
   }

   echo "<meta http-equiv='refresh' content='0;url=../tarifas/rel_tarifas.php'>\n" ;
   exit;
 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG;

   $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];

   if (!$codigo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }

   Snep_Tarifas::remove($codigo);

   echo "<meta http-equiv='refresh' content='0;url=../tarifas/rel_tarifas.php'>\n" ; 
}
?>
