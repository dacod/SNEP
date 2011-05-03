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
 ver_permissao(47);
 $dst_exceptions = $SETUP['ambiente']['dst_exceptions'];
 global $acao ;
 if ($acao == "gerar") {
    // Cria Objeto para formtacao de dados
    $my_object = new Formata ;
    gerar_tarifacao();
 } 
 // Dados do Periodo gerado de tarifacao
 try {
   $sql = "SELECT date_format(calldate,'%d/%m/%Y %H:%i:%s') as dia" ;
   $sql.= " FROM cdr_tarifado ORDER BY calldate LIMIT 1" ;
   $row = $db->query($sql)->fetch();
   $atual_ini = $row['dia'] ;   
   $sql = "SELECT date_format(calldate,'%d/%m/%Y %H:%i:%s') as dia" ;
   $sql.= " FROM cdr_tarifado ORDER BY calldate DESC LIMIT 1" ;
   $row = $db->query($sql)->fetch();
   $atual_fim = $row['dia'] ;
   $dia_ini = substr($atual_fim,0,10) ;
   $hora_ini = substr($atual_fim,11,8) ;
   if ($atual_fim == "") {
      $sql = "SELECT date_format(calldate,'%d/%m/%Y %H:%i:%s') as dia" ;
      $sql.= " FROM cdr ORDER BY calldate LIMIT 1" ;
      $row = $db->query($sql)->fetch();
      $dia_ini = substr($row['dia'],0,10) ;
      $hora_ini = substr($row['dia'],11,5) ;
   }
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }

 
 $dados_iniciais= array("atual_ini" => $atual_ini,
                        "atual_fim" => $atual_fim,
                        "dia_ini" => $dia_ini,
                        "hora_ini"=> $hora_ini ,
                        "dia_fim" => date('d/m/Y'),
                        "hora_fim"=> date('H:i:s'));
 $titulo = $LANG['menu_tarifas']." -> ".$LANG['menu_tarifacao'];
 $smarty->assign ('dt_tarifacao',$dados_iniciais) ;
 display_template("gerar_tarifacao.tpl",$smarty,$titulo) ;
 
/*-----------------------------------------------------------------------------
 *Funcao gerar_tarifacao - Atualiza tabela cdr_tarifado
 * ----------------------------------------------------------------------------*/
 function gerar_tarifacao()  {
  global $LANG, $db, $dia_ini, $dia_fim, $hora_ini, $hora_fim, $prefix_inout, $dst_exceptions;
  $start_geral = microtime(1);
  $ctd = $total = 0;
  
  //---->>>> Monta SQL de abrangencia da tabela CDR <<<<----//
  $dia_inicial= substr($dia_ini,6,4)."-".substr($dia_ini,3,2)."-".substr($dia_ini,0,2);
  $dia_final  = substr($dia_fim,6,4)."-".substr($dia_fim,3,2)."-".substr($dia_fim,0,2);
  $date_clause.=" ( calldate >= '$dia_inicial $hora_ini:00'";
  $date_clause.=" AND calldate <= '$dia_final $hora_fim:59'";

  $CONDICAO = " WHERE $date_clause" ;

  //---->>>> Prefixos de Login/Logout
  if (strlen($prefix_inout)>6) {
         $COND_PIO = "" ;
         $array_prefixo = explode(";",$prefix_inout) ;

          foreach ($array_prefixo as $valor) {
              $par = explode("/", $valor);

              $pio_in = $par[0];
              $pio_out = $par[1];

              $t_pio_in = strlen($pio_in) ;
              $t_pio_out = strlen($pio_out) ;

              $COND_PIO .= " substr(dst,1,$t_pio_in) != '$pio_in' ";
                    if (! $pio_out == '') {
                        $COND_PIO .= " AND substr(dst,1,$t_pio_out) != '$pio_out' ";
                    }
              $COND_PIO .= " AND " ;

          }
     if ($COND_PIO != "")
        $CONDICAO .= " AND ( ".substr($COND_PIO, 0, strlen($COND_PIO) - 4). " ) " ;
  }
  // Filtro de Descarte
  $TMP_COND = "" ;
  $dst_exceptions = explode(";",$dst_exceptions) ;
  foreach ($dst_exceptions as $valor) {
     $TMP_COND .= " dst != '$valor' " ;
     $TMP_COND .= " AND " ;
  }
  $CONDICAO .= " AND ( ".substr($TMP_COND, 0, strlen($TMP_COND) - 4). " ) " ;
  
  // Somente BILLSEC >  0
  $CONDICAO .= " AND billsec > 0 )" ;
  
  // Montagem do SELECT de Consulta
  $SELECT  = "src, dst, disposition, duration, accountcode, userfield, dcontext, amaflags, channel, billsec,dstchannel,calldate" ;
  // Consulta de sql para verificar quantidade de registros selecionados e
  // Montar lista de Totais por tipo de Status
  try {
      $sql_chamadas = "SELECT " . $SELECT ." FROM cdr" . $CONDICAO ."  ORDER BY userfield,calldate,amaflags" ;
      $flag = "" ;
      $quebra = True ;
      
      foreach ($db->query($sql_chamadas) as $row) {
         $total ++ ;
         if ($flag != $row['userfield']) {            
            $flag = $row['userfield'] ;
            if (!isset($disposition) || $disposition == "") {
               $disposition = $row['disposition'] ;
               $quebra = False ;
               continue ;               
            }
            $quebra = True ;
         } else {
            $disposition = $row['disposition'] ;
            $quebra = False ;
            continue ;
         }         
         if (!$quebra)
            continue ;
         // Calcula Valor da Tarifa
         // -----------------------
         if ($disposition == "ANSWERED") {
            $start = microtime(true) ;
            $valor = calcula_tarifa($row['dst'],$row['billsec'],
                                    $row['accountcode'],$row['calldate']) ;
            $end = microtime(true) ;
            
            if ($valor === 0) {
            } else {
               $ctd ++ ;                
               echo "<br>".$row['calldate']." : ".$valor[2]." (".$row['billsec'].") ->".$row['accountcode']." == R$ ".$valor[0] ;
               echo "<br>==>> Tempo = ".number_format($end - $start,0,".",",");
                
            }
            
         }
         $disposition = $row['disposition'] ;
         unset($quebra);
      } // Fim do Foreach
         // Cacula Valor da Tarifa do ultimo registro
         // -----------------------------------------
      

  } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
    exit ;
  } // Fim to try
  $end_geral = microtime(1);
  echo "<br>==>> Total Reg's: $total   Processados: $ctd    Tempo = ".number_format($end_geral - $start_geral,0,".",",");
  
  exit ;
} // Fim da Funcao gerar_tarifacao

/*-----------------------------------------------------------------------------
 *Funcao calcula_tarifa - Calcula Tarifa de uma Ligacao
 * Recebe: destino - campo 'dst' da tabela CDR
 *         duracao - campo 'billsec' da tabela CDR
 *         conta   - campo 'accountcode' da tabela CDR
 *         dt_chamada - campo 'calldate' da tabela CDR
 * Retorna: array (valor, cidade, estado, tp_fone, dst_fmtd)
 * ----------------------------------------------------------------------------*/
 function calcula_tarifa($destino, $duracao, $conta, $dt_chamada) {
   global $db, $my_object ;
   
   $dbg = 1; // Debug =- 1 retorna string com dados encontrados ;
   
   // Aceita somente destino de 8,11 ou 13 digitos 
   $tn = strlen($destino) ;
   if ( $tn < 8 ) {
      return 0 ;
   }
   // Separa o numero do telefone em 3 partes: telefone , ddd, e ddi
   $num_dst = substr($destino,-4) ;
   $prefixo = substr($destino,-8,4) ;
   $ddd_dst = substr($destino,-10,2) ;
   if ( $tn == 11 ) {
      $ddi_dst = "" ;
   } elseif  ($tn > 13 ) {
      $ddi_dst = "" ;
   }
   $dst_fmtd = "$ddi ($ddd_dst) $prefixo-$num_dst";

   if ($dbg==1) {
      echo "<hr>CONTA=$conta == DATA=$dt_chamada == TEMPO=$duracao <br>DST = $dst_fmtd" ;
   }
   
   // Pesquisa cidades no CNL - Anatel
   $array_cidade = $my_object->fmt_cidade(array("a"=>$destino),"A") ;
   $cidade = $array_cidade['cidade'] ;
   if ($array_cidade['flag'] == "S") 
      $nome_cidade = substr($cidade,0,strlen($cidade)-3) ;
   else
      $nome_cidade = "" ;

   if ($dbg==1) {
      echo " // CIDADE = $cidade($nome_cidade)" ;
   }

   // Conecta no BD e pega dados da operadora, baseado no "accountcode"
   try {
      // Pega dados da Operadora
      $sql = "SELECT * FROM operadoras WHERE codigo = " ;
      $sql.= " (SELECT operadora FROM oper_contas  ";
      $sql.= " LEFT JOIN contas ON contas.codigo = oper_contas.conta " ;
      $sql.= " WHERE UPPER(contas.nome) = UPPER('$conta'))" ;
      $row = $db->query($sql)->fetch();
   } catch (Exception $e) {
      display_error("1)".$LANG['error'].$e->getMessage(),true) ;
   }   
   $operadora = $row['codigo'];
   $tpm       = $row['tpm'];   // Tempo do 1o. minuto da operadora - em seg
   $tdm       = $row['tdm'];   // Tempo em segundos dos intervalos subsequentes
   $tbf       = $row['tbf'];   // Valor Padrao para Fixo
   $tbc       = $row['tbc'];   // Valor Padrao para Celular

   if ($dbg==1) {
      echo " // OPERADORA=$operadora , TPM=$tpm , TDM=$tdm , TBF=$tbf , TBC=$tbc " ;
   }
   
   if ($operadora == "")
      return array(0,$cidade,$dst_fmtd) ;
   /* Pega dados das tarifas conforme requisitos (operadora, ddi , ddd e prefixo)
      Condicoes do cadastro de tarifas - ATENCAO: Diferentes cidades tem o mesmo DDD
      1) ddd valido + prefixo valido - Tarifa especial para o prefixo
      2) ddd valido + prefixo=0000   - Tarifa generica para os prefixo do ddd
      3) ddi valido + ddd=valido + prefixo=0000 - Tarifa para determinada regiao do pais
      4) ddi valido + ddd=0 + prefixo=0000 - Tarifa generica para o pais
   */
   try {
      $sql =  "SELECT * FROM tarifas WHERE operadora = $operadora";
      // Se uma cidade foi encontrada na tabela CNL ...
      if ($nome_cidade != "")
         $sql .= " AND cidade = '$cidade'" ;
      if ($ddi_dst != "" && $ddi_dst != 55)   // se nao for BRASIL, considera DDI 
         $sql .= " AND ddi = $ddi_dst " ;
      $sql.= " AND (prefixo = '$prefixo' OR prefixo = '0000') ";
      $sql .= " order by ddi,ddd,prefixo" ;
      $cod_tarifa = "" ;
      foreach ($db->query($sql) as $row){
         $cod_tarifa = $row['codigo'] ;
         // Varre registros encontrados e para na primeira condicao satisfeita
         if ($prefixo == $row['prefixo']) {  // um prefixo equivalente ...
            break ;
         } elseif ($row['prefixo'] == "0000") {  // um prefixo GENERICO
            break ;
         }
      }

      if ($dbg==1) {
         echo " // COD_TARIFA=$cod_tarifa" ;
      }
      
      // Se encontrou TARIFA equivalente, pega valores compativeis com CALLDATE      
      if ($cod_tarifa != "") {
         $sql = "SELECT * FROM tarifas_valores where codigo = $cod_tarifa" ;
         $sql.= " AND data >= '$dt_chamada'";
         $sql.= "order by data" ;
         foreach ($db->query($sql) as $row){
             $tbf = $row['vfix'] ;
             $tbc = $row['vcel'] ;
         }
         
         if ($dbg==1) {
            echo " // TBF=$tbf , TBC=$tbc " ;
         }
      }
   } catch (Exception $e) {
     display_error("2".$LANG['error'].$e->getMessage(),true) ;
   }    

   // Calcula o tempo do primeiro minuto e desconta o tempo restante
   $tp_fone = (strlen($destino) >= 8 && substr($prefixo,-4,1) > 6) ? "C" : "F" ;
   $tpo_resta = $duracao - $tpm ;
   if ($tp_fone=='C')
      $tarifa = $tbc ;
   else
      $tarifa = $tbf ;
   $valor_prop = 0 ;
   if ($tpo_resta > 0) { 
      $tarifa_prop = ( $tarifa / (60/$tdm) ) ;
      $qtd_de_prop = ( (int)( $tpo_resta / $tdm ) +1 ) ;
      $valor_prop = ($qtd_de_prop * $tarifa_prop);
   } 
   if ($dbg == 1) {
      echo " // TIPOFONE = $tp_fone // TARIFA = $tarifa // VALOR_PROP=$valor_prop ";
   }
   $tarifa = $tarifa + $valor_prop ;
   return array($tarifa, $cidade, $dst_fmtd) ;
 }  
?>  