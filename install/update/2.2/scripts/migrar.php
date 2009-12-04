#!/usr/bin/php
<?php
// Controle da exibição de erros
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

// silenciando strict até arrumar zend_locale
date_default_timezone_set("America/Sao_Paulo");

$config_file = "/var/www/snep/includes/setup.conf";

//encontrado diretórios do sistema
if(!file_exists($config_file)) {
    die("FATAL ERROR: arquivo $config_file nao encontrado");
}
$config = parse_ini_file($config_file,true);

// Adicionando caminho de libs ao include path para autoloader trabalhar:
set_include_path($config['system']['path.base'] . "/lib" . PATH_SEPARATOR  . get_include_path());
$logdir = $config['system']['path.log'];
unset($config);
// iniciando auto loader
require_once "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();

// Registrando namespaces para as outras bibliotecas
$autoloader->registerNamespace('Snep_');
$autoloader->registerNamespace('PhpAgi_');

// Carregando arquivo de configuração do snep e alocando as informações
// no registro do Zend.
$config = new Zend_Config_Ini($config_file);
$debug = (boolean)$config->system->debug;
Zend_Registry::set('configFile', $config_file);
Zend_Registry::set('config', $config);

// Versão do SNEP
Zend_Registry::set('snep_version', file_get_contents($config->system->path->base . "/configs/snep_version"));

// Iniciando banco de dados
$db = Zend_Db::factory('Pdo_Mysql', $config->ambiente->db->toArray());
Zend_Db_Table::setDefaultAdapter($db);
Zend_Registry::set('db', $db);

foreach (array("NovasTabelas", "MigrarGrupos") as $script) {
    require_once($script . ".php");
    $script = new $script;

    $script->run();
}

echo "Atualizando tabelas...";
$sql = file_get_contents('./sql/snep25.sql');
$db->exec($sql);
echo "ok\n\n";

echo "FIM\n";