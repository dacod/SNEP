<?php
/*-----------------------------------------------------------------------------
* Programa: rel_cont_names.php - Lista de Contatos
* Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
* Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
* Autor: Flavio Henrique Somensi <flavio@opens.com.br>
*-----------------------------------------------------------------------------*/
require_once("../includes/verifica.php");   
require_once("../configs/config.php"); 

ver_permissao(59) ;

$titulo = $LANG['menu_contato']." -> ".$LANG['menu_contacts'] ;

// SQL padrao
$sql = "SELECT * FROM contacts_names " ;

// Opcoes de Filtros de Busca  
$opcoes = array( "name" => $LANG['name'], "id" => $LANG['id'],
              "city" => $LANG['city'], "state" => $LANG['state']) ;
// Se aplicar Filtro 
if (array_key_exists ('filtrar', $_POST)) {
   $sql .= " WHERE ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'" ;
   $sql .= " ORDER BY ".$_POST['field_filter'] ;
} 
else {
   $sql .= " ORDER BY CAST( id as decimal) " ;
}
 
// Executa acesso ao banco de Dados
try 
{
 $row = $db->query($sql)->fetchAll();
 $totais = count($row);
} 
catch (Exception $e) 
{
 display_error($LANG['error'].$e->getMessage(),true) ;
}

 // Paginação
$tot_pages = ceil(count($row)/$SETUP['ambiente']['linelimit']) ;
  for ($i = 1 ; $i <= $tot_pages ; $i ++ )
$paginas[$i] = $i;

 // Cria Objeto para formtacao de dados
$my_object = new Formata ;
$smarty->register_object("formata",$my_object) ;
     
$tmp =  ver_permissao(57,"", True) ;
// Define variaveis do template
$smarty->assign ('TOT',$tot_pages);
$smarty->assign ('PAGINAS',$paginas) ;
$smarty->assign ('DADOS',$row);
$smarty->assign ('INI',1) ;
$smarty->assign ('TOTAIS',$totais) ;
// Variaveis Relativas a Barra de Filtro/Botao Incluir
$smarty->assign ('view_filter',True) ;
$smarty->assign ('OPCOES', $opcoes) ;
$smarty->assign ('array_include_buttom',array("url" => "../src/cont_names.php", "display"  => $LANG['include']." ".$LANG['menu_contacts']));
$smarty->assign ('view_include_buttom', $tmp );
$smarty->assign ('VIEW_AIE', $tmp) ;
//* Exibe template */
display_template("rel_cont_names.tpl",$smarty,$titulo);
?>
