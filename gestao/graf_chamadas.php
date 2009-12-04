<?php
/* ----------------------------------------------------------------------------
 * Programa: graf_chamadas.php - Grafico das Chamadas
 * Copyright (c) 2007 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- */
 require_once("../includes/verifica.php");
 require_once("../configs/config.php");

 ver_permissao(41);
 
 $totais = $_SESSION['totaisgraf'] ;
 $param  = $_SESSION['parametros'] ;
 $tpgraf = $param['tpgraf'] ;
 $status = $param['status'] ;
 $titulo = $param['titulo'] ;

 foreach ($totais as $td => $val) {
    foreach ($val as $i => $tot) {
       if ($i == 0)
          ${"avg_$td"} = $tot ;
       else
          ${"grf_$td"}[$i-1] = $tot ;
    }
 }

 
 if ($tpgraf=="B") {
   grafico_barras();
 } elseif  ( $tpgraf == "L" ) {
   grafico_linhas();
 }
 //unset($_SESSION['totais'],$_SESSION['parametros']);

/*-----------------------------------------------------------------------------
 * Funcao grafico_barras - Mostra grafico em barras verticais c/ efeito 3D
 *-----------------------------------------------------------------------------*/   
function grafico_barras() {
  include ("../includes/jpgraph/src/jpgraph.php");
  include ("../includes/jpgraph/src/jpgraph_bar.php"); 
  global $grf_dias, $grf_ans, $grf_noa, $grf_bus, $grf_fai, $avg_ans, $avg_noa, $avg_bus, $avg_fai, $status, $titulo, $LANG ;
  // Configuracao das dimensoes do grafico.
  $graph = new Graph(980,380,"auto");
  $graph->img->SetMargin(50,20,40,120);
  $graph->SetScale("textlin");
  $graph->SetMarginColor("white");
  $graph->SetFrame(true,'#a4a7ab',1);
  
  // Adjust the position of the legend box
  //$graph->legend->Pos(0.01,0.01);  
  
  $graph->legend->Pos(0.07,0.96,"left","bottom");
  // Adjust the color for theshadow of the legend
  $graph->legend->SetShadow('darkgray@0.5');
  $graph->legend->SetFillColor('gray@0.3');
  $graph->legend->SetColumns(4);
  // Configuracao do titulo do grafico.
  $graph->title->Set($titulo);
  $graph->title->SetFont(FF_VERA,FS_NORMAL,12);
  $graph->title->SetColor("#808080");
  
  // Configuracao de Font.
  $graph->xaxis->SetFont(FF_VERA,FS_NORMAL,10);
  $graph->yaxis->SetFont(FF_VERA,FS_NORMAL,10);
  $graph->yscale->ticks->SupressZeroLabel(false);
  
  // Dados do --> Eixo X
  $graph->xaxis->SetTickLabels($grf_dias);
  $graph->xaxis->SetLabelAngle(50);
  
  $graph->xgrid->Show();
  $graph->xgrid->SetColor('gray@0.5');
  $graph->ygrid->SetColor('gray@0.5');
  
  $b2 = new BarPlot($grf_ans); 
  $b2->value->Show();
  $b2->value->SetAngle(45);
  $b2->value->SetFormat('%d');
  $b2->value->SetFont(FF_VERA,FS_NORMAL,8);
  $b2->SetFillColor("brown@0.4");
  $b2->SetLegend($LANG['answer']."(".$LANG['media']."=".$avg_ans.")") ;
  $b2->SetShadow('black@0.4');
    
  $b3 = new BarPlot($grf_noa);
  $b3->value->Show();
  $b3->value->SetAngle(45);
  $b3->value->SetFormat('%d');
  $b3->value->SetFont(FF_VERA,FS_NORMAL,8);
  $b3->SetFillColor("darkgreen@0.4");
  $b3->SetLegend($LANG['msg_notanswereds']."(".$LANG['media']."=".$avg_noa.")") ;
  $b3->SetShadow('black@0.4');
    
  $b4 = new BarPlot($grf_bus);
  $b4->value->Show();
  $b4->value->SetAngle(45);
  $b4->value->SetFormat('%d');
  $b4->value->SetFont(FF_VERA,FS_NORMAL,8);
  $b4->SetFillColor("lightblue4@0.4");
  $b4->SetLegend($LANG['busys']."(".$LANG['media']."=".$avg_bus.")") ;
  $b4->SetShadow('black@0.4');
    
  $b5 = new BarPlot($grf_fai);
  $b5->value->Show();
  $b5->value->SetAngle(45);
  $b5->value->SetFormat('%d');
  $b5->value->SetFont(FF_VERA,FS_NORMAL,8);
  $b5->SetFillColor("yellow2@0.4");
  $b5->SetLegend($LANG['fail']."(".$LANG['media']."=".$avg_fai.")") ; 
  $b5->SetShadow('black@0.4');
  
  // Define array das colunas a serem impressas conforme sele��o do usuario  
  $lli = array() ; 
  if (strstr($status, "ALL"))
     $lli = array($b2,$b3,$b4,$b5) ;
  else {
    if (strstr($status, "ANS"))   
       $lli[] = $b2 ;
    if (strstr($status, "NOA"))
       $lli[] = $b3 ;
    if (strstr($status, "BUS"))     
       $lli[] = $b4 ;
    if (strstr($status, "FAI"))     
       $lli[] = $b5 ;
  }
           
  $grupoBarras = new GroupBarPlot($lli);
  $grupoBarras->SetWidth(0.9);
  $graph->Add($grupoBarras);
  // Cria o grafico
  $graph->Stroke();
  
}     

/*------------------------------------------------------------------------------
 Funcao grafico_linhas - Mostra grafico em barras verticais c/ gradiente
 ------------------------------------------------------------------------------*/
function grafico_linhas() {
  include ("../includes/jpgraph/src/jpgraph.php");
  include ("../includes/jpgraph/src/jpgraph_line.php"); 
  global $grf_dias, $grf_ans, $grf_noa, $grf_bus, $grf_fai, $avg_ans, $avg_noa, $avg_bus, $avg_fai, $status, $titulo, $LANG ;
  // Setup the graph
  $graph = new Graph(980,380);
  $graph->SetScale("textlin");
  $graph->SetMargin(50,20,40,120);
  $graph->SetMarginColor("white");
  $graph->SetFrame(true,'#a4a7ab',1);
  //$graph->SetShadow();

  // We must have the frame enabled to get the gradient
  // However, we don't want the frame line so we set it to
  // white color which makes it invisible.
  //$graph->SetFrame(true,'white');

  $graph->legend->Pos(0.01,0.96,"left","bottom");
  // Adjust the color for theshadow of the legend
  $graph->legend->SetShadow('darkgray@0.5');
  $graph->legend->SetFillColor('gray@0.3');
  $graph->legend->SetColumns(4);
  

  // Configuracao do titulo do Grafico.
  $graph->title->Set($titulo);
  $graph->title->SetFont(FF_VERA,FS_NORMAL,12);
  $graph->title->SetColor("#808080");
  
    // Fonts
  $graph->xaxis->SetFont(FF_VERA,FS_NORMAL,10);
  $graph->yaxis->SetFont(FF_VERA,FS_NORMAL,10);
  
  // Setup x,Y grid
  $graph->xgrid->Show();
  $graph->xgrid->SetColor('gray@0.5');
  $graph->ygrid->SetColor('gray@0.5');
  $graph->ygrid->SetFill(true,'#FFFFFF','#BFBFBF@0.5');
  $graph->yscale->ticks->SupressZeroLabel(true);
    
  $graph->xaxis->SetTickLabels($grf_dias);
  $graph->xaxis->SetLabelAngle(45);

  // Setup color for axis and labels on axis
  $graph->xaxis->SetColor('black','black');
  $graph->yaxis->SetColor('black','black');

  // Ticks on the outsid
  $graph->xaxis->SetTickSide(SIDE_DOWN);
  $graph->yaxis->SetTickSide(SIDE_LEFT);

   
  // Create the  lines
  if (strstr($status, "ALL")) {
    $status="ANS,NOA,BUS,FAI" ;
  } 
  if (strstr($status, "ANS")) {
    $l2 = new LinePlot($grf_ans); 
    $l2->SetColor("brown@0.4");
    $l2->SetWeight(3);
    $l2->SetCenter();
    $l2->value->Show();
    $l2->value->SetFormat('%d');
    $l2->SetLegend($LANG['answer']."(".$LANG['media']."=".$avg_ans.")") ; 
    $graph->Add($l2);
  }
  if (strstr($status, "NOA")) {   
    $l3 = new LinePlot($grf_noa); 
    $l3->SetColor("darkgreen@0.4");
    $l3->SetWeight(3);
    $l3->SetCenter();
    //$l3->value->Show();
    $l3->value->SetFormat('%d');
    $l3->SetLegend($LANG['msg_notanswered']."(".$LANG['media']."=".$avg_noa.")") ;
    $graph->Add($l3);
  }
  if (strstr($status, "BUS")){
    $l4 = new LinePlot($grf_bus); 
    $l4->SetColor("lightblue4@0.4");
    $l4->SetWeight(3);
    $l4->SetCenter();
    //$l4->value->Show();
    $l4->value->SetFormat('%d');
    $l4->SetLegend($LANG['busys']."(".$LANG['media']."=".$avg_bus.")") ;
    $graph->Add($l4);
  }
  if (strstr($status, "FAI")) {
    $l5 = new LinePlot($grf_fai); 
    $l5->SetColor("yellow2@0.4");
    $l5->SetWeight(3);
    $l5->SetCenter();
    //$l5->value->Show();
    $l5->value->SetFormat('%d');
    $l5->SetLegend($LANG['fail']."(".$LANG['media']."=".$avg_fai.")") ;
    $graph->Add($l5);
  }

  // Output line
  $graph->Stroke();
}
     
?>
