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
 * Classe to manager a Billing.
 *
 * @see Snep_Billing_Manager
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Billing_Manager {

    public function __construct() {}

    /**
     * Get all Billing
     */
    public function getAll() {

        $db = Zend_registry::get('db');

        $select = $db->select()
                        ->from("tarifas_valores", array('DATE_FORMAT(data,\'%d/%m/%Y %T\') as data', 'vcel', 'vfix'))
                        ->from("tarifas")
                        ->from("operadoras", array('nome'))
                        ->where("operadoras.codigo = tarifas.operadora")
                        ->where("tarifas_valores.codigo = tarifas.codigo");
            
        $stmt = $db->query($select);
        $billing = $stmt->fetchAll();

        return $billing;
        
    }

    /**
     * Get a billing by id
     * @param int $id
     * @return Array
     */
    public function get($id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('tarifas')
            ->where("tarifas.codigo = ?", $id);

        $stmt = $db->query($select);
        $billing = $stmt->fetch();

        return $billing;

    }

    /**
     * Add a Billing.
     * @param array $billing
     * @return int
     */
    public function add($billing) {

        $db = Zend_Registry::get('db');

        $insert_data = array('operadora' => $billing['carrier'],
                             'ddi'       => $billing['country_code'],
                             'pais'      => $billing['country'],
                             'ddd'       => $billing['city_code'],
                             'cidade'    => $billing['city'],
                             'estado'    => $billing['state'],
                             'prefixo'   => $billing['prefix'] );

        $db->insert('tarifas', $insert_data);

        $idBilling = $db->lastInsertId();

        $insert_data = array('codigo'    => $idBilling,
                             'data'      => new Zend_Db_Expr('NOW()'),
                             'vcel'      => $billing['tbc'],
                             'vfix'      => $billing['tbf']);

        $db->insert('tarifas_valores', $insert_data);        
   
    }

    /**
     * Add a billing item
     * @param <type> $idBilling
     * @param <type> $values
     */
    public function addBilling($idBilling, $values) {

        $db = Zend_Registry::get('db');

        $insert_data = array('codigo' => $idBilling,
                             'data'   => new Zend_Db_Expr('NOW()'), /*$values['data']*/
                             'vcel'   => $values['vcel'],
                             'vfix'   => $values['vfix'] );

        $db->insert('tarifas_valores', $insert_data);
    }

    /**
     * Update a billing item values
     * @param <type> $idBilling
     * @param <type> $values
     */
    public function editBilling($idBilling, $values) {

        $db = Zend_Registry::get('db');

        $update_data = array('codigo' => $idBilling,
                             'data'   => $values['data'],
                             'vcel'   => $values['vcel'],
                             'vfix'   => $values['vfix'] );

        $db->update('tarifas_valores', $update_data, "tarifas_valores.data =  '{$values['data']}' and tarifas_valores.codigo = '$idBilling'" );
        
    }

    /**
     * Remove a billing and all bill tax.
     * @param int $id
     */
    public function remove($id) {

            $db = Zend_Registry::get('db');

            $db->beginTransaction();
            $db->delete('tarifas_valores', "codigo = '$id'");
            
            try {
                $db->commit();

            } catch (Exception $e) {
                $db->rollBack();
                
            }
            
            $db->beginTransaction();
            $db->delete('tarifas', "codigo = '$id'");
            
            try {
                $db->commit();

            } catch (Exception $e) {
                $db->rollBack();
                
            }


    }

    /**
     * Get all billing values
     * @param int $idCarrier
     * @param int $costCenter
     */
    public function getBillingValues($idBilling) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('tarifas_valores')
            ->where("tarifas_valores.codigo = ?", $idBilling);

        $stmt = $db->query($select);
        $billingValues = $stmt->fetchAll();

        return $billingValues;

    }

    /**
     * Return all States
     * @return Array
     */
     public function getStates() {

        $db = Zend_registry::get('db');

        $select = $db->select()
            ->from("ars_estado", array('cod', 'name'));

        $stmt = $db->query($select);
        $states = $stmt->fetchAll();

        return $states;         

     }

    /**
     * Return all cities by state
     * @return Array
     */
     public function getCity($state) {

        $db = Zend_registry::get('db');

        $select = $db->select()
            ->from("ars_cidade", array('name'))
            ->from("ars_ddd", array('estado'))
            ->where("ars_ddd.cidade = ars_cidade.id")
            ->where("ars_ddd.estado = ?", $state)
            ->order("ars_cidade.name");

        $stmt = $db->query($select);
        $cities = $stmt->fetchAll();

        return $cities;

     }
}
