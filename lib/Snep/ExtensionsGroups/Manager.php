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
 *  Class that  controls  the  persistence  in  database  of business rules
 * the Snep.
 *
 * Note about  persistence: The  persistence  control  is  done  in  the  SNEP
 * separate classes. Not in the constructor of the class model as is seen in other
 * Frameworks and architectures. The reason is that if a change in
 * how it is made ​​the persistence of these objects need not be the same
 * changed. This increases the compactness with legacy code and facilitates
 * migration of code between versions.
 * ~henrique
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class Snep_ExtensionsGroups_Manager {

    private function __construct() { /* Protegendo métodos dinâmicos */ }
    private function __destruct() { /* Protegendo métodos dinâmicos */ }
    private function __clone() { /* Protegendo métodos dinâmicos */ }


    /**
     * Return a group from the database based on their ID.
     * Retorna um grupo do banco de dados com base na sua identificação.
     *
     * @param int $id
     */

    public static function getGroup ($id) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('groups')
                ->where ("name = '$id'");

        $stmt = $db->query($select);
        $registros = $stmt->fetch();

        return $registros;
    }

    /**
     * Return all the group's database.
     * Retorna todos os grupo do banco de dados.
     */

    public static function getAllGroup() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('groups',array('name','inherit'))
                     ->where("name not in ('all','users','administrator') ");

        $stmt = $db->query($select);
        $extensionsGroup = $stmt->fetchAll();

        return $extensionsGroup;
    }

    /**
     * Returns all groups and extensions of the database based on their ID.
     * Retorna todos os grupo e suas extensões do banco de dados  com base na sua identificação.
     *
     * @param int $id
     */

    public function getExtensionsGroup($id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('peers',array('id','name','group'))
                     ->from('groups',array('name','inherit'))
                     ->where('peers.group = groups.name')
                     ->where('groups.name = ?', $id);

        $stmt = $db->query($select);
        $extensionsGroup = $stmt->fetchAll();

        return $extensionsGroup;
    }
    
    public function getExtensionsOnlyGroup($id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('peers',array('id','name','group'))
                     ->where('peers.group = ?', $id);

        $stmt = $db->query($select);
        $extensionsGroup = $stmt->fetchAll();

        return $extensionsGroup;
    }

    /**
     * Returns all groups and extensions of the database.
     * Retorna todos os grupo e suas extensões do banco de dados.
     */

    public function getExtensionsAllGroup() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('peers',array('id','name','group'))
                     ->from('groups',array('name','inherit'))
                     ->where('peers.group = groups.name');

        $stmt = $db->query($select);
        $extensionsGroup = $stmt->fetchAll();

        return $extensionsGroup;
    }
    
        public function getExtensionsAll() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('peers',array('id','name','group'));

        $stmt = $db->query($select);
        $extensionsGroup = $stmt->fetchAll();

        return $extensionsGroup;
    }
    
    /**
     * Adds the group to the database based on the value reported.
     * Adiciona o grupo no banco de dados com base no valor informado.
     *
     * @param int $group
     */

    public static function addGroup($group) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {
            $db->insert('groups', $group);
            $db->commit();
            return true;
        }
        catch(Exception $e) {
            $db->rollBack();
            return $e;
        }

    }

    /**
     * Change the group in the database based on the value reported.
     * Altera o grupo no banco de dados com base no valor informado.
     *
     * @param int $group
     */

    public static function  editGroup($group) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $value = array('name' => $group['name'] , 'inherit' => $group['type']);

            $db->update("groups" , $value, "name ='".$group['id']."'");
            $db->commit();

            return true;
        }
        catch(Exception $e) {

            $db->rollBack();
            return $e;
        }
    }

    /**
     * Adds the group their extensions in the database based on the value reported.
     * Adiciona ao grupo as suas extensões no banco de dados com base no valor informado.
     *
     * @param string $extensionsGroup
     */
    public function addExtensionsGroup($extensionsGroup) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $value = array("peers.group" => $extensionsGroup['group']);

            $db->update("peers", $value, "name = ".$extensionsGroup['extensions']);
            $db->commit();
            
            return true;
        }
        catch(Exception $e) {

            $db->rollBack();
            return $e;
        }
    }

    /**
     * Remove a group from the database based on his  ID.
     * Remover um grupo do banco de dados com base na sua identificação.
     *
     * @param int $id
     */

    public static function delete($id) {

        $db = Zend_Registry::get('db');

        $db->beginTransaction();

        try {

            $db->delete("groups","name='{$id}'");
            $db->commit();

            return true;
        }
        catch(Exception $e) {

            $db->rollBack();
            return $e;
        }
    }
}
?>
