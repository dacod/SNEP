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
 * Classe para obtenção de informações a respeito de sistemas khomp.
 *
 * Essa classe agrega várias fontes de informações sobre produtos khomp presente
 * no sistema.
 *
 * @category  Snep
 * @package   PBX_Khomp
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Khomp_Info {
    
    private $asterisk;

    public function __construct() {
        $this->asterisk = PBX_Asterisk_AMI::getInstance();
    }

    /**
     * Método que descobre se o sistema tem ou não placas da khomp instaladas.
     * @return boolean
     */
    public function hasWorkingBoards() {
        $response = $this->asterisk->Command("khomp summary concise");
        
        if(ereg("No such command", $response['data'])) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Retorna informações genéricas sobre uma placa da khomp.
     *
     * Informações retornadas:
     * id, numero da placa no sistema
     * model, Modelo da placa (ex: KGSM-SPX, K2E1-SPX, etc)
     * serial, Numero Serial da placa.
     * channels, Numero de canais da placa.
     * links, Numero de Links da placa.
     *
     * @param array $board_id Id da placa.
     * @return array Informações gerais da(s) placa(s)
     */
    public function boardInfo($board_id = -1) {
        if(!$this->hasWorkingBoards()) {
            throw new PBX_Khomp_Exception_NoKhomp();
        }
        else {
            $response = $this->asterisk->Command("khomp summary concise");

            $lines = explode("\n", $response['data']);
            array_pop($lines); // Removendo a linha vazia
            array_shift($lines); // Removendo a linha "Privilege: ..."

            for($i = 2; $i < count($lines); $i++) {
                $raw_info = explode(";", $lines[$i]);
                $bid = (int) substr($raw_info[0], 4);
                $boards[$bid] = array(
                    "id"       => $bid,
                    "model"    => $raw_info[1],
                    "serial"   => $raw_info[2],
                    "channels" => (int) $raw_info[3]
                );
                if(!isset($raw_info[4]) || !ereg('E1', $raw_info[1])){
                    $boards[$bid]['links'] = 0;
                    if(ereg('KFXVoIP', $raw_info[1])) {
                        $boards[$bid]['channels'] = 8;
                    }
                }
                else {
                    $boards[$bid]["links"] = $raw_info[4];
                }
            }

            if($board_id != -1 && isset($boards[$board_id])) {
                return $boards[$board_id];
            }
            else if($board_id == -1) {
                return $boards;
            }
            else {
                throw new PBX_Khomp_Exception_NoSuchBoard("No board found in system with id $board_id");
            }
        }
    }
    
}
