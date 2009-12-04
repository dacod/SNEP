<?php
/**
 * Cria as novas tabelas no banco de dados.
 */
class NovasTabelas {
    public function run() {
        $config = Zend_Registry::get('config');
        echo "Criando novas tabelas...";
        $username = $config->ambiente->db->username;
        $password = $config->ambiente->db->password;
        $dbname   = $config->ambiente->db->dbname;
        if(!exec("mysql -u$username -p$password $dbname < ./sql/new_tables.sql")) {
            echo "ok\n";
        }
        else {
            die();
        }
    }
}
?>
