<?php
require_once("../includes/verifica.php");
require_once("../configs/config.php");

$remove = false;
$verifica = "DESC permissoes_vinculos";

$db = Zend_Registry::get('db');

try {
    $stmt = $db->query($verifica);
    $result = $stmt->fetchAll();    

}catch(Exception $e) {
    migracao();
    
}


function migracao() {

    $db = Zend_Registry::get('db');
    
    $select = $db->select()
    ->from('peers', array('id', 'name', 'vinculo'))
    ->where("peers.peer_type = 'R' ")
    ->where("peers.name != 'admin'" );

    $stmt = $db->query($select);
    $ramais = $stmt->fetchAll();

    $db->beginTransaction() ;
    $query_create = "
        CREATE TABLE `permissoes_vinculos` (
            `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `id_peer` VARCHAR( 100 ) NOT NULL ,
            `tipo` CHAR( 1 ) NOT NULL,
            `id_vinculado` VARCHAR( 100 ) NOT NULL
            ) ENGINE = InnoDB;
    ";

    try {
        $db->query($query_create);
        $db->commit();

        foreach($ramais as $id => $ramal) {
            if( strlen( $ramal['vinculo'] ) > 1 ) {
                $vinculos = explode( "," , $ramal['vinculo'] );
                foreach($vinculos as $num => $vinculo) {
                    $insert_data = array("id_peer" => $ramal['name'],
                                         "tipo"   => "R",
                                         "id_vinculado"   => trim($vinculo)
                    );
                    $db->insert('permissoes_vinculos', $insert_data);
                }
            }
        }
        $remove = true;
    }catch(Exception $e) {
        $db->rollBack();
    }

}

if($remove) {
    //exec("echo '' > /var/www/snep/configs/atualiza.php");
}


?>