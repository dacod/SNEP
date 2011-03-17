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
 * Module descriptor to keep track of registered modules on Snep system.
 *
 * You can use this class attributes as keys to make your info.xml
 *
 * @category  Snep
 * @package   Snep_Module
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Module_Descriptor {

    /**
     * Module id
     *
     * This is the same name as the directory that contains it.
     * Should be unique for all modules.
     *
     * @var string
     */
    protected $id = null;

    /**
     * Module name
     */
    protected $name = "Unamed Module";

    /**
     * Module version string/number
     *
     * @var string
     */
    protected $version = "";

    /**
     * Module description text.
     *
     * Here is where you sell your fish :)
     *
     * @var string
     */
    protected $description = "";

    /**
     * Module website or relevant url.
     *
     * @var string
     */
    protected $website;

    /**
     * Module author
     *
     * @var string
     */
    protected $author;

    /**
     * Constructor
     *
     * @param string $id the module id
     */
    public function __construct( $id ) {
        $this->id = $id;
    }

    public function getModuleId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getWebsite() {
        return $this->website;
    }

    public function setWebsite($website) {
        $this->website = $website;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }
    
}
