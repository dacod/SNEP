<?php

/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as
 *  published by the Free Software Foundation, either version 3 of
 *  the License, or (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 */

/**
 * Class to work with the config files of the different extension technologies on snep.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Lucas Ivan Seidenfus
 * 
 */
class Snep_InterfaceConf {
    
    public static function loadConfFromDb() {

        $view = new Zend_View();
        
        foreach (array("sip", "iax2") as $tech) {
            $config = Zend_Registry::get('config');
            $asteriskDirectory = $config->system->path->asterisk->conf;

            $extenFileConf = "$asteriskDirectory/snep/snep-$tech.conf";
            $trunkFileConf = "$asteriskDirectory/snep/snep-$tech-trunks.conf";

            if (!is_writable($extenFileConf)) {
                return $view->translate("Arquivo de configuração ") . $extenFileConf .  $view->translate("sem permissão de escrita.");
            }
            if (!is_writable($trunkFileConf)) {
                return $view->translate("Arquivo de configuração ") . $trunkFileConf .$view->translate("sem permissão de escrita.");
            }
            /* clean snep-sip.conf file */
            file_put_contents($extenFileConf, '');

            /* Register header on output string of the file */
            $todayDate = date("d/m/Y H:m:s");
            $header = ";------------------------------------------------------------------------------------\n";
            $header .= "; Arquivo: snep-$tech.conf - Cadastro de ramais                                        \n";
            $header .= ";                                                                                    \n";
            $header .= "; Atualizado em: $todayDate                                                         \n";
            $header .= "; Copyright(c) 2008 Opens Tecnologia                                                 \n";
            $header .= ";------------------------------------------------------------------------------------\n";
            $header .= "; Os registros a Seguir sao gerados pelo Software SNEP.                              \n";
            $header .= "; Este Arquivo NAO DEVE ser editado Manualmente sob riscos de                        \n";
            $header .= "; causar mau funcionamento do Asterisk                                               \n";
            $header .= ";------------------------------------------------------------------------------------\n";

            /* query that gets information of the peers on the DB */
            $sql = "SELECT * FROM peers WHERE name != 'admin' AND canal like '" . strtoupper($tech) . "%'";
            try {
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $now = $stmt->rowCount();
            } catch (Exception $e) {
                return  $view->translate("Erro na execução da consulta para coleta dos dados: ") . $e->getMessage();
            }

            $peers = "\n";
            $trunk = "\n";

            if ($now > 0) {
                $database = Zend_Registry::get('db');
                foreach ($stmt->fetchAll() as $peer) {

                    $sipallow = explode(";", $peer['allow']);
                    $allow = '';
                    foreach ($sipallow as $siper) {
                        if ($siper != '') {
                            $allow .= $siper . ",";
                        }
                    }
                    $allow = substr($allow, 0, strlen($allow) - 1);

                    if ($peer['peer_type'] == 'T') {

                        $select = $database->select()->from('trunks')->where("name = {$peer['name']}");
                        unset($stmt);
                        $stmt = $database->query($select);
                        $trunk = $stmt->fetchObject();

                        if ($trunk->type == "SNEPSIP") {
                            /* Assemble trunk entries */
                            $peers .= '[' . $peer['username'] . "]\n";
                            $peers .= 'type=' . $peer['type'] . "\n";
                            $peers .= 'context=' . $peer['context'] . "\n";
                            $peers .= 'canreinvite=' . $peer['canreinvite'] . "\n";
                            $peers .= 'dtmfmode=' . ($peer['dtmfmode'] ? $peer['dtmfmode'] : "rfc2833") . "\n";
                            $peers .= 'host=' . $peer['host'] . "\n";
                            $peers .= 'qualify=' . ($peer['qualify'] == "no" ? "no" : "yes") . "\n";
                            $peers .= 'nat=' . $peer['nat'] . "\n";
                            $peers .= 'disallow=' . $peer['disallow'] . "\n";
                            $peers .= 'allow=' . $allow . "\n";
                            $peers .= "\n";
                        } else if ($trunk->type == "SNEPIAX2") {
                            /* Assemble Extension entries */
                            $peers .= '[' . $peer['username'] . "]\n";
                            $peers .= 'type=' . $peer['type'] . "\n";
                            $peers .= 'username=' . $peer['username'] . "\n";
                            $peers .= 'secret=' . $peer['username'] . "\n";
                            $peers .= 'context=' . $peer['context'] . "\n";
                            $peers .= 'canreinvite=' . $peer['canreinvite'] . "\n";
                            $peers .= 'dtmfmode=' . ($peer['dtmfmode'] ? $peer['dtmfmode'] : "rfc2833") . "\n";
                            $peers .= 'host=' . $peer['host'] . "\n";
                            $peers .= 'qualify=' . ($peer['qualify'] == "no" ? "no" : "yes") . "\n";
                            $peers .= 'nat=' . $peer['nat'] . "\n";
                            $peers .= 'disallow=' . $peer['disallow'] . "\n";
                            $peers .= 'allow=' . $allow . "\n";
                            $peers .= "\n";
                        } else if ($trunk->dialmethod != "NOAUTH") {
                            /* Assemble trunk entries */
                            $peers .= '[' . $peer['username'] . "]\n";
                            $peers .= 'type=' . $peer['type'] . "\n";
                            $peers .= 'context=' . $peer['context'] . "\n";
                            $peers .= ( $peer['fromdomain'] != "") ? ('fromdomain=' . $peer['fromdomain'] . "\n") : "";
                            $peers .= ( $peer['fromuser'] != "") ? ('fromuser=' . $peer['fromuser'] . "\n") : "";
                            $peers .= 'canreinvite=' . $peer['canreinvite'] . "\n";
                            $peers .= 'dtmfmode=' . ($peer['dtmfmode'] ? $peer['dtmfmode'] : "rfc2833") . "\n";
                            $peers .= 'host=' . $peer['host'] . "\n";
                            $peers .= 'qualify=' . $peer['qualify'] . "\n";
                            $peers .= 'nat=' . $peer['nat'] . "\n";
                            $peers .= 'disallow=' . $peer['disallow'] . "\n";
                            $peers .= 'allow=' . $allow . "\n";

                            if ($peer['port'] != "") {
                                $peers .= 'port=' . $peer['port'] . "\n";
                            }
                            if ($peer['call-limit'] != "" && $trunk->type == "SIP") {
                                $peers .= 'call-limit=' . $peer['call-limit'] . "\n";
                            }
                            if ($trunk->insecure != "") {
                                $peers .= 'insecure=' . $trunk->insecure . "\n";
                            }
                            if ($trunk->domain != "" && $trunk->type == "SIP") {
                                $peers .= 'domain=' . $trunk->domain . "\n";
                            }
                            if ($trunk->type == "IAX2") {
                                $peers .= 'trunk=' . $peer['trunk'] . "\n";
                            }
                            if ($trunk->reverse_auth) {
                                $peers .= 'username=' . $peer['username'] . "\n";
                                $peers .= 'secret=' . $peer['secret'] . "\n";
                            }
                            $peers .= "\n";
                        }
                        $trunk .= ( $trunk->dialmethod != "NOAUTH" && !preg_match("/SNEP/", $trunk->type) ? "register => " . $peer['username'] . ":" . $peer['secret'] . "@" . $peer['host'] . "\n" : "");
                    } else {
                        /* Assemble Extension entries */
                        $peers .= '[' . $peer['name'] . "]\n";
                        $peers .= 'type=' . $peer['type'] . "\n";
                        $peers .= 'context=' . $peer['context'] . "\n";
                        $peers .= 'host=' . $peer['host'] . "\n"; # dinamyc
                        $peers .= 'secret=' . $peer['secret'] . "\n";
                        $peers .= 'callerid=' . $peer['callerid'] . "\n";
                        $peers .= 'canreinvite=' . $peer['canreinvite'] . "\n";
                        $peers .= 'dtmfmode=' . ($peer['dtmfmode'] ? $peer['dtmfmode'] : "rfc2833") . "\n";
                        $peers .= 'nat=' . $peer['nat'] . "\n";
                        $peers .= 'qualify=' . $peer['qualify'] . "\n";
                        $peers .= 'disallow=' . $peer['disallow'] . "\n";
                        $peers .= 'allow=' . $allow . "\n";

                        $peers .= 'username=' . $peer['name'] . "\n";

                        $peers .= 'fromuser=' . $peer['name'] . "\n";

                        $peers .= 'call-limit=' . $peer['call-limit'] . "\n";

                        $peers .= "\n";
                    }
                }
                unset($database);
            }

            $trunkcont = str_replace(".conf", "-trunks.conf", $header) . $trunk;
            file_put_contents($trunkFileConf, $trunkcont);

            $content = $header . $peers;

            file_put_contents($extenFileConf, $content);
        }
        // Forcing asterisk to reload the configs
        try {
            
            $asteriskAmi = PBX_Asterisk_AMI::getInstance();
            $asteriskAmi->Command("sip reload");
            $asteriskAmi->Command("iax2 reload");
            
        } catch (Exception $e) {
            return  $view->translate("Erro ao executar comandos de recarga no asterisk: ") . $e->getMessage();
        }
        
        return true;
    }

}
