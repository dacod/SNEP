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
 * Faz o controle em banco dos Alias para expressões regulares.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_ExpressionAliases {

    private static $instance;

    protected function __construct() {}
    protected function __clone() {}

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

    public function getAll() {
        $db = Zend_Registry::get('db');
        $select = "SELECT aliasid, name FROM expr_alias";

        $stmt = $db->query($select);
        $raw_aliases = $stmt->fetchAll();

        $aliases = array();
        foreach ($raw_aliases as $alias) {
            $aliases[$alias['aliasid']] = array(
                "id" => $alias['aliasid'],
                "name" => $alias['name'],
                "expressions" => array()
            );
        }

        $db = Zend_Registry::get('db');
        $select = "SELECT aliasid, expression FROM expr_alias_expression";

        $stmt = $db->query($select);
        $raw_expressions = $stmt->fetchAll();

        foreach ($raw_expressions as $expr) {
            $aliases[$expr["aliasid"]]["expressions"][] = $expr['expression'];
        }

        return $aliases;
    }

    public function get( $id ) {
        if(!is_integer($id)) {
            throw new PBX_Exception_BadArg("Id must be numerical");
        }
        
        $db = Zend_Registry::get('db');
        $select = "SELECT name FROM expr_alias WHERE aliasid='$id'";

        $stmt = $db->query($select);
        $raw_alias = $stmt->fetchObject();
        $alias = array(
            "id" => $id,
            "name" => $raw_alias->name,
            "expressions" => array()
        );

        $db = Zend_Registry::get('db');
        $select = "SELECT expression FROM expr_alias_expression WHERE aliasid='$id'";

        $stmt = $db->query($select);
        $raw_expression = $stmt->fetchAll();
        
        foreach ($raw_expression as $expr) {
            $alias["expressions"][] = $expr['expression'];
        }

        return $alias;
    }

    public function register($expression) {
        $db = Zend_Registry::get('db');

        $db->beginTransaction();
        $db->insert("expr_alias", array("name"=>$expression['name']));
        $id = $db->lastInsertId();

        foreach ($expression['expressions'] as $expr) {
            $data = array("aliasid" => $id, "expression" => $expr);
            $db->insert("expr_alias_expression", $data);
        }

        try {
            $db->commit();
        }
        catch(Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
    }

    public function update($expression) {
        $id = $expression['id'];

        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        $db->update("expr_alias", array("name"=>$expression['name']), "aliasid='$id'");
        $db->delete("expr_alias_expression","aliasid='$id'");

        foreach ($expression['expressions'] as $expr) {
            $data = array("aliasid" => $id, "expression" => $expr);
            $db->insert("expr_alias_expression", $data);
        }

        try {
            $db->commit();
        }
        catch(Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
    }

    public function delete($id) {
        $db = Zend_Registry::get('db');

        $db->delete("expr_alias", "aliasid='$id'");
    }
}
