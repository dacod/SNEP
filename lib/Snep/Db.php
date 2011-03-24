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

require_once "Zend/Db.php";
require_once "Snep/Config.php";

/**
 * Classe singleton para controle da interface com banco de dados do Snep.
 *
 * @author Henrique Grolli Bassotto
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 */
class Snep_Db extends Zend_Db {

    /**
     * Instância da classe Zend_Db_Adapter_Abstract
     *
     * @var Zend_Db_Adapter_Abstract instancia do banco de dados
     */
    protected static $instance;

    /**
     * Protegendo metodos dinâmicos
     */
    protected function __construct() {}

    protected function __clone() {}

    protected function __destruct() {}

    /**
     * Retorna uma instância já existente ou nova instância do banco de dados.
     *
     * @return Zend_Db_Adapter_Abstract $instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            $config = Snep_Config::getConfig()->ambiente->db->toArray();
            $config["driver_options"] = array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
            self::$instance = self::factory('Pdo_Mysql', $config);
            require_once("Zend/Registry.php");
            Zend_Registry::set("db", self::$instance);
        }
        return self::$instance;
    }

}
