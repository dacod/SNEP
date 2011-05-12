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
// Tratamento de sinais vindos do asterisk
declare(ticks = 1);
if (function_exists('pcntl_signal')) {
        pcntl_signal(SIGHUP,  SIG_IGN);
}

// Controle da exibição de erros
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

$config_file = "/var/www/snep/includes/setup.conf";

//encontrado diretórios do sistema
if(!file_exists($config_file)) {
    echo "VERBOSE \"FATAL ERROR: config file '$config_file' not found\" 1\n";
    exit(1);
}
$config = parse_ini_file($config_file,true);

// Adicionando caminho de libs ao include path para autoloader trabalhar:
set_include_path($config['system']['path.base'] . "/lib" . PATH_SEPARATOR  . get_include_path());
$logdir = $config['system']['path.base'] . "/log";
unset($config);

require_once "Snep/Bootstrap/Agi.php";
$bootstrap = new Snep_Bootstrap_Agi($config_file);
$bootstrap->boot();

$asterisk = Zend_Registry::get('asterisk');
$config = Zend_Registry::get('config');
$db = Zend_Registry::get('db');
$request = $asterisk->requestObj;
$log = Zend_Registry::get('log');
