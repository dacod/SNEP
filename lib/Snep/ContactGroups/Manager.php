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
 * Classe to manager a Contact Groups.
 *
 * @see Snep_ContactGroups_Manager
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_ContactGroups_Manager {

    public function __construct() {}

    /**
     * Method to get all contact groups
     */
    public function getAll() {

        $db = Zend_registry::get('db');

        $select = $db->select()
            ->from("contacts_group");

        $stmt = $db->query($select);
        $allGroups = $stmt->fetchAll();

        return $allGroups;        
    }

    /**
     * Method to get a contact group by id
     * @param int $id
     * @return Array
     */
    public function get($id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('contacts_group', array('id', 'name'))
            ->where("contacts_group.id = ?", $id);

        $stmt = $db->query($select);
        $contactGroup = $stmt->fetch();

        return $contactGroup;
    }

    /**
     * Method to add a contact group.
     * @param array $group
     * @return int
     */
    public function add($group) {

        $db = Zend_Registry::get('db');

        $insert_data = array("name" => $group['group']);
        $db->insert('contacts_group', $insert_data);

        return $db->lastInsertId();        
    }

    /**
     * Method to remove a contact group
     * @param int $id
     */
    public function remove($id) {

        $db = Zend_Registry::get('db');

        $db->beginTransaction();
        $db->delete('contacts_group', "id = '$id'");

        try {
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }
    }

    /**
     * Method to update a contact group data
     * @param int $id
     */
    public function edit($group) {

        $db = Zend_Registry::get('db');

        $update_data = array('name'     => $group['group']);
        $db->update("contacts_group", $update_data, "id = '{$group['id']}'");
        
    }

    /**
     * Method do insert contact on group
     * @param int $groupId
     * @param int $contactId
     */
    public function insertContactOnGroup($groupId, $contactId) {

        $db = Zend_Registry::get('db');
        
        $update_data = array("group" => $groupId);

        $db->update("contacts_names", $update_data, "id = '{$contactId}'");
        
    }
    
    /**
     * Method do return all contacts by group id
     * @param int $id
     */
    public function getGroupContacts($id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('contacts_names', array('id', 'name'))
            ->from('contacts_group', array('name as group', 'id as idGroup'))
            ->where("contacts_group.id = ?", $id)
                ->where("contacts_group.id = contacts_names.group");
            

        $stmt = $db->query($select);
        $contactGroup = $stmt->fetchAll();

        return $contactGroup;
    }
}