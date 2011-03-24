#!/usr/bin/php-cgi -q
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

// Controle da exibição de erros
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

require_once "Bootstrap.php";
new Bootstrap();

require_once "Snep/Config.php";
require_once "Snep/Logger.php";
require_once "PBX/Asterisk/AGI.php";
require_once "Zend/Console/Getopt.php";

$config = Snep_Config::getConfig();
$log = Snep_Logger::getInstance();
$asterisk = PBX_Asterisk_AGI::getInstance();

// Configuração das opções da linha de comando
try {
    $opts = new Zend_Console_Getopt(array(
        'version|v' => 'Imprime versao do snep.',
        'outgoing_number|o=s' => 'Define um numero para saida da ligação',
        'xfer|x=s' => 'Define um canal específico para uso na execução.'
    ));
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    $log->err($e->getMessage());
    $log->err($e->getUsageMessage());
    exit;
}

// Imprime versão :)
if ($opts->version) {
    echo "SNEP Version " . Zend_Registry::get('snep_version') . "\n";
    exit;
}

if ($opts->xfer) {
    $asterisk->request['agi_channel'] = $opts->xfer;
    $request = new PBX_Asterisk_AGI_Request($asterisk->request);
    $asterisk->requestObj = $request;
}

if ($opts->outgoing_number) {
    Zend_Registry::set("outgoingNumber", $opts->outgoing_number);
} else {
    Zend_Registry::set("outgoingNumber", "");
}

$log = Zend_Registry::get('log');
$request = $asterisk->requestObj;
// Primeira informação sobre a ligação
$log->info("Tentativa de conexao de $request->origem ($request->channel) para $request->destino");

try {
    // Procurando por regra de negócio no banco de dados
    $dialplan = new PBX_Dialplan();
    $dialplan->setRequest($asterisk->requestObj);
    $dialplan->parse();

    $regra = $dialplan->getLastRule();
} catch (PBX_Exception_NotFound $ex) {
    $log->info("Nenhuma regra valida para essa requisicao: " . $ex->getMessage());
    if (!$opts->xfer) {
        $asterisk->answer();
        $asterisk->stream_file('invalid');
        $asterisk->hangup();
    }
    exit();
} catch (Exception $ex) {
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
if ($lastuserfield['data'] === "") {
    $asterisk->set_variable("CDR(userfield)", $filename);
} else {
    $filename = $lastuserfield['data'];
}


// Variaveis sendo definidas para manutenção da compactibilidade com recurso
// legado do snep.
$asterisk->set_variable("__CALLFILENAME", $filename); // setando a variavel callfilename
$asterisk->set_variable("__TOUCH_MONITOR", $filename); // setando a variavel touch_monitor

/**
 * TODO: Corrigir caminho para gravações. Demanda alteração em todo sistema.
 */
$recordPath = realpath($config->ambiente->path_voz);
//Definindo aplicação de gravação.
$regra->setRecordApp($config->general->record->application, array($recordPath . "/" . $filename . ".wav", $config->general->record->flags));

$regra->setAsteriskInterface($asterisk);

if ($opts->xfer) {
    //$regra->dontRecord();
}

/*
foreach ($bootstrap->getRulePlugins() as $plugin) {
    $regra->registerPlugin($plugin);
}
 */

try {
    $log->info("Executando regra {$regra->getId()}:$regra");
    $regra->execute();
    $log->info("Fim de execucao da regra {$regra->getId()}:$regra");
} catch (PBX_Exception_AuthFail $ex) {
    $log->info("Falha na autenticacao do ramal.");
} catch (Exception $ex) {
    $log->crit($ex);
    die();
}
