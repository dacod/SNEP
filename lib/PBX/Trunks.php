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
 * Classe que cuida da persistencia de troncos no banco de dados do snep.
 *
 * Nota sobre a persistencia: O controle de persistencia é feito no snep em
 * classes separadas. Não no construtor da classe modelo como se ve em outros
 * frameworks e arquiteturas. O motivo disso é que se ocorrer uma mudança na
 * forma como é feita a persistencia desses objetos os mesmos não precisam ser
 * alterados. Isso aumenta a compactibilidade com código legado.
 * ~henrique
 *
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Trunks {
    private function __construct() { /* Protegendo métodos dinâmicos */ }
    private function __destruct() { /* Protegendo métodos dinâmicos */ }
    private function __clone() { /* Protegendo métodos dinâmicos */ }

    /**
     * Método para obter todos os troncos registrados no sistema.
     *
     * @return Array com todos os usuarios do snep.
     */
    public static function getAll() {
        $db = Snep_Db::getInstance();

        $select = $db->select('id')
        ->from('trunks')
        ->order('id');

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();

        $objetos = array();
        foreach($result as $tronco) {
            $objetos[] = self::get($tronco['id']);
        }

        return $objetos;
    }

    
    /**
     * Retorna um tronco do banco de dados do snep.
     *
     * @param int $id Numero do tronco a ser obtido
     */
    public static function get($id) {
        $db = Snep_Db::getInstance();

        $select = $db->select()->from('trunks')->where("id = $id");
        $stmt = $db->query($select);
        $rawTrunk = $stmt->fetchObject();
        if(!$rawTrunk) {
            throw new PBX_Exception_NotFound("Tronco $id nao encontrado");
        }

        $tech = $rawTrunk->type;

        if( ($tech == "SIP" || $tech == "IAX2") && $rawTrunk->dialmethod == "NOAUTH" ) {
            $config = array('host' => $rawTrunk->host);
            if($tech == "SIP")
                $interface = new PBX_Asterisk_Interface_SIP_NoAuth($config);
            else
                $interface = new PBX_Asterisk_Interface_IAX2_NoAuth($config);
        }
        else if($tech == "SIP") {
            $config = array(
                "username"=>$rawTrunk->username,
                "secret"=>$rawTrunk->secret,
                "host"=>$rawTrunk->host
            );
            $interface = new PBX_Asterisk_Interface_SIP($config);
        }
        else if($tech == "IAX2") {
            $config = array(
                "username"=>$rawTrunk->username,
                "secret"=>$rawTrunk->secret,
                "host"=>$rawTrunk->host
            );
            $interface = new PBX_Asterisk_Interface_IAX2($config);
        }
        else if($tech == "KHOMP") {
            $khomp_id = substr($rawTrunk->channel, strpos($rawTrunk->channel, '/')+1);
            $config = array(
                "board" => substr($khomp_id, 1, 1)
            );
            if(substr($khomp_id, 2, 1) == 'c') {
                $config['channel'] = substr($khomp_id, strpos($khomp_id, 'c')+1);
            }
            else if(substr($khomp_id, 2, 1) == 'l') {
                $config['link'] = substr($khomp_id, strpos($khomp_id, 'l')+1);
            }
            $interface = new PBX_Asterisk_Interface_KHOMP($config);
        }
        else {
            $interface = new PBX_Asterisk_Interface_VIRTUAL(array("channel" => $rawTrunk->channel, "channel_regex" => $rawTrunk->channel));
        }

        $trunk = new Snep_Trunk($rawTrunk->callerid, $interface);

        if($rawTrunk->map_extensions) {
            $trunk->setExtensionMapping(true);
        }
        
        $trunk->setId($id);

        $trunk->setDtmfDialMode($rawTrunk->dtmf_dial ? true : false);
        $trunk->setDtmfDialNumber($rawTrunk->dtmf_dial_number);

        return $trunk;
    }
}
