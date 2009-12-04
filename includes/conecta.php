<?php
/* ----------------------------------------------------------------------------
 * Programa: conecta.php - Efetua conexao com Banco de Dados
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autores: Flavio Henrique Somensi <flavio@opens.com.br>
 *        Henrique Grolli Bassotto <henrique@opens.com.br>
 * ----------------------------------------------------------------------------*/

$dbname    = 'snep25';
$user      = 'snep';
$passwd    = 'sneppass';
$type_bd   = 'mysql';
$host      = 'localhost';
$dsn = "$type_bd:host=$host;dbname=$dbname" ;

try {
    $db = new PDO($dsn, $user, $passwd, array(PDO::ATTR_PERSISTENT => True)) ;
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
} catch (Exception $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    exit(1);
}
?>
