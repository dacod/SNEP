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
        'version|v' => 'Prints version.',
        'outgoing_number|o=s' => 'Define a outgoing number',
        'xfer|x=s' => 'Replace the channel used for source identification.'
    ));
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    $log->err($e->getMessage());
    $log->err($e->getUsageMessage());
    exit;
}

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

$log->info("Call from $request->origem ($request->channel) to $request->destino");

try {
    $dialplan = new PBX_Dialplan();
    $dialplan->setRequest($asterisk->requestObj);
    $dialplan->parse();

    $regra = $dialplan->getLastRule();
} catch (PBX_Exception_NotFound $ex) {
    $log->info("No valid rule for this request: " . $ex->getMessage());
    if (!$opts->xfer) {
        $asterisk->answer();
        $asterisk->stream_file('invalid');
        $asterisk->hangup();
    }
    exit();
} catch (Exception $ex) {
    $log->crit("Oops! Exception resolving routing rule.");
    $log->crit($ex);
    die();
}

// Recording file spec
// Format: Timestamp_aaaammdd_hhmm_src_dst.wav
$filename = implode("_", array(
            time(),
            date("Ymd"),
            date("Hi"),
            $request->getOriginalCallerid(),
            $request->getOriginalExtension()
        ));

// Defining the CDR(userfield) to call file name so we can find it later.
$lastuserfield = $asterisk->get_variable('CDR(userfield)');
if ($lastuserfield['data'] === "") {
    $asterisk->set_variable("CDR(userfield)", $filename);
} else {
    $filename = $lastuserfield['data'];
}

$recordPath = realpath($config->ambiente->path_voz);
$regra->setRecordApp($config->general->record->application, array($recordPath . "/" . $filename . ".wav", $config->general->record->flags));

$regra->setAsteriskInterface($asterisk);

try {
    $log->info("Executing rule {$regra->getId()}:$regra");
    $regra->execute();
    $log->info("End of execution of rule {$regra->getId()}:$regra");
} catch (PBX_Exception_AuthFail $ex) {
    $log->info("Failure to authenticate extension. Check password.");
} catch (Exception $ex) {
    $log->crit($ex);
    die();
}
