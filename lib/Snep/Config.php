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

require_once "Zend/Config/Ini.php";

/**
 * Classe estática para facilitar a obtenção das configurações do Snep.
 *
 * @author Henrique Grolli Bassotto
 */
abstract class Snep_Config {

    /**
     * Objeto que armazena as configurações do snep.
     *
     * @var Zend_Config
     */
    protected static $config;

    /**
     * Retorna o objeto que armazena as configurações do snep.
     *
     * @return Zend_Config $config
     */
    public static function getConfig() {
        if(self::$config === null) {
            self::setConfigFile("/etc/snep.conf");
        }

        return self::$config;
    }

    /**
     * Instancia um novo objeto de configurações para o snep a partir de um
     * caminho para um  arquivo .ini
     */
    public static function setConfigFile($file) {
        if (file_exists($file)) {
            $config = new Zend_Config_Ini($file);
            self::$config = $config;
        } else {
            throw new Exception("Fatal Error: configuration file not found: $file");
        }
    }

}
