<?php
/*
 * ATTENTION!
 *
 * THIS SCRIPT ESPECTS TO BE 2 LEVELS FROM THE SNEP ROOT. AND THIS IS HARD CODED.
 *
 * The sole purpose of this file is to serve system variables to javascript code over the snep views.
 * 
 */

header("Content-Type: application/x-javascript");

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . "/../../"));

// Add standard library to the include path
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/lib',
    get_include_path(),
)));

// Initializing Snep Config
require_once "Snep/Config.php";
Snep_Config::setConfigFile(APPLICATION_PATH . '/includes/setup.conf');

$config = Snep_Config::getConfig();

defined('SNEP_VENDOR') || define('SNEP_VENDOR', $config->ambiente->emp_nome);
defined('SNEP_VERSION') || define('SNEP_VERSION', trim(file_get_contents(APPLICATION_PATH . "/configs/snep_version")));

// Define application environment
$snep_env = Snep_Config::getConfig()->system->debug ? "development" : "production";
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : $snep_env));

?>
SNEP_VERSION = "<?php echo SNEP_VERSION; ?>";
SNEP_BASEURL = "<?php echo $config->system->path->web; ?>";
SNEP_SCRIPTURL = "<?php echo $config->system->path->web; ?>/index.php";