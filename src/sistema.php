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
require_once("../includes/verifica.php");
require_once("../configs/config.php");

if( preg_match("/MSIE (?<version>.*?);/", $_SERVER['HTTP_USER_AGENT'], $browser_version) && (float) $browser_version['version'] < 8 ) {
    $smarty->assign("IE_ERROR",true);
}

// Tempo do SIS
$SIS=array() ;
$up = explode(",",exec("uptime")) ;
$uptime = substr($up[0],strpos($up[0],"up")+2) ;
if (strpos($uptime,"min") > 0) {
    $SIS['uptime'] = substr($uptime,0,strpos($uptime,"min")+3);
} elseif (strpos($uptime,":") > 0) {
    $up_tmp=explode(":",$uptime) ;
    $SIS['uptime']=$up_tmp[0]."&nbsp;".$LANG['hour'].",&nbsp;".$up_tmp[1]."&nbsp;".$LANG['minutes'] ;
} else {
    $SIS['uptime'] = substr($uptime,0,strpos($uptime,"day"))."&nbsp;".$LANG['days'].",&nbsp;";
    $up_tmp=explode(":",$up[1]) ;
    $SIS['uptime'].= $up_tmp[0]."&nbsp;".$LANG['hour'].",&nbsp;".$up_tmp[1]."&nbsp;".$LANG['minutes'] ;
}
// Status asterisk
$SIS['ast_vers'] = exec("/usr/sbin/asterisk -V") ;

// Mysql Version
$SIS['mysql_vers'] = exec("mysql -V | awk -F, '{ print $1 }' | awk -F'mysql' '{ print $2 }'");

// Linux Version
if(file_exists("/etc/slackware-version")) {
    exec("cat /etc/slackware-version",$linux_vers) ;
    $SIS['linux_vers'] = $linux_vers[0];
}
else {
    exec("cat /etc/issue",$linux_vers) ;
    $SIS['linux_vers'] = substr($linux_vers[0],0,strpos($linux_vers[0],"\\")) ;
}
// Kernel
$SIS['linux_kernel'] = exec("uname -rv") ;

// Machine
$hard_1 = exec("cat /proc/cpuinfo | grep name |  awk -F: '{print $2}'") ;
$hard_2 = exec("cat /proc/cpuinfo | grep MHz |  awk -F: '{print $2}'") ;
$SIS['hardware'] = $hard_1." , ".$hard_2." Mhz" ; 
// Memoria
$SIS['memory'] = sys_meminfo() ;
// Espaco em Disco
$SIS['space'] = sys_fsinfo() ;

// Status diversos do asterisk
$SIS['sip_peers'] = ast_status("sip show  peers","sip peers"  ) ;

$SIS['sip_channels'] = ast_status("sip show channels","SIP channel" ) ;
$SIS['iax2_peers'] = ast_status("iax2 show peers","iax2 peers" ) ;
$SIS['agents'] = ast_status("show agents", "agents configured" ) ;

// Status do SIS
$path_voz = $SETUP['ambiente']['path_voz'];
$SIS['num_arqvoz'] = exec("scripts/num_arquivos ../". $path_voz) ;
//$SIS['spc_arqvoz'] = exec("du -sch $path_voz | cut -f1") ;
$SIS['spc_arqvoz'] = "" ;

$SIS['modules'] = array();
$modules = Snep_Modules::getInstance()->getRegisteredModules();
foreach ($modules as $module) {
    $SIS['modules'][] = array(
        "name"        => $module->getName(),
        "version"     => $module->getVersion(),
        "description" => $module->getDescription()
    );
}

// Cria Objeto bargraph
$my_bargraph = new Bar_Graph ;
$smarty->register_object("bargraph",$my_bargraph) ; 

$smarty->assign ('SIS',$SIS) ; 
$titulo = $LANG['menu_status'];
display_template("sistema.tpl",$smarty,$titulo) ;

/*-----------------------------------------------------------------------------
 * Funcao:  sys_meminfo - Cria array com status da memoria do servidor
 * Retorna: Array associativo de 2 arrays associativos, contendo estatisticas
 *          da memoria.
 * Copyright(c): phpSysInfo - A PHP System Information Script
 *              http://phpsysinfo.sourceforge.net
 *-----------------------------------------------------------------------------*/  
function sys_meminfo () {
    $results['ram'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
    $results['swap'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
    $results['devswap'] = array();

    $bufr = rfts( '/proc/meminfo' );

    if ( $bufr != "ERROR" ) {
        $bufe = explode("\n", $bufr);
        foreach( $bufe as $buf ) {
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

        $bufr = rfts( '/proc/swaps' );
        if ( $bufr != "ERROR" ) {
            $swaps = explode("\n", $bufr);
            for ($i = 1; $i < (sizeof($swaps)); $i++) {
                if ( trim( $swaps[$i] ) != "" ) {
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
/*-----------------------------------------------------------------------------
 * Funcao:  sys_fsinfo - Cria array com status das particoes montadas
 * Retorna: Array associativo de arrays associativos, contendo estatisticas
 *          dos discos montados no sistema.
 * Copyright(c): phpSysInfo - A PHP System Information Script
 *              http://phpsysinfo.sourceforge.net
 *-----------------------------------------------------------------------------*/  
function sys_fsinfo () {
    $df = execute_program('df', '-kP');
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
        if  ($fstype[$ar_buf[5]] == "tmpfs")
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
