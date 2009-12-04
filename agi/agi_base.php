<?php
// Tratamento de sinais vindos do asterisk
declare(ticks = 1);
if (function_exists('pcntl_signal')) {
        pcntl_signal(SIGHUP,  SIG_IGN);
}

// Controle da exibição de erros
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

// silenciando strict até arrumar zend_locale
date_default_timezone_set("America/Sao_Paulo");

$config_file = "/var/www/snep/includes/setup.conf";

//encontrado diretórios do sistema
if(!file_exists($config_file)) {
    echo "VERBOSE \"FATAL ERROR: arquivo $config_file nao encontrado\" 1\n";
    exit(1);
}
$config = parse_ini_file($config_file,true);

// Adicionando caminho de libs ao include path para autoloader trabalhar:
set_include_path($config['system']['path.base'] . "/lib" . PATH_SEPARATOR  . get_include_path());
$logdir = $config['system']['path.base'] . "/log";
unset($config);
// iniciando auto loader
require_once "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();

// Registrando namespaces para as outras bibliotecas
$autoloader->registerNamespace('Snep_');
$autoloader->registerNamespace('Asterisk_');
$autoloader->registerNamespace('PBX_');

// Carregando arquivo de configuração do snep e alocando as informações
// no registro do Zend.
$config = new Zend_Config_Ini($config_file);
$debug = (boolean)$config->system->debug;
Zend_Registry::set('config', $config);

// Iniciando sistema de logs
$log = new Zend_Log();
Zend_Registry::set('log', $log);

// Definindo aonde serão escritos os logs
$writer = new Zend_Log_Writer_Stream($logdir . '/agi.log');
// Filtramos a 'sujeira' dos logs se não estamos em debug mode.
if(!$debug) {
    $filter = new Zend_Log_Filter_Priority(Zend_Log::WARN);
    $writer->addFilter($filter);
}
$log->addWriter($writer);

// Iniciando banco de dados
$db = Zend_Db::factory('Pdo_Mysql', $config->ambiente->db->toArray());
Zend_Db_Table::setDefaultAdapter($db);
Zend_Registry::set('db', $db);

$agiconfig['debug'] = false;
$agiconfig['error_handler'] = false;
$asterisk = new Asterisk_AGI( null, $agiconfig );

// Definindo aonde serão escritos os logs
$console_writer = new PBX_Asterisk_Log_Writer($asterisk);
$log->addWriter($console_writer);

if(!$debug) {
    $filter = new Zend_Log_Filter_Priority(Zend_Log::INFO);
    $console_writer->addFilter($filter);
}

$format = "{$asterisk->request['agi_callerid']} -> {$asterisk->request['agi_extension']} %priorityName% (%priority%):%message%";
$console_formatter = new Zend_Log_Formatter_Simple($format . PHP_EOL);
$console_writer->setFormatter($console_formatter);

// usando nosso próprio objeto de requisições AGI
$request = new PBX_Asterisk_AGI_Request($asterisk->request);

$format = "%timestamp% - $request->origem -> $request->destino %priorityName% (%priority%):%message%";
$formatter = new Zend_Log_Formatter_Simple($format . PHP_EOL);
$writer->setFormatter($formatter);

// sobreescrevendo request padrão do PhpAgi
$asterisk->requestObj = $request;
