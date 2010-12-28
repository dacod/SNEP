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
 * Manutenção registro referente contatos
 *
 * @see Snep_Contact_Manager
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Elton Santana <elton@opens.com.br>
 *
 */

class Snep_Contact_Manager {

    /*
     *  retorna todos contatos
     */
    public static function getAll() {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from(array('c' => 'ad_contact'), array('id as idCont', 'name as nameCont'))
                        //->join(array('p' => 'ad_phone'), 'p.contact = c.id')
                        ->join(array('gc' => 'ad_group_contact'), 'gc.contact = c.id ')
                        ->join(array('g' => 'ad_group'), 'g.id = gc.group');


        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

        return $registros;
    }

    /*
     *  retorna contato
     */
    public static function get($id) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from(array('c' => 'ad_contact'), array('id as idCont', 'name as nameCont'))                        
                        ->join(array('gc' => 'ad_group_contact'), 'gc.contact = c.id ')
                        ->join(array('g' => 'ad_group'), 'g.id = gc.group')
                        ->where('c.id = ?', $id);

        $stmt = $db->query($select);
        $registros = $stmt->fetch();
        
        return $registros;
    }

    /*
     *  retorna contacts - filtro
     */
    public static function getFilter($field, $query) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from(array('c' => 'ad_contact'), array('id as idCont', 'name as nameCont'))
                        
                        ->join(array('gc' => 'ad_group_contact'), 'gc.contact = c.id ')
                        ->join(array('g' => 'ad_group'), 'g.id = gc.group');

        if (!is_null($query)) {
            $select->where("$field like '%$query%'");
        }

        return $select;
    }
/*
 *  Classe Nao utilizada, eu acho
    public static function getWithFields($id) {
        
        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from(array('c' => 'ad_contact'), array('id as idCont', 'name as nameCont'))
                        ->join(array('p' => 'ad_phone'), 'p.contact = c.id')
                        ->join(array('gc' => 'ad_group_contact'), 'gc.contact = c.id ')
                        ->join(array('g' => 'ad_group'), 'g.id = gc.group')
                        ->where('c.id = ?', $id);

        $stmt = $db->query($select);
        $registros = $stmt->fetch();

        return $registros; 

    }
*/


    /***
     *  Retorna telefones de contato conforme id, ordenado pela proridade.
     *  @param int $id
     */
    public function getPhones($id) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from(array('p' => 'ad_phone'), array('phone'))
                        ->where('p.contact = ?', $id)
                        ->order('p.priority');

        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

        $phones = array();
        foreach($registros as $registro) {
            $phones[] = $registro['phone'];
        }

        return $phones;

    }

    /**
     * Insere um contato no banco de dados
     *
     * @param array $contacts
     */
    public static function add($contacts) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        	
        $addContact = array(
            'name' => $contacts['nameCont'],
        );
        $db->insert('ad_contact', $addContact);
        $idContact = $db->lastInsertId();

        foreach($contacts['phones'] as $k => $phone) {
            $addPhone = array('contact' => $idContact, 'phone' => $phone, 'priority' => $k);
            $db->insert('ad_phone', $addPhone);
        }
        unset($contacts['phones']);

        $addContactGroup = array('group' => $contacts['group'], 'contact' => $idContact);
        $db->insert('ad_group_contact', $addContactGroup);

        while (list($key, $val) = each($contacts)) {
            if ( ! is_string($key) ) {
                    $db->insert('ad_contact_field_value', array ('field' => $key,
                                                                 'contact' => $idContact,
                                                                 'value' => $val));
            }
        }

        try {
            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
            
        }
    }
    
    /**
     * Insere um campo no banco de dados
     *
     * @param array $fields
     */
    public static function add_field($fields) {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        $addField = array(
            'name' => $fields['name'],
            'type' => $fields['type'],
            'required' => $fields['required']
        );

        $db->insert('ad_contact_field', $addField);
        $idField = $db->lastInsertId();

        try {
            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            throw $ex;

        }
    }
    
    /**
     * Atualiza informações de contato no banco de dados.
     *
     * @param array $contact
     */
    public static function edit($contact) {

        $db = Zend_Registry::get('db');

        $db->beginTransaction();
        $db->delete('ad_phone', "contact = {$contact['id']}");
        $db->commit();

        $db->beginTransaction();

        $valueCont = array(
            'name' => $contact['nameCont'],
        );
        
        $valueContGrou = array(
            'contact' => $contact['id'],
            'group' => $contact['group']
        );

        foreach($contact['phones'] as $k => $phone) {
            $addPhone = array('contact' => $contact['id'], 'phone' => $phone, 'priority' => $k);
            $db->insert('ad_phone', $addPhone);
        }

        unset($contact['phones']);

        $db->update('ad_contact', $valueCont, "id='{$contact['id']}'");
        $db->update('ad_group_contact', $valueContGrou, "contact='{$contact['id']}'");
        
		while (list($key, $val) = each($contact)) {
        	if (gettype($key) != 'string' ) {
        	   $value = array('value' => $val);
        	   
        	   // Caso ja existe atualiza, senão insere novo
        	   $select = $db->select()
        	   			  ->from(array('ad_contact_field_value'))
        	   			  ->where("contact = ?", $contact['id'])
        	   	 		  ->where("field = ?", $key);
        	   $stmt = $db->query($select);
			   $registros = $stmt->fetch();
				
        	   if ($registros) {
        	   		$db->update('ad_contact_field_value', $value, "contact={$contact['id']} 
	    	   					AND field={$key}");	            			
        	   	} else {
        	   		$db->insert('ad_contact_field_value', array('field' => $key, 
            											 'contact' => $contact['id'],
           												 'value' => $val));
        	   	}
            }
		}
        
        try {
            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
            
        }
    }

    /*
     *  exclui campo
     *  param int $contact
     */
    public static function del($contact) {
        
        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        $db->delete('ad_contact_field_value', "contact = $contact");
        $db->delete('ad_group_contact', "contact = $contact");
        $db->delete('ad_phone', "contact = $contact");
        $db->delete('ad_contact', "id = $contact");

        try {
            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollBack();
            return $e;
            
        }
    }

}

