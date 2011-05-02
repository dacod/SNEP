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
class Snep_PickupGroups_Manager {

    private function __construct() { /* Protegendo métodos dinâmicos */ }
    private function __destruct() { /* Protegendo métodos dinâmicos */ }
    private function __clone() { /* Protegendo métodos dinâmicos */ }

    /**
     * Remove a pickup group from the database based on his  ID.
     *
     * @param int $id
     */
    public static function delete($id) {

        $db = Zend_Registry::get('db');
        $db->delete("grupos","cod_grupo='{$id}'");
    }

    /**
     * Return a pickup group from the database based on his  ID.
     *
     * @param int $id
     */
    public static function get ($id) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('grupos')
                ->where ("cod_grupo = '$id'");

        $stmt = $db->query($select);
        $registros = $stmt->fetch();

        return $registros;
    }

    public static function getAll() {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('grupos');

        $stmt = $db->query ($select);
        $row = $stmt->fetchAll();

        $pickupGroups = array();
        foreach ($row as $pickupGroup) {
            $pickupGroups[$pickupGroup['cod_grupo']] = $pickupGroup['nome'];
        }
        return $pickupGroups;
    }

    public static function getFilter ($field, $query) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('grupos');

        if (!is_null($query)) {
                $select->where ("$field like '%$query%'");
        }

        return $select;
    }

    public static function add($pickupGroup) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {
            $db->insert('grupos', $pickupGroup);
            $db->commit();
            return true;
        }
        catch(Exception $e) {
            $db->rollBack();
            return $e;
        }
    }

    public static function  edit($pickupGroup) {

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
