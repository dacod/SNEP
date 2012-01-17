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
 * Tabela de Abstração das Expressões Regulares
 *
 * @category  lib
 * @package   Snep
 * @copyright Copyright (c) 2011 TheSource
 * @author Amim Knabben
 */
class PBX_Expression extends Zend_Db_Table_Abstract {
    protected $_name = "expression";
    protected $_primary = "id_expression";
    
    protected $_referenceMap = array(
        'PBX_ExpressionAliases' =>  array(
            'columns' => 'id_alias_expression',
            'refTableClass' => 'PBX_ExpressionAliases',
            'refColumns' => 'id_alias_expression'
        )
    );
}

/**
 * Faz o controle em banco dos Alias para expressões regulares.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_ExpressionAliases extends Zend_Db_Table_Abstract {
    protected $_name = "alias_expression";
    protected $_primary = "id_alias_expression";
    private static $instance;

    /**
     * Retorna instancia dessa classe
     *
     * @return PBX_ExpressionAliases
     */
    public static function getInstance() {
        if( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 
     * Retorna a lista das Expressões Regulares cadastradas 
     * @return Array $aliases
     */
    public function getAll() {
        $rawAliases = $this->fetchAll();
        
        $aliases = array();
        foreach ($rawAliases as $alias) {
        	$aliasId = $alias->id_alias_expression;
        	
        	$aliases[$aliasId] = array(
        	   'id' => $aliasId,
        	   'name' => $alias->ds_name,
        	   'expressions' => array()
        	);
        	
        	if (($exprs = $alias->findPBX_Expression()) != NULL) {
	            foreach ($exprs as $expr) {
	                array_push($aliases[$aliasId]['expressions'], 
	                           $expr->ds_expression);
	            }	
        	}
        }
        
        return $aliases;
    }
    
    /**
     * 
     * Retorna apenas a expressão com o id referenciado
     * @param int $id 
     * @return Array $alias
     */
    public function get($id) {
        if(!is_integer($id)) {
            throw new PBX_Exception_BadArg("Id must be numerical");
        }
        
        $rawAlias = $this->fetchRow("id_alias_expression = '$id'");
        $alias = array(
            'id' => $id,
            'name' => $rawAlias->ds_name,
            'expressions' => array()
        );

        $db = Zend_Registry::get('db');
        $select = "SELECT expression FROM expr_alias_expression WHERE aliasid='$id'";

        $stmt = $db->query($select);
        $raw_expression = $stmt->fetchAll();
        
        if (($exprs = $rawAlias->findPBX_Expression()) != NULL) {
            foreach ($exprs as $expr) {
                array_push($alias['expressions'], 
                           $expr->ds_expression);
            }   
        }

        return $alias;
    }

    /**
     * 
     * Método para cadastro de expressão regular
     * @param Array $expression
     */
    public function register($expression) {
        $db = Zend_Registry::get('db');

        $db->beginTransaction();
        $id = $this->insert(array("ds_name"=>$expression['name']));
        
        foreach ($expression['expressions'] as $expr) {
            $data = array("id_alias_expression" => $id, "ds_expression" => $expr);
            $db->insert("expression", $data);
        }

        try {
            $db->commit();
        }
        catch(Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
    }
    
    /**
     * 
     * Atualiza uma expressão cadastrada
     * @param Array $expression
     */
    public function update($expression) {
        $id = $expression['id'];

        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        $db->update("alias_expression", array("ds_name"=>$expression['name']), 
                    "id_alias_expression='$id'");
        $db->delete("expression", "id_alias_expression='$id'");
        
        foreach ($expression['expressions'] as $expr) {
            $data = array("id_alias_expression" => $id, "ds_expression" => $expr);
            $db->insert("expression", $data);
        }

        try {
            $db->commit();
        }
        catch(Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
    }
    
    /**
     * 
     * Remove uma expressão cadastrada
     * @param int $id
     */
    public function delete($id) {
        $db = Zend_Registry::get('db');
        
        $db->delete("alias_expression", "id_alias_expression='$id'");
    }
}
