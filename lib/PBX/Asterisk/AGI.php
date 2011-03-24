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

require_once("Asterisk/AGI.php");

/**
 * AGI singleton class.
 *
 * @category  PBX
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_AGI extends Asterisk_AGI {

    /**
     * Singleton instance.
     *
     * @return PBX_Asterisk_AGI
     */
    protected static $instance;

    /**
     * Returns the singleton instance of this class.
     *
     * @return PBX_Asterisk_AGI
     */
    public static function getInstance() {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        parent::__construct(null, array("debug"=>false, "error_handler"=>false));
    }

    protected function __clone() {}
}
