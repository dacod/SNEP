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

 // Pagina��o
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
