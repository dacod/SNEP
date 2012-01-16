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
 * Classe que abstrai as tarifas
 *
 * @see Snep_Tarifas
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Cnl extends Zend_Db_Table_Abstract {

    public function __construct() {}
    public function __destruct() {}
    public function __clone() {}

    public static function fetchStateId($state) {
        $db = Zend_Registry::get('db');
        
        try {
            $stmt = $db->select()
               ->from('state')
               ->where('ds_code = ?', $state)
               ->query();
            $stateId = $stmt->fetch();
            
        } catch (Exception $ex) {

            $db->rollBack();
            throw $ex;
        }
        return $stateId['id_state'];
        
    }
    
    public static function addOperadora($id, $name) {    	
        $db = Zend_Registry::get('db');        
        $select = $db->select()->from('carrier')->where("ds_name = ? ", $name);
        $carrier = $db->query($select)->fetch();

        if( ! $carrier) {
            $db->beginTransaction();
            try {
                $carrier = array('id_carrier' => $id,
                                 'ds_name' => $name,
                                 'vl_start' => 0,
                                 'vl_fractionation' => 0,
                                 'fg_active' => 'false');
                $db->insert('carrier', $carrier);
                $db->commit();
            } catch (Exception $ex) {

                $db->rollBack();
                throw $ex;
            }
        }        
    }

    public static function addCidade($name) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $cidade = array('ds_name' => $name);
            $db->insert('city', $cidade);
            $id = $db->lastInsertId('city_id_city');
            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
        return $id;
    }
    
    
    public static function addDDD($code, $estado, $cidade) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $ddd = array('vl_code' => $code,
                         'id_state' => $estado,
                         'id_city' => $cidade);
            $db->insert('city_code', $ddd);

            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
    }

    public static function addPrefixo($prefixo,$cidade,$operadora) {//600, '23', 4

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $addPrefixo = array('vl_prefix' => $prefixo,
                                'id_city' => $cidade,
                                'id_carrier' => $operadora);
            $db->insert('carrier_prefix', $addPrefixo);

            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
    }

    public static function getCnl() {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from('cnl');

        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

        return $registros;
    }

    public static function getOperadora() {

        $db = Zend_Registry::get('db');
        $select = $db->select()
                     ->distinct('operadora')
                        ->from('cnl','operadora');

        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

        return $registros;
    }

    public function get($uf) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('ddd' => 'ars_ddd'),array('estado as uf'))
        ->join(array('cid' => 'ars_cidade'), 'cid.id = ddd.cidade' ,array('name as municipio'))
        ->where("ddd.estado = '$uf'")
        ->order("municipio");

        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

        return $registros;
    }

    public function getPrefixo($cidade) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('cid' => 'ars_cidade'),array('name as municipio'))
        ->join(array('pre' => 'ars_prefixo'), 'pre.cidade = cid.id' ,array('prefixo'))
        ->where("cid.name = '$cidade'");

        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

        return $registros;
    }

    public function getCidade($prefixo) {

		$db = Zend_Registry::get('db');

		$select = $db->select()
        ->from(array('cid' => 'ars_cidade'),array('name as municipio'))
        ->join(array('pre' => 'ars_prefixo'), 'pre.cidade = cid.id' ,array('prefixo'))
        ->join(array('ddd' => 'ars_ddd'), 'pre.cidade = ddd.cidade')
        ->where("pre.prefixo = '$prefixo'");

        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

		return $registros;


	}
}
