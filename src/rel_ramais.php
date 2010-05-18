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

$count = isset($count) ? $count : 0;
$a = isset($a) ? $a : 0;
$x = isset($x) ? $x : 0;

for ($i=0; $i <= $count; $i++) {
    if($a < $x) {
        $numeros[] = $i;
    }
}
$_SESSION['pagina'] = isset($_GET['pag']) ? $_GET['pag'] : null;

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