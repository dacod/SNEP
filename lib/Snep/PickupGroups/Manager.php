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
 *  Class that  controls  the  persistence  of pickup groups.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class Snep_PickupGroups_Manager extends Zend_Db_Table_Abstract {

	protected $_name = "pickup_group";
	protected $_primary = "id_pickupgroup";
	
    private function __clone() { /* Protegendo métodos dinâmicos */ }

    public function getAll() {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('pickup_group');

        $stmt = $db->query ($select);
        $row = $stmt->fetchAll();

        $pickupGroups = array();
        foreach ($row as $pickupGroup) {
            $pickupGroups[$pickupGroup['cod_grupo']] = $pickupGroup['nome'];
        }
        return $pickupGroups;
    }

    public function getFilter ($field, $query) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('grupos');

        if (!is_null($query)) {
                $select->where ("$field like '%$query%'");
        }

        return $select;
    }

    public function edit($pickupGroup) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        $value = array ('nome' => $pickupGroup['name']);

        try {

            $db->update ('grupos', $value, 'cod_grupo ='.$pickupGroup['id']);
            $db->commit();
            return true;
        }
        catch(Exception $e) {

            $db->rollBack();
            throw $e;
        }
    }
}
