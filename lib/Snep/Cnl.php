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
 * Classe que abstrai as tarifas
 *
 * @see Snep_Tarifas
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Cnl {

    public function __construct() {}
    public function __destruct() {}
    public function __clone() {}

    public function get($uf) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->distinct('municipio')
        ->from('cnl', 'municipio')
        ->where("uf = '$uf'")
        ->order('municipio');

        $stmt = $db->query($select);
        $municipios = $stmt->fetchAll();

        return $municipios;
    }

    public function getPrefixo($cidade) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->distinct('prefixo')
        ->from('cnl', 'prefixo')
        ->where("municipio = '$cidade'")
        ->order('prefixo');

        $stmt = $db->query($select);
        $prefixo = $stmt->fetchColumn();

        return substr($prefixo, 0, 2);
    }

}
?>