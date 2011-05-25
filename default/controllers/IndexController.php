<?php

require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("Welcome to Snep version %s", SNEP_VERSION);

        // Direcionando para o "snep antigo"
        $config = Zend_Registry::get('config');
        $db = Zend_Registry::get('db');

        $linfoData = new Zend_Http_Client('http://localhost/snep/lib/linfo/index.php?out=xml');
        try {
            $linfoData->request();
            $sysInfo = $linfoData->getLastResponse()->getBody();
            $sysInfo = simplexml_load_string($sysInfo);
        } catch (HttpException $ex) {
            echo $ex;
        }

        if (trim($config->ambiente->db->host) == "") {
            $this->_redirect("/installer/");
        } else {

            $systemInfo = array();
            $uptimeRaw = explode(';', $sysInfo->core->uptime);
            $systemInfo['uptime'] = $uptimeRaw[0];

            require_once "includes/AsteriskInfo.php";
            $astinfo = new AsteriskInfo();

            $astVersionRaw = explode('@', $astinfo->status_asterisk("core show version", "", True));

            preg_match('/Asterisk (.*) built/', $astVersionRaw[0], $astVersion);

            $systemInfo['asterisk'] = $astVersion[1];

            $systemInfo['mysql'] = trim(exec("mysql -V | awk -F, '{ print $1 }' | awk -F'mysql' '{ print $2 }'"));

            $systemInfo['linux_ver'] = $sysInfo->core->os . ' / ' . $sysInfo->core->Distribution;

            $systemInfo['linux_kernel'] = $sysInfo->core->kernel;

            $cpuRaw = explode('-', $sysInfo->core->CPU);
            $systemInfo['hardware'] = $cpuRaw[1];

            $cpuNumber = count(explode('<br />', $sysInfo->core->CPU));

            $cpuUsageRaw = explode(' ', $sysInfo->core->load);
            $loadAvarege = ($cpuUsageRaw[0] + $cpuUsageRaw[1] + $cpuUsageRaw[2]) / 3;

            $systemInfo['usage'] = round(($loadAvarege * 100) / ($cpuNumber - 1));

            $systemInfo['memory']['ram'] = array('total' => $this->byte_convert(floatval($sysInfo->memory->Physical->total)), 
                                                                       'free' =>  $this->byte_convert(floatval($sysInfo->memory->Physical->free)), 
                                                                       'used' => $this->byte_convert(floatval($sysInfo->memory->Physical->used)), 
                                                                       'percent' => round(floatval($sysInfo->memory->Physical->used) / floatval($sysInfo->memory->Physical->total)*100));
            $systemInfo['memory']['swap'] = array('total' => $this->byte_convert(floatval($sysInfo->memory->swap->core->free)), 
                                                                         'free' => $this->byte_convert(floatval($sysInfo->memory->swap->core->total)), 
                                                                         'used' => $this->byte_convert(floatval($sysInfo->memory->swap->core->used)), 
                                                                         'percent' => round(floatval($sysInfo->memory->swap->core->used) / floatval($sysInfo->memory->swap->core->total)*100));
            
            $deviceArray = $sysInfo->mounts->mount;
            foreach ($deviceArray as $mount) {
                 $systemInfo['space'][] = array('mount_point' =>$mount["mountpoint"], 
                                                          'size' => $this->byte_convert(floatval($mount["size"])), 
                                                          'free' => $this->byte_convert(floatval($mount["free"])), 
                                                          'percent' => round((floatval($mount["used"])/floatval($mount["size"]))*100));
            }    
            
            $netArray = $sysInfo->net->interface;
            $count = 0;
            
            foreach ($netArray as $board) {
                if ($count < 6){
                 $systemInfo['net'][] = array(
                                                          'device' => $board["device"], 
                                                          'up' => $board["state"]);
                 $count++;
                }
            }           

            $sqlN = "select count(*) from";
            $select = $db->query($sqlN . ' peers');
            $result = $select->fetch();

            $systemInfo['num_peers'] = $result['count(*)'];

            $select = $db->query($sqlN . ' trunks');
            $result = $select->fetch();

            $systemInfo['num_trunks'] = $result['count(*)'];

            $select = $db->query($sqlN . ' regras_negocio');
            $result = $select->fetch();

            $systemInfo['num_routes'] = $result['count(*)'];

            $systemInfo['modules'] = array();
            $modules = Snep_Modules::getInstance()->getRegisteredModules();
            foreach ($modules as $module) {
                $systemInfo['modules'][] = array(
                    "name" => $module->getName(),
                    "version" => $module->getVersion(),
                    "description" => $module->getDescription()
                );
            }

            $this->view->indexData = $systemInfo;

            // Creates Snep_Inspector Object
            $objInspector = new Snep_Inspector();

            // Get array with status of inspected system requirements
            $inspect = $objInspector->getInspects();

            // Verify errors
            $this->view->error = false;
            foreach ($inspect as $log => $message) {
                if ($message['error'] == 1) {
                    $this->view->error = true;
                }
            }

            // Inspector url
            $this->view->inspector = $this->getFrontController()->getBaseUrl() . '/inspector/';
        }
    }
    
    function byte_convert($size, $precision = 2) {


	// Sanity check
	if (!is_numeric($size))
		return '?';
	
	// Get the notation
	$notation = 1024;

	// Fixes large disk size overflow issue
	// Found at http://www.php.net/manual/en/function.disk-free-space.php#81207
	$types = array('B', 'KB', 'MB', 'GB', 'TB');
	$types_i = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
	for($i = 0; $size >= $notation && $i < (count($types) -1 ); $size /= $notation, $i++);
	return(round($size, $precision) . ' ' . ($notation == 1000 ? $types[$i] : $types_i[$i]));
}

}
