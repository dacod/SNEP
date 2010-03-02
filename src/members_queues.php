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
 ver_permissao(18) ;
 global $name, $acao ;
 $name = isset($_POST['name']) ? $_POST['name'] : $_GET['name'];
 if ($acao == 'gravar') {
    grava_members() ;
    echo "<meta http-equiv='refresh' content='0;url=../src/rel_queues.php'>\n" ;
 }
 $titulo = $LANG['menu_register']." -> ".$LANG['menu_queues']." -> ".$LANG['queue_members']." : ".$name;
   // Lista de Todos os ramais disponiveis
 $sql = "SELECT name, canal, callerid, `group` FROM peers  WHERE canal != '' AND peer_type = 'R' ORDER BY `group`, name" ;
 $ramais_disp = array() ;
 try {
    foreach ($db->query($sql) as $row) {
       $cd = explode(";",$row['canal']);
       foreach ($cd as $canal) {
          if (strlen($canal) > 0)
             $ramais_disp[$canal] = $row['callerid']." ($canal)({$row['group']})";
          }
       }
 } catch (Exception $e) {
    display_error($LANG['error'].$e->getMessage(),true) ;
 }
 // Lista de Todos os ramais que ja participam da fila e ajusta o canal
 // com o callerid atual da tabela ramais
 $sql = "SELECT membername,interface FROM queue_members WHERE queue_name = '".$name."'" ;
 $sql.= " ORDER BY membername" ;
 $ramais_used = array() ;
 try {
    $row = $db->query($sql)->fetchAll() ;
    foreach ($row as $val) {
       if (array_key_exists($val['interface'],$ramais_disp))
          $ramais_used[$val['interface']] = $ramais_disp[$val['membername']] ;
       else
          $ramais_used[$val['interface']] = $val['membername'] ;
    }
    // Retira da Lista de disponiveis os que ja estao sendo usados
    foreach ($ramais_disp as $key=>$val) {
       if (array_key_exists($key,$ramais_used)) {
          unset($ramais_disp[$key]) ;
       }
    }
 } catch (Exception $e) {
   display_error($LANG['error'].$e->getMessage(),true) ;
 }
 $smarty->assign ('OPCOES_LIVRES',$ramais_disp);
 $smarty->assign ('OPCOES_USADOS',$ramais_used);
 $smarty->assign ('ACAO','gravar') ;
 $smarty->assign ('name',$name) ;
 display_template("members_queues.tpl",$smarty,$titulo);

/*-----------------------------------------------------------------------------
 * Funcao grava_members - Grava dados nas teb&#231;las do BD
 * ----------------------------------------------------------------------------*/
 function grava_members() {
    global $db, $lista2, $name ;


    $db->beginTransaction() ;
    $sql = "DELETE FROM queue_members WHERE queue_name = '$name'";
    $stmt = $db->prepare($sql) ;
    $stmt->execute() ;

    foreach ($lista2 as $val) {
        $sql = "INSERT INTO queue_members (interface,membername,queue_name) VALUES ('$val', '$val', '$name') ";
        $db->exec($sql); 
    }

    try{
        $db->commit();
        echo "<meta http-equiv='refresh' content='0;url=../src/rel_queues.php'>\n" ;

    } catch(Exception $e) {
        $db->rollBack();
        display_error($LANG['error'].$e->getMessage(),true) ;
    }
     
 }
 
