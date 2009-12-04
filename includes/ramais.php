<?php
/* includes/troncos.php - Função responsável por listar os troncos cadastrados e responde-los via
 * JSON para funções em Ajax.
 *
 * @author Rafael Bozzetti <rafael@opens.com.br>
 */

require_once("../configs/config.php");
require_once("conecta.php");

$ramal = $_POST['ramal'];


try {
       $sql = "SELECT name from peers where name = '$ramal' " ;
       $row = $db->query($sql)->fetchAll();

} catch (Exception $e) {
       display_error($LANG['error'].$e->getMessage(),true) ;
}
if(isset($row[0])) {
    echo 1;
} else {
    echo 0;
} 
?>
