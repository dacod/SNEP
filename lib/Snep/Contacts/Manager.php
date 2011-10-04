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
 * Classe to manager a Contacts.
 *
 * @see Snep_Contacts_Manager
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Contacts_Manager {

    public function __construct() {}

    /**
     * Method to get all contact
     */
    public function getAll() {

        $db = Zend_registry::get('db');

        $select = $db->select()
            ->from("contacts_names")
            ->from("contacts_group", "name as groupName")
            ->where('contacts_names.group = contacts_group.id');

        $stmt = $db->query($select);
        $allGroups = $stmt->fetchAll();

        return $allGroups;        
    }

    /**
     * Method to get a contact by id
     * @param int $id
     * @return Array
     */
    public function get($id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('contacts_names')
            ->where("contacts_names.id = ?", $id);

        $stmt = $db->query($select);
        $contacts = $stmt->fetch();

        return $contacts;
    }

    /**
     * Method to add a contact.
     * @param array $contact
     * @return int
     */
    public function add($contact) {

        $db = Zend_Registry::get('db');

        print_r($contact);

        $insert_data = array('id'       => $contact['id'],
                             'name'     => $contact['name'],
                             'address'  => $contact['address'],
                             'city'     => $contact['city'],
                             'state'    => $contact['state'],
                             'cep'      => $contact['zipcode'],
                             'phone_1'  => $contact['phone'],
                             'cell_1'   => $contact['cell'],
                             'group'    => $contact['group'] );

        $db->insert('contacts_names', $insert_data);
   
    }

    /**
     * Method to remove a contact
     * @param int $id
     */
    public function remove($id) {

            $db = Zend_Registry::get('db');

            $db->beginTransaction();
            $db->delete('contacts_names', "id = '$id'");

            try {
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }        
    }

    /**
     * Method to update a contact data
     * @param Array $data
     */
    public function edit($contact) {

        $db = Zend_Registry::get('db');

        $update_data = array('name'     => $contact['name'],
                             'address'  => $contact['address'],
                             'city'     => $contact['city'],
                             'state'    => $contact['state'],
                             'cep'      => $contact['zipcode'],
                             'phone_1'  => $contact['phone'],
                             'cell_1'   => $contact['cell'],
                             'group'    => $contact['group']);


        $db->update("contacts_names", $update_data, "id = '{$contact['id']}'");

    }

    /**
     * Method to return a last inserted id.
     * The Contacts id cannot be a auto increment field
     */
    public function getLastId() {

        $db = Zend_registry::get('db');

        $select = $db->select()
            ->from("contacts_names", array(' max( floor( id ) ) as id'))
            //->order('id DESC')
            ->limit('1');

        $stmt = $db->query($select);
        $lastId = $stmt->fetch();
        $return = $lastId['id'] + 1;

        return $return;
    }


    public function removeByGroupId($groupId) {

            $db = Zend_Registry::get('db');

            $db->beginTransaction();
            $db->delete('contacts_names', "contacts_names.group = '$groupId'");

            try {
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }

    }
}