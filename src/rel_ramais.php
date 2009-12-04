<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_ramais.php - Lista Ramais/Troncos Cadastradas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
 require_once("../includes/verifica.php");   
 require_once("../configs/config.php");
 ver_permissao(15) ;
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_ramais'] ;
 // SQL padrao
 $sql = "SELECT * FROM peers WHERE peer_type = 'R'" ;
 // Opcoes de Filtros
 $opcoes = array( "name" => $LANG['ramal'],
                  "callerid" => $LANG['name'], 
                  "context" => $LANG['context']) ;
 // Se aplicar Filtro ....
 if (array_key_exists ('filtrar', $_POST)) {
    $sql .= " AND ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
 }
 $sql .= " ORDER BY name" ;
 // Executa acesso ao banco de Dados
 try {
    $row = $db->query($sql)->fetchAll();
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }
 $tot_pages = ceil(count($row)/$SETUP['ambiente']['linelimit']) ;
 for ($i = 1 ; $i <= $tot_pages ; $i ++ )
     $paginas[$i] = $i;

foreach ($row as $key => $ramal) {
    switch($ramal['group']) {
        case 'admin':
            $ramal['group'] = 'Administradores';
            break;
        case 'users':
            $ramal['group'] = 'Usu&aacute;rios';
            break;
        default:
            $ramal['group'] = $ramal['group'];
    }
    $row[$key] = $ramal;
}

 for ($i=0; $i <= $count; $i++) {
     if($a < $x) {
         $numeros[] = $i;
     }
 }
 $_SESSION['pagina'] = $_GET['pag'];
 
 // Define variaveis do template          
 $smarty->assign ('DADOS',$row);
 $smarty->assign ('OPCAO_YN',$tipos_yn) ;
 $smarty->assign ('TOT',$tot_pages);
 $smarty->assign ('PAGINAS',$paginas) ;
 $smarty->assign ('INI',1);
 $smarty->assign ('COUNT', $count);
 
 // Variaveis Relativas a Barra de Filtro/Botao Incluir
 $smarty->assign ('view_filter',True) ;
 $smarty->assign ('view_include_buttom',True) ;
 $smarty->assign ('view_include_buttom2',True) ;
 $smarty->assign ('PROTOTYPE', True) ;
 $smarty->assign ('OPCOES', $opcoes) ;
 $smarty->assign ('array_include_buttom',array("url" => "../src/ramais.php", "display"  => $LANG['include']." ".$LANG['ramal'], "peer_type"=>"R"));
 $smarty->assign ('array_include_buttom2',array("url" => "../src/ramais_varios.php", "display"  => $LANG['include']." ".$LANG['menu_ramais'], "peer_type"=>"R"));
  
 // Exibe template
 display_template("rel_ramais.tpl",$smarty,$titulo);
 ?>