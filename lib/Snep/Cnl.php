<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
class Snep_Cnl {

    public static function delPrefixo() {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $db->delete('ars_prefixo');
            $db->commit();

        } catch (Exception $ex) {

            $db->rollBack();
                throw $ex;
        }
        return;
    }

    public static function delCidade() {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $db->delete('ars_cidade');
            $db->commit();

        } catch (Exception $ex) {

            $db->rollBack();
                throw $ex;
        }
        return;
    }

    public static function delDDD() {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $db->delete('ars_ddd');
            $db->commit();

        } catch (Exception $ex) {

            $db->rollBack();
                throw $ex;
        }
        return;
    }

    public static function delOperadora() {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $db->delete('ars_operadora');
            $db->commit();

        } catch (Exception $ex) {

            $db->rollBack();
                throw $ex;
        }
        return;
    }
    
    public static function addOperadora($id,$data) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $operadora = array('id' => $id , 'name' => $data);
            $db->insert('ars_operadora', $operadora);
            $db->commit();

        } catch (Exception $ex) {

            $db->rollBack();
            throw $ex;
        }
    }

    public static function addDDD($cod,$estado,$cidade) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $ddd = array('cod' => $cod,'estado' => $estado,'cidade' => $cidade);
            $db->insert('ars_ddd', $ddd);

            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
    }

    public static function addCidade($name) {

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $cidade = array('name' => $name);
            $db->insert('ars_cidade', $cidade);
            $id = $db->lastInsertId();

            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
        return $id;
    }

    public static function addPrefixo($prefixo,$cidade,$operadora) {//600, '23', 4

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            $addPrefixo = array('prefixo' => $prefixo,'cidade' => $cidade,'operadora' => $operadora);
            $db->insert('ars_prefixo', $addPrefixo);

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
                        ->from('ars_operadora');

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

    public static function getCarrierByPrefix($prefix) {
        $db = Zend_Registry::get('db');
        $sql = sprintf("select op.id, op.name from ars_prefixo p inner join ars_operadora op on p.operadora = op.id where p.prefixo = '%s'", $prefix);
        $stmt = $db->query($sql);
        $data = $stmt->fetch();
        $stmt->fetchAll();
        return $data;
    }
}
