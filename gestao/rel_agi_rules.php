<?php
/*-----------------------------------------------------------------------------
 * Programa: rel_agi_rules.php - Lista Regras de Diaplan cadastradas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 *-----------------------------------------------------------------------------*/
require_once("../includes/verifica.php");  
require_once("../configs/config.php"); 
ver_permissao(48);

$titulo = $LANG['menu_rules']." -> ".$LANG['menu_exit'];

// Opcoes de Filtros
$opcoes = array( "src" => $LANG['origin'],
              "dst" => $LANG['destination']);

// Se aplicar Filtro ....
if (array_key_exists ('filtrar', $_POST))
    $where = " WHERE ".$_POST['field_filter']." like '%".$_POST['text_filter']."%'";
else
    $where = null;

// Executa acesso ao banco de Dados
$regras = PBX_Rules::getAll($where);

$dados = array();
foreach ($regras as $regra) {

    $list_src = '';
    foreach($regra->getSrcList() as $src) {
        switch($src['type']) {
            case "X" :
                $list_src .= "{$LANG['any']}<br />";
                break;
            case "R" :
                $list_src .= $src['value'] . "<br />";
                break;
            case "RX" :
                $list_src .= $src['value'] . "<br />";
                break;
            case "T" :
                $trunk = PBX_Trunks::get($src['value']);
                $list_src .= "{$LANG['trunk']} {$trunk->getName()}<br />";
                break;
            case "G" :
                switch ($src['value']) {
                    case 'all':
                        $groupname = $LANG['all'];
                        break;
                    case 'users':
                        $groupname = $LANG['user'];
                        break;
                    case 'admin':
                        $groupname = $LANG['admin'];
                        break;
                    default:
                        $groupname = $src['value'];
                        break;
                }
                $list_src .= "{$LANG['group']} {$groupname}<br />";
                break;
        }
    }

    $list_dst = '';
    foreach($regra->getDstList() as $dst) {
        switch($dst['type']) {
            case "X" :
                $list_dst .= "{$LANG['any']}<br />";
                break;
            case "R" :
                $list_dst .= $dst['value'] . "<br />";
                break;
            case "RX" :
                $list_dst .= $dst['value'] . "<br />";
                break;
            case "S" :
                $list_dst .= "{$LANG['no_destiny']}<br />";
                break;
            case "G" :
                switch ($dst['value']) {
                    case 'all':
                        $groupname = $LANG['all'];
                        break;
                    case 'users':
                        $groupname = $LANG['user'];
                        break;
                    case 'admin':
                        $groupname = $LANG['admin'];
                        break;
                    default:
                        $groupname = $dst['value'];
                        break;
                }
                $list_dst .= "{$LANG['group']} {$groupname}<br />";
                break;
        }
    }

    $dados[] = array(
        "codigo"    => $regra->getId(),
        "ativa"     => $regra->isActive(),
        "src"       => $list_src,
        "dst"       => $list_dst,
        "descricao" => $regra->getDesc(),
        "ordem"     => $regra->getPriority(),
    );
}


// Define variaveis do template
$smarty->assign ('DADOS',$dados);
$smarty->assign('OPCOES_TEMPO',array("A"=>$LANG['year'],"M"=>$LANG['month'],"D"=>$LANG['day']));
// Variaveis Relativas a Barra de Filtro/Botao Incluir
$smarty->assign ('view_filter',True);
$smarty->assign ('view_include_buttom',True);
$smarty->assign ('debugger_btn',True);
$smarty->assign ('OPCOES', $opcoes);
$smarty->assign ('array_include_buttom',array("url" => "../gestao/agi_rules.php", "display"  => $LANG['include']." ".$LANG['menu_rules']));
// Exibe template
display_template("rel_agi_rules.tpl",$smarty,$titulo);
?>