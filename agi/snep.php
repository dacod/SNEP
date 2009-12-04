#!/usr/bin/php-cgi -q
<?php

/**
 * @file Executável AGI SNEP.
 *
 * Executável AGI que faz o controle de ligações no dialplan do Asterisk.
 *
 * Este aplicativo inicia o ambiente para que a biblioteca do snep possa
 * trabalhar no encaminhamento das ligações.
 */

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
$logdir = $config['system']['path.log'];
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

// Versão do SNEP
Zend_Registry::set('snep_version', file_get_contents($config->system->path->base . "/configs/snep_version"));

// Configuração das opções da linha de comando
try {
    $opts = new Zend_Console_Getopt(
      array(
        'version|v'    => 'Imprime versao do snep.',
        'xfer|x=s'     => 'Define um canal específico para uso na execução.'
      )
    );
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getMessage();
    echo $e->getUsageMessage();
    exit;
}

// Imprime versão :)
if($opts->version) {
    echo "SNEP Version " . Zend_Registry::get('snep_version') . "\n";
    exit;
}

// Iniciando sistema de logs
$log = new Zend_Log();
Zend_Registry::set('log', $log);

// Definindo aonde serão escritos os logs
$writer = new Zend_Log_Writer_Stream($logdir . '/agi.log');
// Filtramos a 'sujeira' dos logs se não estamos em debug mode.
if(!$debug) {
    $filter = new Zend_Log_Filter_Priority(Zend_Log::NOTICE);
    $writer->addFilter($filter);
}
$log->addWriter($writer);

// Iniciando banco de dados
$db = Zend_Db::factory('Pdo_Mysql', $config->ambiente->db->toArray());
Zend_Db_Table::setDefaultAdapter($db);
Zend_Registry::set('db', $db);

// Iniciando objeto para comunicação com o asterisk
$agiconfig['debug'] = false;
$agiconfig['error_handler'] = false;
$asterisk = new Asterisk_AGI( null, $agiconfig );

if($opts->xfer) {
    $asterisk->request['agi_channel'] = $opts->xfer;
}

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
if($opts->xfer) {
    //$request->setSrcObj($PBX_Interfaces::getChannelOwner($opts->xfer));
}

$format = "%timestamp% - $request->origem -> $request->destino %priorityName% (%priority%):%message%";
$formatter = new Zend_Log_Formatter_Simple($format . PHP_EOL);
$writer->setFormatter($formatter);

// sobreescrevendo request padrão do PhpAgi
$asterisk->requestObj = $request;

// Primeira informação sobre a ligação
$log->info("Tentativa de conexao de $request->origem ($request->channel) para $request->destino");

try {
    // Procurando por regra de negócio no banco de dados
    $dialplan = new PBX_Dialplan();
    $dialplan->setRequest($asterisk->requestObj);
    $dialplan->parse();

    $regra = $dialplan->getLastRule();
}
catch(PBX_Exception_NotFound $ex) {
    $log->info("Nenhuma regra valida para essa requisicao: " . $ex->getMessage());
    $asterisk->answer();
    $asterisk->stream_file('invalid');
    $asterisk->hangup();
    exit();
}
catch(Exception $ex) {
    $log->crit("Oops! Excecao ao resolver regra de negocio, contate o suporte tecnico");
    $log->crit($ex);
    die();
}

// Definindo nome do arquivo de gravação.
// Formato: Timestamp_aaaammdd_hhmm_src_dst.wav
$filename = implode("_", array(
    time(),
    date("Ymd"),
    date("Hi"),
    $request->getOriginalCallerid(),
    $request->getOriginalExtension()
));
// Definindo userfield com o nome do arquivo para que se possa encontrar a
// gravação a partir do registro no CDR.
$lastuserfield = $asterisk->get_variable('CDR(userfield)');
if($lastuserfield['data'] != "") {
    $asterisk->set_variable("CDR(userfield)", $lastuserfield['data']);
}
else {
    $asterisk->set_variable("CDR(userfield)", $filename);
}


// Variaveis sendo definidas para manutenção da compactibilidade com recurso
// legado do snep.
$asterisk->set_variable("__CALLFILENAME", $filename); // setando a variavel callfilename
$asterisk->set_variable("__TOUCH_MONITOR", $filename); // setando a variavel touch_monitor

/**
 * TODO: Corrigir caminho para gravações. Demanda alteração em todo sistema.
 */
$recordPath = realpath("../" . $config->ambiente->path_voz);
//Definindo aplicação de gravação.
$regra->setRecordApp($config->general->record->application, array($recordPath . "/" . $filename . ".wav", $config->general->record->flags));

$regra->setAsteriskInterface($asterisk);

if($opts->xfer) {
    //$regra->dontRecord();
}

try {
    $log->info("Executando regra {$regra->getId()}:$regra");
    $regra->execute();
    $log->info("Fim de execucao da regra {$regra->getId()}:$regra");
}
catch(PBX_Exception_AuthFail $ex) {
    $log->info("Falha na autenticacao do ramal.");
}
catch (Exception $ex) {
    $log->crit($ex);
    die();
}
