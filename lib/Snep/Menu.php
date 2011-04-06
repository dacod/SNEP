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

require_once "Snep/Locale.php";

/**
 * Snep main menu system
 *
 * @category  Snep
 * @package   Snep_Bootstrap
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Menu {

    protected static $master;

    /**
     * Master instance of Snep_Menu
     *
     * @return Snep_Menu
     */
    public static function getMasterInstance() {
        if(self::$master === null) {
            self::$master = new self("master");
        }

        return self::$master;
    }

    /**
     * Menu or resource id.
     *
     * @var string id
     */
    private $id;

    /**
     * Sub-menus of this menu.
     *
     * @var Snep_Menu[]
     */
    private $children = array();

    /**
     * @var string Label to show.
     */
    private $label;

    /**
     * @var string uri for the resource
     */
    private $uri;

    /**
     * Base path for menu links
     *
     * @var string
     */
    protected $baseUrl = "";

    public function __construct( $id ) {
        $this->id = $id;
    }

    public function __toString() {
        return $this->render();
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }

    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }
    
    public function getUri() {
        return $this->uri;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }
    
    /**
     * Add a child.
     *
     * @param Snep_Menu $child
     */
    public function addChild( Snep_Menu $child ) {
        $item = $this->getChildById($child->getId());
        if($item) {
            $item->setSubmenu(array_merge($item->getSubmenu(), $child->getSubmenu()));
        }
        else {
            $this->children[] = $child;
        }
    }

    /**
     * Finds a child with the desidered id
     *
     * @param string $id
     * @return Snep_Menu|null
     */
    public function getChildById( $id ) {
        foreach ($this->getChildren() as $child) {
            if( $child->getId() === $id ) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Returns all the children of this menu.
     *
     * @return Snep_Menu[]
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Defines the children of this menu
     *
     * @param Snep_Menu_Item[] $children
     */
    public function setChildren($children) {
        $this->children = $children;
    }

    /**
     * @return string menu id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Render all the children of this menu
     *
     * @return string HTML rendered children
     */
    public function renderChildren() {
        $html = "";
        foreach ($this->getChildren() as $child) {
            $html .= $child->render();
        }
        return $html;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->label = Snep_Locale::getInstance()->getZendTranslate()->translate($label);;
    }

    /**
     * Render the menu and its children
     *
     * @return string HTML rendered menu
     */
    public function render() {
        $html = "<li id=\"{$this->getId()}\">";
        $html .= "<a href=\"{$this->getUri()}\">" . $this->getLabel() . "</a>";
        if(count($this->getChildren()) > 0) {
            $html .= "<ul>";
            $html .= $this->renderChildren();
            $html .= "</ul>";
        }

        $html .= "</li>";
        
        return $html;
    }

}
