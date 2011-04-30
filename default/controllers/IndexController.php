<?php

require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->breadcrumb = $this->view->translate("Welcome to Snep version %s", SNEP_VERSION);

        // Direcionando para o "snep antigo"
        $config = Zend_Registry::get('config');
        $db = Zend_Registry::get('db');

        if (trim($config->ambiente->db->host) == "") {
            $this->_redirect("/installer/");
        } else {
            $systemInfo = array();
            $uptimeRaw = explode(",", exec("uptime"));
            $uptimeRaw = substr($uptimeRaw[0], strpos($uptimeRaw[0], "up") + 2);

            if (strpos($uptimeRaw, "min") > 0) {
                $systemInfo['uptime'] = substr($uptimeRaw, 0, strpos($uptimeRaw, "min") + 3);
            } elseif (strpos($uptimeRaw, ":") > 0) {
                $uptimeTmp = explode(":", $uptimeRaw);
                $systemInfo['uptime'] = $uptimeTmp[0] . $this->view->translate(' hora(s), ') . $uptimeTmp[1] . $this->view->translate(' minutos');
            } else {
                $systemInfo['uptime'] = substr($uptimeRaw, 0, strpos($uptimeRaw, "day")) . $this->view->translate(' dias, ');
                $uptimeTmp = explode(":", $up[1]);
                $systemInfo['uptime'].= $uptimeTmp[0] . $this->view->translate(' hora(s), ') . $uptimeTmp[1] . $this->view->translate(' minutos');
            }
            $systemInfo['uptime'] = trim($systemInfo['uptime']);

            $systemInfo['asterisk'] = exec("/usr/sbin/asterisk -V ");

            $systemInfo['mysql'] = trim(exec("mysql -V | awk -F, '{ print $1 }' | awk -F'mysql' '{ print $2 }'"));

            if (file_exists("/etc/slackware-version")) {
                exec("cat /etc/slackware-version", $linuxVer);
                $systemInfo['linux_ver'] = $linuxVer[0];
            } else {
                exec("cat /etc/issue", $linuxVer);
                $systemInfo['linux_ver'] = substr($linuxVer[0], 0, strpos($linuxVer[0], "\\"));
            }

            $systemInfo['linux_kernel'] = exec("uname -sr");

            $hard1 = exec("cat /proc/cpuinfo | grep name |  awk -F: '{print $2}'");
            $hard2 = exec("cat /proc/cpuinfo | grep MHz |  awk -F: '{print $2}'");
            $systemInfo['hardware'] = trim($hard1 . " , " . $hard2 . " Mhz");

            $systemInfo['memory'] = $this->sys_meminfo();
            $systemInfo['space'] = $this->sys_fsinfo();

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
        }
    }

    private function sys_meminfo() {
        $results['ram'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
        $results['swap'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
        $results['devswap'] = array();

        $bufr = $this->rfts('/proc/meminfo');

        if ($bufr != "ERROR") {
            $bufe = explode("\n", $bufr);
            foreach ($bufe as $buf) {
                if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['total'] = $ar_buf[1];
                } else if (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['free'] = $ar_buf[1];
                } else if (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['cached'] = $ar_buf[1];
                } else if (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $results['ram']['buffers'] = $ar_buf[1];
                }
            }
            $results['ram']['used'] = $results['ram']['total'] - $results['ram']['free'];
            $results['ram']['percent'] = round(($results['ram']['used'] * 100) / $results['ram']['total']);
            // values for splitting memory usage
            if (isset($results['ram']['cached']) && isset($results['ram']['buffers'])) {
                $results['ram']['app'] = $results['ram']['used'] - $results['ram']['cached'] - $results['ram']['buffers'];
                $results['ram']['app_percent'] = round(($results['ram']['app'] * 100) / $results['ram']['total']);
                $results['ram']['buffers_percent'] = round(($results['ram']['buffers'] * 100) / $results['ram']['total']);
                $results['ram']['cached_percent'] = round(($results['ram']['cached'] * 100) / $results['ram']['total']);
            }

            $bufr = $this->rfts('/proc/swaps');
            if ($bufr != "ERROR") {
                $swaps = explode("\n", $bufr);
                for ($i = 1; $i < (sizeof($swaps)); $i++) {
                    if (trim($swaps[$i]) != "") {
                        $ar_buf = preg_split('/\s+/', $swaps[$i], 6);
                        $results['devswap'][$i - 1] = array();
                        $results['devswap'][$i - 1]['dev'] = $ar_buf[0];
                        $results['devswap'][$i - 1]['total'] = $ar_buf[2];
                        $results['devswap'][$i - 1]['used'] = $ar_buf[3];
                        $results['devswap'][$i - 1]['free'] = ($results['devswap'][$i - 1]['total'] - $results['devswap'][$i - 1]['used']);
                        $results['devswap'][$i - 1]['percent'] = round(($ar_buf[3] * 100) / $ar_buf[2]);
                        $results['swap']['total'] += $ar_buf[2];
                        $results['swap']['used'] += $ar_buf[3];
                        $results['swap']['free'] = $results['swap']['total'] - $results['swap']['used'];
                        $results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);
                    }
                }
            }
        }
        return $results;
    }

    private function rfts($strFileName, $intLines = 0, $intBytes = 4096) {
        $strFile = "";
        $intCurLine = 1;
        if (file_exists($strFileName)) {
            if ($fd = fopen($strFileName, 'r')) {
                while (!feof($fd)) {
                    $strFile .= fgets($fd, $intBytes);
                    if ($intLines <= $intCurLine && $intLines != 0) {
                        break;
                    } else {
                        $intCurLine++;
                    }
                }
                fclose($fd);
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }
        return $strFile;
    }

    private function sys_fsinfo() {
        $df = $this->execute_program('df', '-kP');
        $mounts = explode("\n", $df);
        $fstype = array();
        if ($fd = fopen('/proc/mounts', 'r')) {
            while ($buf = fgets($fd, 4096)) {
                list($dev, $mpoint, $type) = preg_split('/\s+/', trim($buf), 4);
                $fstype[$mpoint] = $type;
                $fsdev[$dev] = $type;
            }
            fclose($fd);
        }

        for ($i = 1; $i < sizeof($mounts); $i++) {
            $ar_buf = preg_split('/\s+/', $mounts[$i], 6);
            if ($fstype[$ar_buf[5]] == "tmpfs")
                continue;
            $results[$i - 1] = array();

            $results[$i - 1]['disk'] = $ar_buf[0];
            $results[$i - 1]['size'] = $ar_buf[1];
            $results[$i - 1]['used'] = $ar_buf[2];
            $results[$i - 1]['free'] = $ar_buf[3];
            $results[$i - 1]['percent'] = round(($results[$i - 1]['used'] * 100) / $results[$i - 1]['size']) . '%';
            $results[$i - 1]['mount_point'] = $ar_buf[5];
            ($fstype[$ar_buf[5]]) ? $results[$i - 1]['fstype'] = $fstype[$ar_buf[5]] : $results[$i - 1]['fstype'] = $fsdev[$ar_buf[0]];
        }

        return $results;
    }

    private function execute_program($program, $params) {
        $path = array('/bin/', '/sbin/', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');
        $buffer = '';
        while ($cur_path = current($path)) {
            if (is_executable("$cur_path/$program")) {
                if ($fp = popen("$cur_path/$program $params", 'r')) {
                    while (!feof($fp)) {
                        $buffer .= fgets($fp, 4096);
                    }
                    return trim($buffer);
                }
            }
            next($path);
        }
    }

}

