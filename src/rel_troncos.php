<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_troncos.php - Lista Troncos Cadastrados
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 ver_permissao(30) ;
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_troncos'] ;
 // SQL padrao
 // ----------
 $sql = "SELECT * FROM trunks" ;
 // Opcoes de Filtros
 // -----------------
 $opcoes = array( "name" => $LANG['tronco'],
                  "callerid" => $LANG['desc']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) {
    $sql .= " WHERE ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 }
 $sql .= " ORDER BY name" ;
 // Executa acesso ao banco de Dados
 // --------------------------------
 try { 
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }          
 foreach ($row as $k => $v) {
   $row[$k]['tecnologias'] = substr($v['channel'],0,strrpos($v['channel'],"/"));
   $trunks_red[$v['id']] = $v['name']." - ".$v['callerid'] ;
 }
 // Monta nome do tronco redundante
 // -------------------------------
 foreach ($row as $k => $v) {
    if ($v['trunk_redund'] != 0) {
       $tr = $v['trunk_redund'] ;
       $row[$k]['trunkredund'] = $trunks_red[$tr] ;
    }
 }
$tot_pages = ceil(count($row)/$SETUP['ambiente']['linelimit']) ;
 for ($i = 1 ; $i <= $tot_pages ; $i ++ )
     $paginas[$i] = $i;

 $_SESSION['pagina'] = $_GET['pag'];
 $count = count($row);
 $x = count($row)/10;
 $numeros = array();
 $s = array();

 for ($i=0; $i <= $count; $i++) {
     if($a < $x) {
         $numeros[] = $i;
     }
 }

 // Define variaveis do template
 $smarty->assign ('OPCAO_YN',$tipos_yn) ;
 $smarty->assign ('TOT',$tot_pages);
 $smarty->assign ('PAGINAS',$paginas) ;
 $smarty->assign ('INI',1);
 $smarty->assign ('COUNT', $count);
 $smarty->assign ('PROTOTYPE', True);
 $smarty->assign ('DADOS',$row);
 $smarty->assign ('OPCAO_TTRONCO',array("I"=>"IP","T"=>"TDM")) ;
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../src/troncos.php", "display"  => $LANG['include']." ".$LANG['menu_troncos'], "peer_type"=>"T"));
 
 // Exibe template
 // --------------
 display_template("rel_troncos.tpl",$smarty,$titulo);
 ?>