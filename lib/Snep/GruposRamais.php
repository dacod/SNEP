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
 * Classe que abstrai os Grupos de Ramais
 *
 * @see Snep_GruposRamais
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class Snep_GruposRamais {

    public function __construct() {}
    public function __destruct() {}
    public function __clone() {}

    public function __get($atributo) {
        return $this->{$atributo};
    }

    public function getAll() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('groups');
        
        $stmt = $db->query($select);
        $grupos = $stmt->fetchAll();

        return $grupos;
    }

    public function getRamalGrupos() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('peers', array('peer_type', 'group', 'name'))
        ->from('groups', array('inherit'))
        ->where("peers.peer_type='R'")
        ->where("`peers.group`=`groups.inherit`");

        $stmt = $db->query($select);
        $ramais_grupos = $stmt->fetchAll();

        return $ramais_grupos;
    }

}
