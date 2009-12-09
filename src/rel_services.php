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

ver_permissao(102);
 
global $acao, $prefix_inout, $filas ;

$prefix_inout = $SETUP['ambiente']['prefix_inout'];
$dst_exceptions = $SETUP['ambiente']['dst_exceptions'];
    
    if ($acao == "relatorio" || $acao == "csv") 
    {        
        monta_relatorio($acao); 
    } 
    elseif ($acao == "imp") 
    {
        exibe_relatorio() ;     
    }

    /* Busca servi�os d�sponiveis */

    try {
       $sq_srv = " SELECT DISTINCT service FROM services_log " ;
       $srv = $db->query($sq_srv)->fetchAll();
    } catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
       exit ;
    }
    $service = array();
    foreach($srv as $srv) {
        $service[] = $srv['service'];

    }

    /* Grupos de Ramais */

    $sql = "SELECT * FROM groups" ;
     try {
        $row = $db->query($sql)->fetchAll();

     } catch (Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;

     }

     $g = array(''=>'');
     foreach ($row as $key => $group) {

        switch($group['name']) {
            case 'admin':
                $g[$group['name']] = 'Administradores';
                break;
            case 'users':
                $g[$group['name']] = 'Usu&aacute;rios';
                break;
            case 'all':
                $g[$group['name']] = 'Todos';
                break;
            default:
                $g[$group['name']] = $group['name'];
        }
    }
    /* Determina data inicial e final */
    $dados_iniciais = array("dia_ini" => ( $_SESSION['relservices']['dia_ini'] ? $_SESSION['relservices']['dia_ini'] : date('d/m/Y') ) ,
                            "hora_ini"=> ( $_SESSION['relservices']['hora_ini'] ? $_SESSION['relservices']['hora_ini'] : '00:00' ),
                            "dia_fim" => ( $_SESSION['relservices']['dia_fim'] ? $_SESSION['relservices']['dia_fim'] : date('d/m/Y') ),
                            "hora_fim"=> ( $_SESSION['relservices']['hora_fim'] ? $_SESSION['relservices']['hora_fim'] : '23:59'),
                            "src" => ( $_SESSION['relservices']['src'] ? $_SESSION['relservices']['src'] : '')
) ;


/* Monta  formulario de busca */        
$titulo = $LANG['menu_reports']." -> ".$LANG['services_report'] ;
$smarty->assign ('VINCULOS', monta_vinculo($_SESSION['vinculos_user'],"L")) ;
$smarty->assign ('dt_relchamadas',$dados_iniciais) ;
$smarty->assign ('groupsrc', $_SESSION['relservices']['groupsrc']);
$smarty->assign ('OPCOES_USERGROUPS',$g);
$smarty->assign ('SERVICES', $service);
display_template("rel_services.tpl",$smarty,$titulo) ;

/* Monta Relat�rio */
/*------------------------------------------------------------------------------------------*/
function monta_relatorio($acao) {
    global  $LANG, $db, $smarty, $state, $dia_ini, $dia_fim, $hora_ini, $hora_fim, $src, $prefix_inout, $SETUP, $my_object, $acao, $groupsrc, $services ;

  $_SESSION['relservices']['dia_ini'] = $dia_ini;
  $_SESSION['relservices']['dia_fim'] = $dia_fim;
  $_SESSION['relservices']['hora_ini'] = $hora_ini;
  $_SESSION['relservices']['hora_fim'] = $hora_fim;
  $_SESSION['relservices']['src'] = $src;
  $_SESSION['relservices']['groupsrc'] = $groupsrc;

  /*  Verifica Dia final */
  if($dia_fim) {
      $dia_final  = substr($dia_fim,6,4)."-".substr($dia_fim,3,2)."-".substr($dia_fim,0,2);    
  }
  else {
      $dia_fim = date("Y-m-d");
      $dia_final = substr($dia_fim,6,4)."-".substr($dia_fim,3,2)."-".substr($dia_fim,0,2);
  }
    
  /*  Verifica Dia inicial */  
  if($dia_ini) {
      $dia_inicial = substr($dia_ini,6,4)."-".substr($dia_ini,3,2)."-".substr($dia_ini,0,2);     
  }else {
      $dia_ini = gmdate("Y-m-d", time()-(3600*27));
      $dia_inicia = substr($dia_ini,6,4)."-".substr($dia_ini,3,2)."-".substr($dia_ini,0,2);
  }

  /* Servi�os selecionados na busca */
  $srv = '';
  if($services) {
      foreach($services as $service) {
          $srv .= "'$service',";

      }
  $srv = " AND service IN (".substr($srv, 0, -1).")";

  }
  
  /* Busca os ramais pertencentes ao grupo de ramal de orgem selecionado */
  $ramaissrc = $ramaisdst = "" ;
  
  /* Busca os ramais pertencentes ao grupo de ramal de origem selecionado */
  if($groupsrc) {
      $origens = PBX_Usuarios::getByGroup($groupsrc);
      if( count($origens) == 0 ) {
          display_error($LANG['error'] . $LANG['error_nogroup_item'] ,true);
      }
      else {
          foreach ($origens as $ramal) {
              $ramalsrc .= $ramal->getNumero() . ',';
          }
          $ramaissrc = " AND peer in (" . trim($ramalsrc, ',') . ") ";
      }
  }
  if($src)  {

      $list_src = array();
      $src = explode(",", $src);
      
      foreach($src as $ram_src) {
          if(!in_array($ram_src, $list_src)) {
              $list_src[] = $ram_src;
          }
      }

      foreach($list_src as $valor) {
          $list .= $valor.',';

      }
      $ramaissrc = " AND services_log.peer IN (".substr($list, 0, -1).") ";
      
  }

  /* Verifica estado do servi�o. */
  $state_cnt = count($state);
  if($state_cnt == 2) {
      $estado = " ";
  }else{
      if($state[0] == "0") {
          $estado = " AND services_log.state = '0' ";
      }
      if($state[0] == "1") {
          $estado = " AND services_log.state = '1' ";
      }
  }
  
  
  /*  Monta query para Data */
  $date_clause =" ( date >= '$dia_inicial'";
  $date_clause.=" AND date <= '$dia_final 23:59:59'"; //'
  $date_clause.=" AND DATE_FORMAT(date,'%T') >= '$hora_ini:00'";
  $date_clause.=" AND DATE_FORMAT(date,'%T') <= '$hora_fim:59') ";
  
  $CONDICAO .= " $date_clause " ;
  $_SESSION['titulo_2'] = '<br />Periodo: '.$dia_ini.' ('.$hora_ini.') a '. $dia_fim .' ('.$hora_fim.') ';

  $sql = " SELECT * FROM services_log WHERE ";
  $sql.= $CONDICAO . $estado ;
  $sql.= ($ramaissrc ? $ramaissrc : '');
  $sql.= ($srv ? $srv : '');
  $sql.= " ORDER BY services_log.peer ";
  

  $_SESSION['sql_chamadas'] = $sql;  
  $_SESSION['filtros'] = $filtros;

    echo "<meta http-equiv='refresh'  content='0; url=./rel_services.php?acao=imp&t=$acao'>\n" ;
  }
    
/* Exibe relat�rio */
/*------------------------------------------------------------------------------------------*/
function exibe_relatorio() {
 $my_object = new Formata ;
 global $db, $smarty, $SETUP, $LANG, $tipos_disp, $tp_rel, $acao, $status_filas;

   $_SESSION['sql_chamadas'];

    try{
         $query = $_SESSION['sql_chamadas'];
         $row = $db->query($query)->fetchAll();

         $cnt = count($row);
         if($cnt < 1) {
                display_error($LANG['msg_notdata'],true) ;
         }

         foreach($row as $rindex => $rvalue) {

         $row[$rindex]['date'] = substr($rvalue['date'],8,2) ."-". substr($rvalue['date'],5,2) ."-". substr($rvalue['date'],0,4) ." ". substr($rvalue['date'],11,10);;

         }
         
         $totais = count($row);
         $_SESSION['totais'] = $totais;
         
      }catch(Exception $e) {
          display_error($LANG['error'].$e->getMessage(),true) ;
          exit ;
      }

  /* Percorre o array e identifica registros pertencentes a mesma chamada */
    
     $tp_rel = $_GET['t'] ; 
     if ($tp_rel == "csv") {
                
        /*  Array que define os campos (ids) e os titulos (values) do CSV.  */
        $titulo = array(
                        "date" => $LANG['only_date'],
                        "peer" => $LANG['ramal'],
                        "service" => $LANG['service_enable'],
                        "status" => $LANG['service_status']
                        );
        /* Chama fun��o monta_csv, passando array $titulo e o resultado da query $row */ 
        $csv_rel_filas = monta_csv($titulo, $row);
  }

$opcoes_procura = array("ramal" => ramal, "numero" => numero);


/* Monta array com totaliza��es de registros. */      
$totais = array("answered"=>$tot_ans,"notanswer"=>$tot_noa,"abandon"=>$tot_aba, "endbyagent" =>$tot_end_a, "endbycaller" =>$tot_end_c);

$tot_pages = ceil(count($row)/$SETUP['ambiente']['linelimit']) ;
 for ($i = 1 ; $i <= $tot_pages ; $i ++ )
     $paginas[$i] = $i;

$titulo = $LANG['menu_reports']." -> ".$LANG['services_report'];
$titulo.= $_SESSION['titulo_2']; 
$smarty->register_object("formata",$my_object) ; 
$smarty->assign ('DADOS',$row);
$smarty->assign ('TOT',$tot_pages);
$smarty->assign ('PAGINAS',$paginas) ;
$smarty->assign ('TPREL',$tp_rel) ;
$smarty->assign ('ARQCVS',$csv_rel_filas);
$smarty->assign ('OPCOES_PROCURA', $opcoes_procura);
$smarty->assign ('INI',1);
$smarty->assign ('TOTAIS',$totais) ;
display_template("rel_services_view.tpl",$smarty,$titulo);
exit ;    
}
?>