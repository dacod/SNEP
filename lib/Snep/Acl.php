<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once "Zend/Acl.php";

/**
 * Snep Acl Manager
 *
 * @author Henrique Grolli Bassotto <henrique@opens.com.br>
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 */
class Snep_Acl extends Zend_Acl {

    /**
     * Singleton instance of this class.
     *
     * @var Snep_Acl
     */
    protected static $instance;

    protected function  __clone() { /*Singleton*/ }

    /**
     * Class constructor and base resources inicialization.
     */
    protected function __construct() {
        $this->addRole("guest");
        $this->add(new Zend_Acl_Resource('unknown'));
        $this->deny("guest", "unknown");
    }

    /**
     * Returns the single instance of this class.
     *
     * @return Snep_Acl
     */
    public static function getInstance() {
        if( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
