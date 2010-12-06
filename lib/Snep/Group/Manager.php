<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contacts
 *
 * @author elton
 */
class Snep_Group_Manager {

    /*
     * Manutenção registro referente group
     */

    // retorna todos group
    public static function getAll() {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('ad_group');

        $stmt = $db->query ($select);
        $row = $stmt->fetchAll();
        
        $groups = array();
        foreach ($row as $group) {
            $groups[$group['id']] = $group['name'];
        }
        return $groups;
    }

    // retorna campanha
    public static function get ($id) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('ad_group')
                ->where ("id = '$id'");

        $stmt = $db->query($select);
        $registros = $stmt->fetch();

        return $registros;
    }

    // retorna group - filtro
    public static function getFilter ($field, $query) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from ('ad_group');

        if (!is_null($query)) {
                $select->where ("$field like '%$query%'");
        }

        return $select;
    }

    // insere group
    public static function add($group) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {
            $db->insert('ad_group', $group);
            $db->commit();
            return true;
        }
        catch(Exception $e) {
            $db->rollBack();
            return $e;
        }
    }

    // edita group
    public static function  edit($group) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        $value = array ('name' => $group['name']);
        
        try {

            $db->update ('ad_group', $value, 'id ='.$group['id']);
            $db->commit();
            return true;
        }
        catch(Exception $e) {

            $db->rollBack();
            return $e;
        }
    }

    // exlui um group
    public static function del($group) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {
            $db->delete('ad_group', "id = $group");
            $db->commit();
            return true;
        }
        catch(Exception $e) {
            $db->rollBack();
            return $e;
        }
    }
}