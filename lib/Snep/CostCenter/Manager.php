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
 * Classe to manager a Cost Centers.
 *
 * @see Snep_CostCenter_Manager
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_CostCenter_Manager extends Zend_Db_Table_Abstract {

    protected $_name = 'cost_center';
    protected $_primary = array('id_costcenter');
    
    protected $_referenceMap = array(
        'Snep_Carrier_Manager' =>  array(
            'columns' => 'id_carrier',
            'refTableClass' => 'Snep_Carrier_Manager',
            'refColumns' => 'id_carrier'
        )
    );
}