<?php

/**
 * Manutenção registro referente contatos
 *
 * @author elton
 */
class Snep_Contact_Manager {

    // retorna todos contatos
    public static function getAll() {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from(array('c' => 'ad_contact'), array('id as idCont', 'name as nameCont'))
                        ->join(array('p' => 'ad_phone'), 'p.contact = c.id')
                        ->join(array('gc' => 'ad_group_contact'), 'gc.contact = c.id ')
                        ->join(array('g' => 'ad_group'), 'g.id = gc.group');


        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

        return $registros;
    }

    // retorna contato
    public static function get($id) {

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

    // retorna contacts - filtro
    public static function getFilter($field, $query) {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from(array('c' => 'ad_contact'), array('id as idCont', 'name as nameCont'))
                        ->join(array('p' => 'ad_phone'), 'p.contact = c.id')
                        ->join(array('gc' => 'ad_group_contact'), 'gc.contact = c.id ')
                        ->join(array('g' => 'ad_group'), 'g.id = gc.group');

        if (!is_null($query)) {
            $select->where("$field like '%$query%'");
        }

        return $select;
    }

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

    /**
     * Insere um contato no banco de dados
     *
     * @param array $contacts
     */
    public static function add($contacts) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();
		
        try {
        	
            $addContact = array(
                'name' => $contacts['nameCont'],
            );
            $db->insert('ad_contact', $addContact);
            $idContact = $db->lastInsertId();

            $addPhone = array('contact' => $idContact, 'phone' => $contacts['phone'], 'priority' => '');
            $db->insert('ad_phone', $addPhone);
            $idPhon = $db->lastInsertId();

            $addContactGroup = array('group' => $contacts['group'], 'contact' => $idContact);
            $db->insert('ad_group_contact', $addContactGroup);
            
            while (list($key, $val) = each($contacts)) {
            	if (gettype($key) != 'string' ) {
	            	$db->insert('ad_contact_field_value', array ('field' => $key, 
                                                                     'contact' => $idContact,
                                                                     'value' => $val));
            	}
            }
            
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

        try {
            $addField = array(
                'name' => $fields['name'],
                'type' => $fields['type'],
            	'required' => $fields['required']
            );
            
            $db->insert('ad_contact_field', $addField);
            $idField = $db->lastInsertId();
            
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

        $valueCont = array(
            'name' => $contact['nameCont'],
        );
        $valuePhon = array('phone' => $contact['phone']);
        $valueContGrou = array(
            'contact' => $contact['id'],
            'group' => $contact['group']
        );

        $db->update('ad_contact', $valueCont, "id='{$contact['id']}'");
        $db->update('ad_group_contact', $valueContGrou, "contact='{$contact['id']}'");
        $db->update('ad_phone', $valuePhon, "contact='{$contact['id']}'");

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

    // exclui campo
    public static function del($contact) {
        
        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {
            $db->delete('ad_contact_field_value', "contact = $contact");
            $db->delete('ad_group_contact', "contact = $contact");
            $db->delete('ad_phone', "contact = $contact");
            $db->delete('ad_contact', "id = $contact");
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return $e;
        }
    }

}

