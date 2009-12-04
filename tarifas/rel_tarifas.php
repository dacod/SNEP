<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_tarifas.php - Lista tarifas/Troncos Cadastradas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 ver_permissao(45) ;
 $titulo = $LANG['menu_tarifas']." -> ".$LANG['menu_tarifas'] ;
 // Monta lista de Operadoras
 if (!isset($operadora) || count($operadoras) == 0) {
    try {
       $sql_oper = "SELECT * FROM operadoras ORDER by nome" ;
       $row_oper = $db->query($sql_oper)->fetchAll();
    } catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
    }
    unset($val);
    $operadoras = array(""=>$LANG['undef']);
    foreach ($row_oper as $val)
       $operadoras[$val['codigo']] = $val['nome'] ;
    asort($operadoras) ;
 }

 // SQL padrao
 $sql = "SELECT tarifas.*, operadoras.nome FROM tarifas, operadoras WHERE tarifas.operadora=operadoras.codigo " ;
 // Opcoes de Filtros
 $opcoes = array( "nome" => $LANG['operadora'],
                  "cidade" => $LANG['city'],
                  "ddd" => $LANG['ddd']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) {
    $sql .= " AND ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 }
 $sql .= "  ORDER BY operadora,cidade,prefixo" ;
 // Executa acesso ao banco de Dados
 
 try {
     $row = $db->query($sql)->fetchAll();
     foreach ($row as $key=>$value) {
        $codigo = $value['codigo'] ;
        $sql_vlr = "SELECT * FROM tarifas_valores WHERE codigo = $codigo " ;
        $sql_vlr.= " ORDER BY data DESC LIMIT 1" ;
        $row_vlr = $db->query($sql_vlr)->fetch();
        if ($row_vlr != "" )
           $row[$key] += $row_vlr ;          
     }
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ; 
    exit ;
 }

 $tot_pages = ceil(count($row)/$SETUP['ambiente']['linelimit']) ;
 for ($i = 1 ; $i <= $tot_pages ; $i ++ )
     $paginas[$i] = $i;
 
 // Define variaveis do template
 $smarty->assign('OPERADORAS',$operadoras);
 $smarty->assign ('DADOS',$row);
 $smarty->assign ('TOT',$tot_pages);
 $smarty->assign ('PAGINAS',$paginas) ;
 $smarty->assign ('INI',1);
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../tarifas/tarifas.php", "display"  => $LANG['include']." ".$LANG['menu_tarifas']));
  
 // Exibe template
 display_template("rel_tarifas.tpl",$smarty,$titulo);
 ?>
