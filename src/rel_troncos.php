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
ver_permissao(30) ;
$titulo = $LANG['menu_register']." » ".$LANG['menu_troncos'] ;
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

$_SESSION['pagina'] = isset($_GET['pag']) ? $_GET['pag'] : NULL;
$count = count($row);

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
