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

ver_permissao(99);

if (array_key_exists ('permissao', $_POST)) {
    gravar() ;
}

$name = ( isset( $_POST['name'] ) ? $_POST['name'] : null ) ;
$dt_id = ( isset( $_POST['dt_id'] ) ? $_POST['dt_id'] : null ) ;
$nome = ( isset(  $_POST['nome']  ) ? $_POST['nome'] : null) ;

$titulo = $LANG['menu_register']." » ".$LANG['menu_ramais']." » ".$LANG['permitions']." ".$LANG['of']." ".$LANG['user'] ;

/* Vinculados ao ramal */
$vinculados = Snep_Vinculos::getVinculados($name);
$arrVinculados = array();
if($vinculados) {
    foreach($vinculados as $vinculado) {
        $arrVinculados["r-" . $vinculado['id_vinculado']] = "Ramal: ". $vinculado['id_vinculado'] ;
    }
}

/* Agentes Vinculados ao ramal */
$agentes_vinculados = Snep_Vinculos::getVinculadosAgente($name);
if($vinculados) {
    foreach($agentes_vinculados as $id => $agentes_vinculado) {
        /* Inclui agente, a lista de ramais vinculados */
        $arrVinculados["a-" . $agentes_vinculado['id_vinculado']] = "Agente: {$agentes_vinculado['id_vinculado']} " ;
    }
}

/* Desvinculados ao ramal */
$desvinculados = Snep_Vinculos::getDesvinculados($name);
$arrDesvinculados = array();
if($desvinculados) {
    foreach($desvinculados as $desvinculado) {
        $arrDesvinculados["r-" . $desvinculado['name']] = "Ramal: ". $desvinculado['name'] ;
    }
}

/* Agentes Desvinculados ao ramal */
$agentes_desvinculados = Snep_Vinculos::getDesvinculadosAgente($name);
if($agentes_desvinculados) {
    foreach($agentes_desvinculados as $ida => $agentes_desvinculado) {
        //$arrAgentesDesvinculados[$id] = "Agente: ". $agentes_desvinculado ;
        $arrDesvinculados["a-" . $ida] = "Agente: $agentes_desvinculado";

    }
}

// Lista das Rotinas disponiveis na tabela ROTINAS
$sql = " SELECT r.cod_rotina as cod_rotina,r.desc_rotina as desc_rotina," ;
$sql.= " permissoes.permissao as permissao";
$sql.= " FROM rotinas as r " ;
$sql.= " LEFT JOIN permissoes ON permissoes.cod_rotina = r.cod_rotina ";
$sql.= " AND permissoes.cod_usuario = '$dt_id' " ;
$sql.= " order by desc_rotina" ;

try {
    $row = $db->query($sql)->fetchAll();

} catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true);
    
}

// Define variaveis do template          
$smarty->assign ('dt_permissoes', $row);
$smarty->assign ('dt_usuario', $nome) ;
$smarty->assign ('dt_id', $dt_id);
$smarty->assign ('dt_name', $name);
$smarty->assign ('LISTA_DESVINCULADOS', $arrDesvinculados);
$smarty->assign ('LISTA_VINCULADOS', $arrVinculados);
$smarty->assign ('TIPOS_PERMS', array('S' => $LANG['yes'], 'N' => $LANG['no'], '' =>$LANG['undef']));
$smarty->assign ('PROTOTYPE', true);
display_template("permissoes.tpl", $smarty, $titulo) ;

/*------------------------------------------------------------------------------
  Funcao GRAVA_ALTERAR - Grava registro Alterado
------------------------------------------------------------------------------*/
function gravar() {

    global $db;

    $id = $_POST['id'];
    $name = $_POST['name'];     

    /* Remove qualquer referencia de vinculo a este ramal */
    Snep_Vinculos::resetVinculos($name);

    /* Cadastro de ramais vinculados */
    $vinculos = ( isset( $_POST['vinculo2'] ) ? $_POST['vinculo2'] : null );
    if ($vinculos) {
        foreach($vinculos as $vinculo) {
            $tipo = substr($vinculo, 0, 1);
            $numero = substr($vinculo, strpos($vinculo, "-")+1);

            if($tipo == "r") {
                Snep_Vinculos::setVinculos($name, 'R', $numero);
            }else {
                Snep_Vinculos::setVinculos($name, 'A', $numero);
            }            
        }
    }
    
    try {
        $db->beginTransaction() ;
        $sql = "SELECT cod_rotina FROM rotinas order by cod_rotina ";
        foreach ($db->query($sql) as $row) {
            // Verifica se usuario ja tem permissao registrada para a rotina
            $sql_upd = "REPLACE INTO permissoes (cod_rotina,cod_usuario,permissao)" ;
            $sql_upd.= " VALUES ('" . $row['cod_rotina'] . "',$id,'" . $_POST[$row['cod_rotina']]."')"  ;
            $db->exec($sql_upd) ;
        } // Fim do Foreach  da tabela de rotinas
        $db->commit();
    } catch (Exception $e) {
        display_error($LANG['error'].$e->getMessage(),true) ;
    }
    echo "<meta http-equiv='refresh' content='0;url=../src/extensions.php'>\n" ;
}
