<?php
/**
 * Faz a migração de grupos de ramais do arquivo de configuração do snep 2.2
 * para o banco de dados do snep 2.5
 */
class MigrarGrupos {
    public function run() {
        $config = Zend_Registry::get('config');
        $db = Zend_Registry::get('db');

        $groups = explode(';', $config->usuarios->grupos);
        echo "Criando grupos:\n";
        foreach ($groups as $group) {
            if($group != 'default') {
                echo "    $group\n";
                $db->exec("INSERT INTO `groups` VALUES('$group', 'users')");
            }
        }
        echo "OK\n";
        echo "Atualizando tabela de ramais...";
        $db->exec("UPDATE peers SET `group`='users' WHERE `group`='' OR `group`='default' OR `group`=' ' OR `group` IS NULL");
        echo "OK\n";
    }
}