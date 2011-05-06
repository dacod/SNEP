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
 * Classe que inspeciona o sistema
 *
 * @see Snep_Inspector
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class Snep_Inspector {

    /**
     * Se algum teste reportou erro
     *
     * @var boolean
     */
    protected $errored = false;

    // Array de registro de inspeções.
    private $inspected = array();

    /**
     * Construcao do objeto, onde a pasta /inspectors/ é percorrida e suas
     * classes pertencentes instanciadas.
     */
    public function __construct($inspect = false) {

        if(! $inspect ) {
            $config = Zend_Registry::get('config');
            $path = $config->system->path->base . "/inspectors/";
            $classes = array();

            foreach( scandir($path) as $file ) {
                if( preg_match("/.*\.php$/", $file) ) {
                    include $path ."/". $file ;
                    $class = basename($file, ".php");
                    $obj = new $class;

                    $testData = $obj->getTests();
                    $testData["name"] = $obj->getTestName();
                    $this->inspected[$class] = $testData;
                    if($testData["error"] == true) {
                        $this->errored = true;
                    }
                }
            }
        }else{

            $config = Zend_Registry::get('config');
            $path = $config->system->path->base . "/inspectors/";
            include $path ."/". $inspect .".php" ;
            $obj = new $inspect;

            $testData = $obj->getTests();
            $this->inspected[$inspect] = $testData;
            
            if($testData["error"] == true) {
                $this->errored = true;
            }

        }
        
    }

    public function errored() {
        return $this->errored;
    }
    
    /**
     * Retorna array de inspeções encontradas no construtor.
     * @return Array
     */
    public function getInspects() {
        return $this->inspected;
    }
}