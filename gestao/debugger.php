<?php
/**
 * @file
 * Dialplan Debugger.
 * Simula ligações para teste do dialplan.
 * @author Henrique Grolli Bassotto <henrique@opens.com.br>
 */
// SNEP header
require_once("../includes/verifica.php");
require_once("../configs/config.php");
ver_permissao(49);

// Vendo se foi passado os parametros para teste
$dst    = isset($_GET['dst']) && $_GET['dst'] != "" ? $_GET['dst'] : 's';
$caller = isset($_GET['caller']) ? $_GET['caller'] : NULL;
$time   = isset($_GET['time']) ? $_GET['time'] : NULL;
$acao   = isset($_GET['acao']) ? $_GET['acao'] : NULL;
// Controle de Conflitos
$view   = isset($_GET['view']) ? $_GET['view'] : NULL;
$hini   = isset($_GET['hini']) ? $_GET['hini'] : NULL;
$hfim   = isset($_GET['hfim']) ? $_GET['hfim'] : NULL;

// Parse básico
if($acao == "simulate" && !$caller) {
  display_error($LANG['msg_requiredinfo'], true);
}
else if($acao == "simulate") {
    // Criando o debugger
    $debugger = new PBX_Dialplan_Verbose();

    $request = new Asterisk_AGI_Request(
        array(
            "agi_callerid"  => $caller,
            "agi_extension" => $dst
        )
    );

    $debugger->setRequest($request);

    if($time){
      if(ereg("^[0-9]:([0-9]{2})$", $time)) {
        $time = "0" . $time;
      }
      $debugger->setTime($time);
    }

    try {
        $debugger->parse(); // O debug =)
    }
    catch(PBX_Exception_NotFound $ex) {
        $smarty->assign('deb_ERROR', 'norule');
    }

    // Se foi encontrada alguma regra para mostrar
    if(count($debugger->getMatches()) > 0){
        $found = false; // Flag se encontramos uma regra que será executada
        foreach ($debugger->getMatches() as $index => $rule) {
            if($rule->getId() == $debugger->getLastRule()->getId()) {
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
                    $ramal = PBX_Usuarios::get($config['ramal']);
                    $acoes[] = "Discar para Ramal " . $ramal->getCallerid();
                }
                else if($action instanceof PBX_Rule_Action_Queue) {
                    $acoes[] = "Direcionar para fila " . $config['queue'];
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
                "state"   => $state,
                "caller"  => $srcs,
                "dst"     => $dsts,
                "desc"    => $rule->getDesc(),
                "valid"   => join(";", $rule->getValidTimeList()),
                "actions" => $acoes
            );
        }

      // Enviando os parametros recebidos
      $input = array("caller" => $caller, "dst" => $dst, "time" => $debugger->getLastExecutionTime());

      // Enviando para o template algumas variáveis
      $smarty->assign('deb_input', $input);
      $smarty->assign('deb_result', $result);

    }
}

$titulo = $LANG['menu_rules']." -> ".$LANG['menu_exit']." -> ".$LANG['debugger'];
$smarty->assign('debugger', true);
if ($view == "conflict")
   display_template("debugger_col.tpl",$smarty,$titulo);
else
   display_template("debugger.tpl",$smarty,$titulo);

// Conver hora no formato hh:mm:ss para timestamp
function ts_hora ($hora) {
   $hora = explode(":",$hora) ;
   if (count($hora) <= 1)
      return 0 ;
   $hora = mktime($hora[0],$hora[1],0,1,1,98);
   return $hora ;
}
