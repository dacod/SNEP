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
 
 ver_permissao(49) ;

 // Variaveis de ambiente do form
 $smarty->assign('ACAO',$acao);
 $smarty->assign('OPCOES_SN',$tipos_sn);
 $smarty->assign('OPCOES_TF',$tipos_tf);
 
 /* Monta lista de  Grupos de  Usuarios */
 try {
    $sql = "SELECT name FROM groups ";
    $groups = $db->query($sql)->fetchAll();
 }
 catch (PDOException $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }

 foreach($groups as $id => $val) {     
    if($val['name'] == 'all') {
        $grupos[$val['name']] = 'Todos';
    }
    elseif($val['name'] == 'admin') {
        $grupos[$val['name']] = 'Administrador';
    }
    elseif($val['name'] == 'users') {
        $grupos[$val['name']] = 'Usuarios';
    }else{    
        $grupos[$val['name']] = $val['name'];
    }
 }

 $smarty->assign('OPCOES_GRUPOS', $grupos);

$select = "SELECT id, name FROM contacts_group";
$raw_groups = $db->query($select)->fetchAll();

$groups = array();
foreach ($raw_groups as $row) {
    $groups[$row["id"]] = $row["name"];
}

$smarty->assign('OPCOES_CONTACTS_GROUPS', $groups);

/* Monta lista de Centro de Custos */
/* ----------------------------------------------------------------- */
try {
       $sql = "SELECT * FROM ccustos ORDER BY codigo" ;
       $rowcc = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
}

$ccustos = array();
foreach ($rowcc as $id => $val) {
    $ccustos[$val['codigo']] = $val['codigo'] ." - ".$val['nome'];
}
$smarty->assign('OPCOES_CC', $ccustos);
/* ----------------------------------------------------------------- */
/* Monta lista de Filas */
/* ----------------------------------------------------------------- */
try {
       $sql = "SELECT name FROM queues" ;
       $rowq = $db->query($sql)->fetchAll();

} catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;

}
$filas = array();
foreach ($rowq as $id => $val) {
    $filas[$id] = $val['name'];
}
$smarty->assign('OPCOES_FILAS', $filas);

/* ----------------------------------------------------------------- */
/* Listam de troncos */
/* ----------------------------------------------------------------- */
$trunks = array();
foreach (PBX_Trunks::getAll() as $tronco) {
    $trunks[] = array(
        'id'   =>$tronco->getId(),
        'name' => $tronco->getName()
    );
    
}
$smarty->assign('OPCOES_TRONCOS', $trunks);


 // ordens de Execucao
 for ($i=0;$i<=5;$i++)
     $ordem_list[$i] = $i ;
 $smarty->assign('OPCOES_ORDER',$ordem_list);
 
 if ($acao == "cadastrar") {
    cadastrar();
 } elseif ($acao ==  "alterar") {
    $titulo = $LANG['menu_rules']." -> ".$LANG['menu_exit']." -> ".$LANG['change'];
    alterar() ;
 } elseif ($acao ==  "grava_alterar") {
    grava_alterar() ;
 } elseif ($acao ==  "excluir") {
    excluir() ;
 } else {
   $titulo = $LANG['menu_rules']." -> ".$LANG['menu_rules_in_out']." -> ".$LANG['include'];
    $smarty->assign('weekDays',array(
    "sun" => true,
    "mon" => true,
    "tue" => true,
    "wed" => true,
    "thu" => true,
    "fri" => true,
    "sat" => true
));
   principal() ;
 }
/*------------------------------------------------------------------------------
 Funcao PRINCIPAL - Monta a tela principal da rotina
------------------------------------------------------------------------------*/
function principal()  {
   global $smarty,$titulo, $LANG, $db ;
   $smarty->assign('ACAO',"cadastrar");

 

   $smarty->assign('dt_agirules',array("dst"=>"dstObj.addItem();\n",
                                       "src"=>"origObj.addItem();\n",
                                       "time"=>"timeObj.addItem();\n",
                                       "autorizado"=>"S",
                                       "ordem" => 0));
   
   display_template("agi_rules.tpl",$smarty,$titulo) ;
}

/*------------------------------------------------------------------------------
 Funcao CADASTRAR - Inclui um novo registro
------------------------------------------------------------------------------*/
function cadastrar()  {
   global $LANG, $db, $prioridade, $srcValue, $gravacao, $dstValue, $descricao, $autorizado, $timeValue, $ordem, $grupo_src, $grupo_dst;
   
   // Tratamento das ações
   $indice = $_POST['indice'];
   $cap = explode(',', substr($_POST['ids'],0,-1));

   $action = array();

   foreach($cap as $ordem => $acoes) {
             switch (substr($acoes,0,1)) {
                case 't':
                    $cc = $acoes.'cc';
                    $tnk = $acoes.'tnk';
                    $to = $acoes.'to';
                    $tl = $acoes.'tl';
                    $omo = $acoes.'omo';
                    $fg = $acoes.'fg';
                    $em = $acoes.'em';
                    $action[$ordem.'t']['cc']  = $_POST[$cc];
                    $action[$ordem.'t']['tnk'] = $_POST[$tnk];
                    $action[$ordem.'t']['to']  = $_POST[$to];
                    $action[$ordem.'t']['tl']  = $_POST[$tl];
                    $action[$ordem.'t']['fg']  = $_POST[$fg];
                    $action[$ordem.'t']['em']  = $_POST[$em];
                    $action[$ordem.'t']['omo']  = ( $_POST[$omo] ? 1 : 0);
                    break;
                case 'e':
                    $cc  = $acoes.'cc';
                    $rm  = $acoes.'rm';
                    $to  = $acoes.'to';
                    $tl  = $acoes.'tl';
                    $omo = $acoes.'omo';
                    $fg  = $acoes.'fg';
                    $em  = $acoes.'em';
                    $action[$ordem.'e']['cc']  = $_POST[$cc];
                    $action[$ordem.'e']['rm']  = $_POST[$rm];
                    $action[$ordem.'e']['to']  = $_POST[$to];
                    $action[$ordem.'e']['tl']  = $_POST[$tl];
                    $action[$ordem.'e']['omo'] = $_POST[$omo];
                    $action[$ordem.'e']['fg'] = $_POST[$fg];
                    $action[$ordem.'e']['em']  = $_POST[$em];
                    break;
                case 'c':
                    $ct = $acoes.'ct';
                    $action[$ordem.'c']['ct'] = $_POST[$ct];
                    break;
                case 'p':
                    $ct = $acoes.'ct';
                    $cc = $acoes.'cc';
                    $action[$ordem.'p']['ct'] = $_POST[$ct];
                    $action[$ordem.'p']['cc'] = $_POST[$cc];
                    break;
                case 'd':
                    $cc = $acoes.'cc';
                    $ct = $acoes.'ct';
                    $action[$ordem.'d']['ct'] = $_POST[$ct];
                    $action[$ordem.'d']['cc'] = $_POST[$cc];
                    break;
                case 'r':
                    $cc = $acoes.'cc';
                    $ct = $acoes.'ct';
                    $action[$ordem.'r']['ct'] = $_POST[$ct] == 'on'?true:false;
                    $action[$ordem.'r']['cc'] = $_POST[$cc] == 'on'?true:false;
                    break;
                case 'a':
                    $ct = $acoes.'ct'; // Tipo de ação Origem ou Destino
                    $cc = $acoes.'cc'; // Instrução de Corte
                    $to = $acoes.'to'; // Prefixo
                    $tl = $acoes.'tl'; // Sufixo
                    $action[$ordem.'a']['cc']  = $_POST[$cc];
                    $action[$ordem.'a']['ct']  = $_POST[$ct];
                    $action[$ordem.'a']['to']  = $_POST[$to];
                    $action[$ordem.'a']['tl']  = $_POST[$tl];
                    break;
                case 'l':
                    $cc = $acoes.'cc';
                    $ct = $acoes.'ct';
                    $action[$ordem.'l']['ct'] = $_POST[$ct];
                    $action[$ordem.'l']['cc'] = $_POST[$cc]-1;
                    break;
                case 'q':
                    $cc = $acoes.'cc';
                    $fl = $acoes.'fl';
                    $to = $acoes.'to';
                    $action[$ordem.'q']['cc'] = $_POST[$cc];
                    $action[$ordem.'q']['fl'] = $_POST[$fl];
                    $action[$ordem.'q']['to'] = $_POST[$to];
                    break;
            }
   }

    // Instancia um objeto do tipo regra
    $regra = new PBX_Rule();

    $diasDaSemana = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
    $regra->cleanValidWeekList();
    foreach ($diasDaSemana as $dia) {
        if( key_exists($dia, $_POST) ) {
            $regra->addWeekDay($dia);
        }
    }

    // Adicionando Origens
    foreach (explode(',', $srcValue) as $src) {
        if(!strpos($src, ':')) {
            $regra->addSrc(array("type" => $src, "value" => ""));
        }
        else {
            $info = explode(':', $src);
            if(!is_array($info) OR count($info) != 2) {
                throw new PBX_Exception_BadArg("Valor errado para origem da regra de negocio.");
            }
            $regra->addSrc(array("type" => $info[0], "value" => $info[1]));
        }
    }

    // Adicionando Destinos
    foreach (explode(',', $dstValue) as $dst) {
        if(!strpos($dst, ':')) {
            $regra->addDst(array("type" => $dst, "value" => ""));
        }
        else {
            $info = explode(':', $dst);
            if(!is_array($info) OR count($info) != 2) {
                throw new PBX_Exception_BadArg("Valor errado para destino da regra de negocio.");
            }
            $regra->addDst(array("type" => $info[0], "value" => $info[1]));
        }
    }

    // Adicionando tempos
    $regra->cleanValidTimeList();
    foreach (explode(',', $timeValue) as $validade) {
        $regra->addValidTime($validade);
    }

    // Adiciona Descricao
    $regra->setDesc($descricao);
    
    // Percorre origem e destino incluindo-as no objeto da regra
    
    foreach($src as $origem) {
        $regra->addSrc($origem);
    }
    foreach($dst as $destino) {
        $regra->addDst($destino);
    }

    // Definindo ordem de gravação
    if($gravacao == 'S') {
        $regra->record();
    }

    // Define prioridade
    $regra->setPriority($prioridade);

    $regra->cleanActionsList();
    // percorre array de acoes cadastradas e instancia os objetos de Acao e os inclui na regra.
    foreach($action as $tipo => $acao) {
    
        switch (substr($tipo,-1)) {
            // alterar, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'a':
                $reg = new PBX_Rule_Action_Rewrite();

                $conf = array(
                    'type' => $acao['ct'],
                    'cut'  => $acao['cc']
                );

                if($acao['to'] != "") {
                    $conf['prefix'] = $acao['to'];
                }
                
                if($acao['tl'] != "") {
                    $conf['tl'] = $acao['tl'];
                }

                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // tronco, instancia ação, adiciona configuracao e adiciona acao a regra
            case 't':
                $cc = new PBX_Rule_Action_CCustos();
                $cc->setConfig(array('ccustos' => $acao['cc']));
                $regra->addAcao($cc);
                $reg = new PBX_Rule_Action_DiscarTronco();                
                $conf = array('tronco' => $acao['tnk'], 'dial_timeout' => $acao['to'], 'dial_limit' => $acao['tl'], 'omit_kgsm' => $acao['omo'], 'dial_flags' => $acao['fg'], 'alertEmail' => $acao['em']);
                $reg->setConfig($conf);
                $regra->addAcao($reg);                
                break;
            // fila , instancia ação, adiciona configuracao e adiciona acao a regra
            case 'q':
                $cc = new PBX_Rule_Action_CCustos();
                $cc->setConfig(array('ccustos' => $acao['cc']));
                $regra->addAcao($cc);
                $reg = new PBX_Rule_Action_Queue();
                $conf = array('queue' => $acao['fl'], 'timeout' => $acao['to']);                
                $reg->setConfig($conf);
                $regra->addAcao($reg);                
                break;
            // contexto, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'c':                
                $reg = new PBX_Rule_Action_GoContext();
                $conf = array('context' => $acao['ct']);                
                $reg->setConfig($conf);
                $regra->addAcao($reg);                
                break;
            // cadeado, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'p':
                $reg = new PBX_Rule_Action_Cadeado();
                $conf = array(
                    'senha' => $acao['ct'],
                    'ask_peer' => ($acao['cc']?"true":"false")
                );
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // loop, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'l':
                $reg = new PBX_Rule_Action_ActionLoop();
                $conf = array('loopcount' => $acao['ct'], 'actionindex' => $acao['cc']);
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // ramal, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'e':
                $cc = new PBX_Rule_Action_CCustos();
                $cc->setConfig(array('ccustos' => $acao['cc']));
                $regra->addAcao($cc);
                $reg = new PBX_Rule_Action_DiscarRamal();
                $conf = array(
                    'dial_timeout' => $acao['to'],
                    'dial_flags' => $acao['tl'],
                    'dont_overflow' => ($acao['omo']?"true":"false"),
                    'diff_ring' => ($acao['fg']?"true":"false"),
                    'allow_voicemail' => ($acao['em']?"true":"false")
                );
                if(is_numeric($acao['rm'])) $conf['ramal'] = $acao['rm'];
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // Definir origem/destino
            case 'd':
                // Adicionando reescrita de Origem
                if(isset($acao['cc'])) {
                    $reg = new PBX_Rule_Action_Rewrite();
                    $conf = array('type' => 'src', 'replace' => $acao['cc']);
                    $reg->setConfig($conf);
                    $regra->addAcao($reg);
                }
                // Adicionando reescrita de Destino
                if(isset($acao['ct'])) {
                    $reg = new PBX_Rule_Action_Rewrite();
                    $conf = array('type' => 'dst', 'replace' => $acao['ct']);
                    $reg->setConfig($conf);
                    $regra->addAcao($reg);
                }
                break;
            // Restaurar origem/destino
            case 'r':
                $reg = new PBX_Rule_Action_Restore();
                $conf = array('origem' => $acao['cc'], 'destino' => $acao['ct']);
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
        }    
    }

    
    PBX_Rules::register($regra);
    

    echo "<meta http-equiv='refresh' content='0;url=../gestao/rel_agi_rules.php'>\n" ;
 }

/*------------------------------------------------------------------------------
  Funcao ALTERAR - Alterar um registro
------------------------------------------------------------------------------*/
function alterar()  {
    
   global $LANG, $db, $smarty, $titulo, $acao, $grupos;
   
   $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];

   if (!$codigo) {
      display_error($LANG['msg_notselect'],true) ;
      exit ;
   }

   // Pega regra em si.
   $regra = PBX_Rules::get($codigo);

   $srcList = $regra->getSrcList();
    $src = "origObj.addItem(" . count($srcList) . ");";
    foreach ($srcList as $index => $_src) {
        $src .= "origObj.widgets[$index].type='{$_src['type']}';\n";
        $src .= "origObj.widgets[$index].value='{$_src['value']}';\n";
    }

    $dstList = $regra->getDstList();
    $dst =  "dstObj.addItem(" . count($dstList) . ");";
    foreach ($dstList as $index => $_dst) {
        $dst .=  "dstObj.widgets[$index].type='{$_dst['type']}';\n";
        $dst .=  "dstObj.widgets[$index].value='{$_dst['value']}';\n";
    }

    $timeList = $regra->getValidTimeList();
    $time = "timeObj.addItem(" . count($timeList) . ");";
    foreach ($timeList as $index => $_time) {
        $_time = explode('-', $_time);
        $time .=  "timeObj.widgets[$index].startTime='{$_time[0]}';\n";
        $time .=  "timeObj.widgets[$index].endTime='{$_time[1]}';\n";
    }

   // Pega acoes da regra
   $acoes = $regra->getAcoes();

   $action = array();
   $ult_cc = '';
   // Percorre o array de regras e as reconhece pelo tipo de instancia de objeto.
   // monta array com informacoes especificas a serem repassadas a classe javascript.

   $ult_acao = null;
   foreach($acoes as $id => $acao) {

        if($acao instanceof PBX_Rule_Action_Ccustos) {
             $swp= $acao->getConfigArray();
             $ult_cc = $swp['ccustos'];
        }

        if($acao instanceof PBX_Rule_Action_DiscarTronco ) {
            $swp = $acao->getConfigArray();
            $action[] = array(
                'tipo'   => 't',
                'nome'   => $acao->getName(),
                'cc'     => $ult_cc,
                'tl'     => isset($swp['dial_limit']) ? $swp['dial_limit'] : "",
                'to'     => $swp['dial_timeout'],
                'omo'    => isset($swp['omit_kgsm']) ? $swp['omit_kgsm'] : "",
                'tronco' => $swp['tronco'],
                'fg'     => $swp['dial_flags'],
                'em'     => isset($swp['alertEmail']) ? $swp['alertEmail'] : ""
            );
            unset($swp);
        }

        if($acao instanceof PBX_Rule_Action_DiscarRamal ) {
            $swp = $acao->getConfigArray();
            $action[] = array(
                'tipo'  => 'e',
                'nome'  => $acao->getName(),
                'cc'    => $ult_cc,
                'to'    => $swp['dial_timeout'],
                'ramal' => $swp['ramal'],
                'tl'    => $swp['dial_flags'],
                'omo'   => $swp['dont_overflow'],
                'fg'    => $swp['diff_ring'],
                'em'    => $swp['allow_voicemail']
            );
            unset($swp);
        }

        if($acao instanceof PBX_Rule_Action_Queue ) {
            $swp = $acao->getConfigArray();
            $action[] = array(
                'tipo'  => 'q',
                'cc'    => $ult_cc,
                'queue' => $swp['queue'],
                'to'    => $swp['dial_timeout']
            );
            unset($swp);
        }

        if($acao instanceof PBX_Rule_Action_GoContext ) {
            $swp = $acao->getConfigArray();
            $action[] = array(
                'tipo' => 'c',
                'ct'   => $swp['context']
            );
            unset($swp);
        }

        if($acao instanceof PBX_Rule_Action_Cadeado ) {
            $swp = $acao->getConfigArray();
            $action[] = array(
                'tipo'  => 'p',
                'ct'    => $swp['senha'],
                'cc'    => $swp['ask_peer']
            );
            unset($swp);
        }

        if($acao instanceof PBX_Rule_Action_ActionLoop ) {
            $swp = $acao->getConfigArray();
            $action[] = array(
                'tipo'  => 'l',
                'cc'    => $swp['actionindex'] +1,
                'ct'    => $swp['loopcount']
            );
            unset($swp);
        }

        if($acao instanceof PBX_Rule_Action_Rewrite) {
            $swp = $acao->getConfigArray();
            // Caso a regra tenha instruções para substituição
            if (isset($swp['replace']) && $swp['replace'] != "") {
                if($ult_acao instanceof PBX_Rule_Action_Rewrite) {
                    $ult_acao_cfg = $ult_acao->getConfigArray();
                    $action[$last_rewrite_index] = array(
                        'tipo'  => 'd',
                        'cc'    => $swp['type'] == 'src' ? $swp['replace'] : ($ult_acao_cfg['type'] == 'src' ? $ult_acao_cfg['replace'] : ''),
                        'ct'    => $swp['type'] == 'dst' ? $swp['replace'] : ($ult_acao_cfg['type'] == 'dst' ? $ult_acao_cfg['replace'] : '')
                    );
                }
                else {
                    $action[] = array(
                        'tipo'  => 'd',
                        'cc'    => $swp['type'] == 'src' ? $swp['replace'] : '',
                        'ct'    => $swp['type'] == 'dst' ? $swp['replace'] : ''
                    );
                    $last_rewrite_index = count($action) -1;
                }
            }
            else {
                $action[] = array(
                    'tipo'  => 'a',
                    'cc'    => $swp['type'],
                    'ct'    => $swp['cut'],
                    'to'    => isset($swp['prefix']) ? $swp['prefix'] : '',
                    'tl'    => isset($swp['sufix']) ? $swp['sufix'] : ''
                );
                $index = count($action)-1;
            }
            unset($swp);
        }
        
        if($acao instanceof PBX_Rule_Action_Restore ) {
            $swp = $acao->getConfigArray();
            $action[] = array(
                'tipo'  => 'r',
                'cc'    => $swp['origem'],
                'ct'    => $swp['destino']
            );
            unset($swp);
        }
        $ult_acao = $acao;
   }
  

   // Tratamento do horario da regra
   $horario = $regra->getValidTimeList();
   $data = explode("-", $horario['0']);


   $smarty->assign('codigo', $codigo);
   $smarty->assign('dt_agirules',array("dst"=> $dst,
                                       "src"=> $src,
                                       "time"=>$time,
                                       "gravacao" => $regra->isRecording() ? 'S' : 'N',
                                       "descricao" => $regra->getDesc(),
                                       "autorizado"=>"S",
                                       "prioridade" => $regra->getPriority(),
                                       "ordem => 0"));
    $listaDiasSemana = $regra->getValidWeekDays();
    $smarty->assign('weekDays',array(
            "sun" => in_array("sun", $listaDiasSemana),
            "mon" => in_array("mon", $listaDiasSemana),
            "tue" => in_array("tue", $listaDiasSemana),
            "wed" => in_array("wed", $listaDiasSemana),
            "thu" => in_array("thu", $listaDiasSemana),
            "fri" => in_array("fri", $listaDiasSemana),
            "sat" => in_array("sat", $listaDiasSemana)
    ));
   $smarty->assign('ACAO',"grava_alterar") ;
  
   $js = ' <script type=\'text/javascript\'> ';
   foreach($action as $id => $acao) {
       
       switch ($acao['tipo']) {

            case 'a':
                //echo "<script type=\"text/javascript\">   </script> ";
                $js .= " x.newnode('alterar','".$action[$id]['cc']."','".$action[$id]['ct']."','".$action[$id]['to']."','".$action[$id]['tl']."','',''); ";
                break;
            case 't':
                //echo "<script type=\"text/javascript\">  </script> ";                
                $js .= " x.newnode('trunk','".$action[$id]['cc']."','".$action[$id]['tronco']."','".$action[$id]['to']."','".$action[$id]['tl']."','".$action[$id]['omo']."','".$action[$id]['fg']."','".$action[$id]['em']."');";
                break;
            case 'e':
                //echo "<script type=\"text/javascript\">   </script> ";
                $js .= "x.newnode('exten','".$action[$id]['cc']."','".$action[$id]['ramal']."','".$action[$id]['to']."','".$action[$id]['tl']."','".$action[$id]['omo']."','" . $action[$id]['fg'] . "','{$action[$id]['em']}');";
                break;
            case 'q':
                //echo "<script type=\"text/javascript\">   </script> ";
                $js .= "x.newnode('queue','".$action[$id]['cc']."','".$action[$id]['queue']."','".$action[$id]['to']."','','','');";
                break;
            case 'c':
                //echo "<script type=\"text/javascript\">   </script> ";
                $js .= "x.newnode('context','','".$action[$id]['ct']."','','','','');";
                break;
            case 'p':
                //echo "<script type=\"text/javascript\">   </script> ";
                $js .= "x.newnode('padlock','{$action[$id]['cc']}','{$action[$id]['ct']}','','','','');";
                break;
            case 'l':
                //echo "<script type=\"text/javascript\">   </script> ";
                $js .=  "x.newnode('loop','".$action[$id]['cc']."','".$action[$id]['ct']."','','','','');";
                break;
            case 'd':
                //echo "<script type=\"text/javascript\">   </script> ";
                $js .= "x.newnode('define','".$action[$id]['cc']."','".$action[$id]['ct']."','','','','');";
                break;
            case 'r':
                //echo "<script type=\"text/javascript\">   </script> ";
                $js .= "x.newnode('restore','".$action[$id]['cc']."','".$action[$id]['ct']."','','','','');";
                break;
        }
    }
    $js .= "</script>";    
     $smarty->assign ('JS', $js);
     display_template("agi_rules.tpl",$smarty,$titulo);
}

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function grava_alterar()  {
   global $LANG, $db, $gravacao, $prioridade, $descricao, $autorizado, $timeValue, $codigo, $ccustos,  $canais, $routes_red, $alias, $ordem, $mixmonitor, $mixmonitor_flags, $dial_timeout, $dial_flags, $route, $opercode, $srcValue, $dstValue, $grupo_src, $grupo_dst, $redccustos, $redopercode, $callerid, $redcallerid, $dial_limit, $dial_warn, $kgsm_restricted;

   $codigo = $_POST['codigo'];
   $regra = PBX_Rules::get($codigo);

   $diasDaSemana = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");
   $regra->cleanValidWeekList();
   foreach ($diasDaSemana as $dia) {
       if( key_exists($dia, $_POST) ) {
           $regra->addWeekDay($dia);
       }
   }

    $regra->srcClean();
    foreach (explode(',', $srcValue) as $src) {
        if(!strpos($src, ':')) {
            $regra->addSrc(array("type" => $src, "value" => ""));
        }
        else {
            $info = explode(':', $src);
            if(!is_array($info) OR count($info) != 2) {
                throw new PBX_Exception_BadArg("Valor errado para origem da regra de negocio.");
            }
            $regra->addSrc(array("type" => $info[0], "value" => $info[1]));
        }
    }
    
    $regra->dstClean();
    foreach (explode(',', $dstValue) as $dst) {
        if(!strpos($dst, ':')) {
            $regra->addDst(array("type" => $dst, "value" => ""));
        }
        else {
            $info = explode(':', $dst);
            if(!is_array($info) OR count($info) != 2) {
                throw new PBX_Exception_BadArg("Valor errado para destino da regra de negocio.");
            }
            $regra->addDst(array("type" => $info[0], "value" => $info[1]));
        }
    }

    $regra->cleanValidTimeList();
    foreach (explode(',', $timeValue) as $validade) {
        $regra->addValidTime($validade);
    }

   // Tratamento das ações
   $indice = $_POST['indice'];
   $cap = explode(',', trim($_POST['ids'],","));

   $action = array();

   foreach($cap as $ordem => $acoes) {

         switch (substr($acoes,0,1)) {
            case 'a':
                $ct = $acoes.'ct'; // Tipo de ação Origem ou Destino
                $cc = $acoes.'cc'; // Instrução de Corte
                $to = $acoes.'to'; // Prefixo
                $tl = $acoes.'tl'; // Sufixo
                $action[$ordem.'a']['cc']  = $_POST[$cc];
                $action[$ordem.'a']['ct']  = $_POST[$ct];
                $action[$ordem.'a']['to']  = $_POST[$to];
                $action[$ordem.'a']['tl']  = $_POST[$tl];
                break;
           case 't':
                $cc = $acoes.'cc';
                $tnk = $acoes.'tnk';
                $to = $acoes.'to';
                $tl = $acoes.'tl';
                $omo = $acoes.'omo';
                $fg = $acoes.'fg';
                $em = $acoes.'em';
                $action[$ordem.'t']['cc']  = $_POST[$cc];
                $action[$ordem.'t']['tnk'] = $_POST[$tnk];
                $action[$ordem.'t']['to']  = $_POST[$to];
                $action[$ordem.'t']['tl']  = $_POST[$tl];
                $action[$ordem.'t']['fg']  = $_POST[$fg];
                $action[$ordem.'t']['em']  = $_POST[$em];
                $action[$ordem.'t']['omo']  = ( $_POST[$omo] ? 1 : 0);
                break;
            case 'e':
                $cc  = $acoes.'cc';
                $rm  = $acoes.'rm';
                $to  = $acoes.'to';
                $tl  = $acoes.'tl';
                $omo = $acoes.'omo';
                $fg  = $acoes.'fg';
                $em  = $acoes.'em';
                $action[$ordem.'e']['cc']  = $_POST[$cc];
                $action[$ordem.'e']['rm']  = $_POST[$rm];
                $action[$ordem.'e']['to']  = $_POST[$to];
                $action[$ordem.'e']['tl']  = $_POST[$tl];
                $action[$ordem.'e']['omo'] = $_POST[$omo];
                $action[$ordem.'e']['em']  = $_POST[$em];
                $action[$ordem.'e']['fg'] = $_POST[$fg];
                break;
            case 'd':
                $cc = $acoes.'cc';
                $ct = $acoes.'ct';
                $action[$ordem.'d']['ct'] = $_POST[$ct];
                $action[$ordem.'d']['cc'] = $_POST[$cc];
                break;
            case 'r':
                $cc = $acoes.'cc';
                $ct = $acoes.'ct';
                $action[$ordem.'r']['ct'] = $_POST[$ct] == 'on'?true:false;
                $action[$ordem.'r']['cc'] = $_POST[$cc] == 'on'?true:false;
                break;
            case 'c':
                $ct = $acoes.'ct';
                $action[$ordem.'c']['ct'] = $_POST[$ct];
                break;
            case 'p':
                $ct = $acoes.'ct';
                $cc = $acoes.'cc';
                $action[$ordem.'p']['ct'] = $_POST[$ct];
                $action[$ordem.'p']['cc'] = $_POST[$cc];
                break;
            case 'l':
                $cc = $acoes.'cc';
                $ct = $acoes.'ct';
                $action[$ordem.'l']['ct'] = $_POST[$ct];
                $action[$ordem.'l']['cc'] = $_POST[$cc]-1;
                break;
            case 'q':
                $cc = $acoes.'cc';
                $fl = $acoes.'fl';
                $to = $acoes.'to';
                $action[$ordem.'q']['cc'] = $_POST[$cc];
                $action[$ordem.'q']['fl'] = $_POST[$fl];
                $action[$ordem.'q']['to'] = $_POST[$to];
                break;
        }
   }

    // Instancia um objeto do tipo regra
    $regra->setId($codigo);

    // Adiciona Descricao
    $regra->setDesc($descricao);

    // Define prioridade
    $regra->setPriority($prioridade);

    // Definindo ordem de gravação
    if($gravacao == 'S') {
        $regra->record();
    }
    else {
        $regra->dontRecord();
    }

    $regra->cleanActionsList();
    // percorre array de acoes cadastradas e instancia os objetos de Acao e os inclui na regra.
    foreach($action as $tipo => $acao) {

        switch (substr($tipo,-1)) {
            // alterar, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'a':
                $reg = new PBX_Rule_Action_Rewrite();

                $conf = array(
                    'type' => $acao['ct'],
                    'cut'  => $acao['cc']
                );

                if($acao['to'] != "") {
                    $conf['prefix'] = $acao['to'];
                }

                if($acao['tl'] != "") {
                    $conf['tl'] = $acao['tl'];
                }

                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // tronco, instancia ação, adiciona configuracao e adiciona acao a regra
            case 't':
                $cc = new PBX_Rule_Action_CCustos();
                $cc->setConfig(array('ccustos' => $acao['cc']));
                $regra->addAcao($cc);
                $reg = new PBX_Rule_Action_DiscarTronco();
                $conf = array('tronco' => $acao['tnk'], 'dial_timeout' => $acao['to'], 'dial_limit' => $acao['tl'], 'omit_kgsm' => $acao['omo'], 'dial_flags' => $acao['fg'], 'alertEmail' => $acao['em']);
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // fila , instancia ação, adiciona configuracao e adiciona acao a regra
            case 'q':
                $cc = new PBX_Rule_Action_CCustos();
                $cc->setConfig(array('ccustos' => $acao['cc']));
                $regra->addAcao($cc);
                $reg = new PBX_Rule_Action_Queue();
                $conf = array('queue' => $acao['fl'], 'timeout' => $acao['to']);
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // contexto, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'c':
                $reg = new PBX_Rule_Action_GoContext();
                $conf = array('context' => $acao['ct']);
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // cadeado, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'p':
                $reg = new PBX_Rule_Action_Cadeado();
                $conf = array(
                    'senha' => $acao['ct'],
                    'ask_peer' => ($acao['cc']?"true":"false")
                );
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
                        // loop, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'l':
                $reg = new PBX_Rule_Action_ActionLoop();
                $conf = array('loopcount' => $acao['ct'], 'actionindex' => $acao['cc']);
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // ramal, instancia ação, adiciona configuracao e adiciona acao a regra
            case 'e':
                $cc = new PBX_Rule_Action_CCustos();
                $cc->setConfig(array('ccustos' => $acao['cc']));
                $regra->addAcao($cc);
                $reg = new PBX_Rule_Action_DiscarRamal();
                $conf = array(
                    'dial_timeout' => $acao['to'],
                    'dial_flags' => $acao['tl'],
                    'dont_overflow' => ($acao['omo']?"true":"false"),
                    'diff_ring' => ($acao['fg']?"true":"false"),
                    'allow_voicemail' => ($acao['em']?"true":"false")
                );
                if(is_numeric($acao['rm'])) $conf['ramal'] = $acao['rm'];
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
            // Definir origem/destino
            case 'd':
                // Adicionando reescrita de Origem
                if($acao['cc'] != "") {
                    $reg = new PBX_Rule_Action_Rewrite();
                    $conf = array('type' => 'src', 'replace' => $acao['cc']);
                    $reg->setConfig($conf);
                    $regra->addAcao($reg);
                }
                // Adicionando reescrita de Destino
                if($acao['ct'] != "") {
                    $reg = new PBX_Rule_Action_Rewrite();
                    $conf = array('type' => 'dst', 'replace' => $acao['ct']);
                    $reg->setConfig($conf);
                    $regra->addAcao($reg);
                }
                break;
            // Restaurar origem/destino
            case 'r':
                $reg = new PBX_Rule_Action_Restore();
                $conf = array('origem' => $acao['cc'], 'destino' => $acao['ct']);
                $reg->setConfig($conf);
                $regra->addAcao($reg);
                break;
        }
    }

    PBX_Rules::update($regra);
    echo "<meta http-equiv='refresh' content='0;url=../gestao/rel_agi_rules.php'>\n" ;

 }

/*------------------------------------------------------------------------------
  Funcao EXCLUIR - Excluir registro selecionado
------------------------------------------------------------------------------*/
function excluir()  {
   global $LANG, $db;
   $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];

   if (!$codigo OR !is_numeric($codigo)) {
      display_error($LANG['msg_notselect'],1) ;
      exit ;
   }

   //$regra = new PBX_Rule();
   PBX_Rules::delete($codigo);

   echo "<meta http-equiv='refresh' content='0;url=../gestao/rel_agi_rules.php'>\n" ;
}
