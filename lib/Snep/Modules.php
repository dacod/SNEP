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
require_once "Snep/Module/Descriptor.php";
require_once "Snep/Acl.php";
require_once "Snep/Menu.php";

/**
 * Controle de módulos instalados no sistema.
 *
 *
 * @category  Snep
 * @package   Snep_Module
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Modules {

    /**
     * Módulos registrados no sistema.
     *
     * @var array
     */
    protected $registeredModules = array();
    protected static $instance;

    protected $path = array();

    protected function __construct() { /* Singleton */ }
    protected function __clone() { /* Singleton */ }

    /**
     * Retorna a instancia dessa classe com os módulos registrados
     *
     * @return Snep_Modules instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna todos os modulos registrados no sistema
     *
     * @return Snep_Module_Descriptor[]
     */
    public function getRegisteredModules() {
        return $this->registeredModules;
    }

    public function addPath($path) {
        if (file_exists($path) && is_dir($path)) {
            $this->path[] = $path;
            foreach (scandir($path) as $file) {
                if( is_dir($file) && file_exists($path . "/$file/info.xml") ) {
                    $this->registerModule("$path/$file");
                }
            }
        }
        else {
            throw new PBX_Exception_IO("'$path' is not a valid path");
        }
    }

    /**
     * Parses the file info.xml to a module descriptor object.
     *
     * @param SimpleXMLElement $info
     * @return Snep_Module_Descriptor
     */
    protected function parseInfo($id, SimpleXMLElement $info) {
        $descriptor = new Snep_Module_Descriptor($id);

        if(isset($info->name)) {
            $descriptor->setName((string)$info->name);
        }
        if(isset($info->version)) {
            $descriptor->setVersion((string)$info->version);
        }
        if(isset($info->description)) {
            $descriptor->setDescription((string)$info->description);
        }
        if(isset($info->author)) {
            $descriptor->setAuthor((string)$info->author);
        }
        if(isset($info->website)) {
            $descriptor->setWebsite((string)$info->website);
        }

        return $descriptor;
    }

    /**
     * Parses recursively the resources xml into Snep_Acl
     *
     * @param SimpleXMLElement $resources
     */
    protected function loadResources(SimpleXMLElement $resources, $parent = null, $menu_parent = null) {
        $acl = Snep_Acl::getInstance();
        $menu = Snep_Menu::getMasterInstance();
        $baseUrl = Snep_Config::getConfig()->system->path->web;
        $menu_parent = $menu_parent === null ? $parent : $menu_parent;

        foreach ($resources as $element) {
            $id = (string) $element->attributes()->id;

            if($id === null) {
                throw new Exception("Resource object must contain id attribute.");
            }

            if($element->getName() == "resource") {
                $resname = $parent . "_" . $id;
                $acl->addResource($resname, $parent);
            }
            else {
                $resname = $parent;
            }

            if($element->attributes()->label !== null) {
                $menu_id = $menu_parent . "_" . $id;

                $menu_object = new Snep_Menu($menu_id);
                $menu_object->setLabel((string) $element->attributes()->label);
                $menu_object->setUri($baseUrl . "/index.php/" . str_replace("_", "/", $resname));

                $menu_parent_obj = $menu->getChildById($menu_parent);
                if($menu_parent_obj === null) {
                    $menu_parent_obj = $menu;
                }
                $menu_parent_obj->addChild($menu_object);
            }
            else {
                $menu_id = $menu_parent;
            }

            if($element->count() > 0) {
                $this->loadResources($element, $resname, $menu_id);
            }
        }
    }

    /**
     * Take care of the details of module registering.
     *
     * @param Snep_Module_Descriptor $module
     */
    protected function registerModule($path) {
        $info = simplexml_load_file($path . "/info.xml");
        $pathinfo = pathinfo($path);
        $descriptor = $this->parseInfo($pathinfo['filename'], $info);
        $this->registeredModules[] = $descriptor;

        // Adding module lib to include path
        if(is_dir($path . "/lib")) {
            set_include_path(implode(PATH_SEPARATOR, array("$path/lib", get_include_path())));
        }

        if(file_exists("$path/resources.xml")) {
            Snep_Acl::getInstance()->addResource($descriptor->getModuleId());
            $this->loadResources(simplexml_load_file("$path/resources.xml"), $descriptor->getModuleId());
        }
    }

}
