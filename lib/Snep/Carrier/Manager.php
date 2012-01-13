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
 * Classe to manager a Carrier.
 *
 * @see Snep_Carrier_Manager
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Carrier_Manager extends Zend_Db_Table_Abstract {
    protected $_name = "carrier";
    
    /**
     * 
     * Atualiza lista de operadoras e centros de custos relacionados
     * @param int $carrierId
     * @param Array $costCentersId
     */
    public function save($carrierId, $dados) {
        $selected = $this->fetchRow($this->select()
                                   ->where('id_carrier = ?', $carrierId));

        $selected->vl_start         = $dados['ta'];
        $selected->vl_fractionation = $dados['tf'];
        $selected->fg_active        = $dados['active'];
                    
        $selected->save();
        
        // Busca e limpa centros de custos relacionados
        $costcenters = $selected->findSnep_CostCenter_Manager();
        if (count($costcenters) > 0) {
            foreach ($costcenters as $cc) {
        	   $cc->id_carrier = null;
        	   $cc->save();
            }
        }
        
        // Cadastra os Centro de Custos selecionados
        foreach($dados['box_add'] as $costcenterId) {
        	$cs = new Snep_CostCenter_Manager();   
            $csRow = $cs->fetchRow($cs
                        ->select()
                        ->where('id_costcenter = ?', $costcenterId));
                                                    
            $csRow->id_carrier = $carrierId;
            $csRow->save();
        }
    }


    /**
     * Return Carrier Cost Center's
     * @param int $idCarrier
     * @return Array $_used
     */
    public function getCarrierCostCenter($idCarrier) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('ccustos', array('codigo', 'tipo', 'nome'))
            ->from('oper_ccustos', array())
            ->where('ccustos.codigo = oper_ccustos.ccustos')
            ->where('oper_ccustos.operadora = ?', $idCarrier);

        $stmt = $db->query($select);
        $_used = $stmt->fetchAll();

        return $_used;

        $usedCostCenter = array();
        foreach($_used as $used) {
            $usedCostCenter[] = $used['ccustos'];
        }        
        
    }

    /**
     * 
     * Return all idle Cost Centers
     * @return Array
     */
    public function getIdleCostCenter() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                     ->from('cost_center')
                     ->where('id_carrier is null');

        $stmt = $db->query($select);
        $_used = $stmt->fetchAll();
        
        return $_used;

    }
}
