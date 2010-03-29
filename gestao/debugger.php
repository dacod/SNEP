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
 
/**
 * @file
 * Dialplan Debugger.
 * Simula ligações para teste do dialplan.
 */
// SNEP header
require_once("../includes/verifica.php");
require_once("../configs/config.php");
ver_permissao(49);

// Vendo se foi passado os parametros para teste
$extension = isset($_GET['dst']) && $_GET['dst'] != "" ? $_GET['dst'] : 's';
$srcType   = isset($_GET['srcType']) ? $_GET['srcType'] : NULL;
$trunk     = isset($_GET['trunk']) ? $_GET['trunk'] : NULL;
$caller    = isset($_GET['caller']) && $_GET['caller'] != "" ? $_GET['caller'] : "unknown";
$time      = isset($_GET['time']) ? $_GET['time'] : NULL;
$acao      = isset($_GET['acao']) ? $_GET['acao'] : NULL;

// Controle de Conflitos
$view = isset($_GET['view']) ? $_GET['view'] : NULL;
$hini = isset($_GET['hini']) ? $_GET['hini'] : NULL;
$hfim = isset($_GET['hfim']) ? $_GET['hfim'] : NULL;

$trunks = array();
foreach (PBX_Trunks::getAll() as $tronco) {
    $trunks[$tronco->getId()] = $tronco->getId() . " - " . $tronco->getName();
}
$smarty->assign('TRUNKS', $trunks);

// Parse básico
if($acao == "simulate") {
    // Criando o debugger
    $dialplan = new PBX_Dialplan_Verbose();

    if($srcType == "exten") {
        try {
            $srcObj = PBX_Usuarios::get($caller);
        }
        catch( PBX_Exception_NotFound $ex ) {
            display_error($ex->getMessage(), true);
            exit();
        }
        $channel = $srcObj->getInterface()->getCanal();
    }
    else if($srcType == "trunk") {
        $srcObj = PBX_Trunks::get($trunk);
        $channel = $srcObj->getInterface()->getCanal();
    }
    else {
        $srcObj = null;
        $channel = "unknown";
    }

    $request = new PBX_Asterisk_AGI_Request(
        array(
            "agi_callerid"  => $caller,
            "agi_extension" => $extension,
            "agi_channel"   => $channel
        )
    );

    $request->setSrcObj($srcObj);

    $dialplan->setRequest($request);

    if($time){
      if(preg_match("/^[0-9]:([0-9]{2})$/", $time)) {
        $time = "0" . $time;
      }
      $dialplan->setTime($time);
    }

    try {
        $dialplan->parse(); // O debug =)
    }
    catch(PBX_Exception_NotFound $ex) {
        $smarty->assign('deb_ERROR', 'norule');
    }

    // Se foi encontrada alguma regra para mostrar
    if(count($dialplan->getMatches()) > 0){
        $found = false; // Flag se encontramos uma regra que será executada
        foreach ($dialplan->getMatches() as $index => $rule) {
            if($rule->getId() == $dialplan->getLastRule()->getId()) {
                $state = "torun";
                $found = true;
            }
            else if($found) {
                $state = "ignored";
            }
            else {
                $state = "outdated";
            }

            $acoes = array();
            
            foreach ($rule->getAcoes() as $action) {
                $config = $action->getConfigArray();
                if($action instanceof PBX_Rule_Action_CCustos) {
                    $acoes[] = "Definir Centro de Custos para " . $config['ccustos'];
                }
                else if($action instanceof PBX_Rule_Action_DiscarTronco) {
                    $tronco = PBX_Trunks::get($config['tronco']);
                    $acoes[] = "Discar para Tronco " . $tronco->getName();
                }
                else if($action instanceof PBX_Rule_Action_DiscarRamal) {
                    if(isset($config['ramal']) && $config['ramal'] != "") {
                        $peer = $config['ramal'];
                    }
                    else {
                        $peer = $extension;
                    }

                    try {
                        $ramal = PBX_Usuarios::get($peer);
                        $acoes[] = "Discar para Ramal " . $ramal->getCallerid();
                    }
                    catch(PBX_Exception_NotFound $ex ){
                        $acoes[] = "<strong style='color:red'>Tentativa com falha para ramal $extension (ramal inexistente)</strong>";
                    }
                }
                else if($action instanceof PBX_Rule_Action_Queue) {
                    $acoes[] = "Direcionar para fila " . $config['queue'];
                }
                else if($action instanceof PBX_Rule_Action_Cadeado) {
                    $acoes[] = "Requisitar senha";
                }
                else if($action instanceof PBX_Rule_Action_Context) {
                    $acoes[] = "Direcionar para contexto " . $config['context'];
                }
            }

            $srcs = array();
            foreach ($rule->getSrcList() as $src) {
                $srcs[] = trim(implode(":", $src), ':');
            }
            $srcs = implode(",", $srcs);
            $dsts = array();
            foreach ($rule->getDstList() as $dst) {
                $dsts[] = trim(implode(":", $dst), ':');
            }
            $dsts = implode(",", $dsts);

            $result[$index] = array(
                "id"      => $rule->getId(),
                "state"   => $state,
                "caller"  => $srcs,
                "dst"     => $dsts,
                "desc"    => $rule->getDesc(),
                "valid"   => join(";", $rule->getValidTimeList()),
                "actions" => $acoes
            );
        }

      // Enviando os parametros recebidos
      $input = array("caller" => $caller, "dst" => $extension, "time" => $dialplan->getLastExecutionTime());

      // Enviando para o template algumas variáveis
      $smarty->assign('deb_input', $input);
      $smarty->assign('deb_result', $result);

    }
}

$smarty->assign('PROTOTYPE', true);
$titulo = $LANG['menu_rules']." -> ".$LANG['menu_rules_in_out']." -> ".$LANG['debugger'];
$smarty->assign('debugger', true);

if ($view == "conflict") {
   display_template("debugger_col.tpl",$smarty,$titulo);
}
else {
   display_template("debugger.tpl",$smarty,$titulo);
}

// Conver hora no formato hh:mm:ss para timestamp
function ts_hora ($hora) {
   $hora = explode(":",$hora) ;
   if (count($hora) <= 1)
      return 0 ;
   $hora = mktime($hora[0],$hora[1],0,1,1,98);
   return $hora ;
}
