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
 * Itens do menu do Snep.
 *
 * @category  Snep
 * @package   Snep_Bootstrap
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Menu_Item {

    /**
     * Id do menu
     *
     * @var string
     */
    private $id;
    
    /**
     * Texto do item do menu.
     *
     * @var string
     */
    private $label = "undefined";

    /**
     * URI que o menu aponta.
     *
     * @var string
     */
    private $uri;

    /**
     * Array de itens de menú para que seja feito um submenu
     *
     * @var Snep_Menu_Item[]
     */
    private $submenu;

    /**
     * Id do recurso do sistema a que se refere o item para controle via ACL ou
     * permissões.
     *
     * @var string
     */
    private $resourceId;

    /**
     * Construi o item de menu
     *
     * @param string $id
     * @param string $label
     * @param string $uri
     */
    function __construct($id, $label, $uri = null, $submenu = null) {
        $this->setId($id);
        $this->setLabel($label);
        $this->setUri($uri);
        if( $submenu !== null ) {
            $this->setSubmenu($submenu);
        }
    }

    /**
     * Retorna o ID do item
     *
     * @return string id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Define um id para o item
     *
     * @param string $id
     */
    public function setId($id) {
        if( ereg("[^a-zA-Z0-9_]", $id) ) {
            throw new PBX_Exception_BadArg("Id de menus só podem conter letras, numeros e _ (underline).");
        }
        else {
            $this->id = $id;
        }
    }

    /**
     * Retorna o id do recurso.
     *
     * @return string $resourceId
     */
    public function getResourceId() {
        return $this->resourceId;
    }

    /**
     * Define um id de recurso
     *
     * @param string $resourceId
     */
    public function setResourceId($resourceId) {
        $this->resourceId = (string) $resourceId;
    }

    /**
     * Retorna o texto de impressão do link
     *
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Define um texto novo para o link
     *
     * @param string $label nova label
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * Retorna a uri do link, se existir.
     *
     * @return string
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Retorna o HTML do Item de menu renderizado
     *
     * @return string HTML
     */
    public function render() {
        $lang = Zend_Registry::get('lang');
        $label = key_exists($this->getLabel(), $lang) ? $lang[$this->getLabel()] : $this->getLabel();

        if( $this->getSubmenu() !== null ) {
            $label =  $label . " +";
        }

        $html = "";
        if( $this->getUri() !== null ) {
            $html .= "<a href='{$this->getUri()}'>$label</a>";
        }
        else {
            $html .= "<a href='#'>$label</a>";
        }

        if( $this->getSubmenu() !== null ) {
            $html .= "<ul>";
            foreach ($this->getSubmenu() as $item) {
                $html .= $item->render();
            }
            $html .= "</ul>";
        }

        return "<li>" . $html . "</li>";
    }

    /**
     * Define a URI do link
     *
     * @param string $uri
     */
    public function setUri($uri) {
        $this->uri = $uri;
    }

    /**
     * Retorna, se existir, o submenu desse item.
     *
     * @return Snep_Menu_Item[]
     */
    public function getSubmenu() {
        return $this->submenu;
    }

    /**
     * Adiciona um item ao submenu desse item
     *
     * @param Snep_Menu_Item $item
     */
    public function addSubmenuItem( Snep_Menu_Item $item ) {
        $this->submenu[] = $item;
    }

    /**
     * Define um submenu para o item
     *
     * @param Snep_Menu_Item[] $submenu
     */
    public function setSubmenu( $submenu ) {
        if( is_array($submenu) ) {
            foreach ($submenu as $item) {
                if( !$item instanceof Snep_Menu_Item ) {
                    throw new PBX_Exception_BadArg("Argumento inválido para definir submenu, espera-se um array de itens de menu. Sendo todos da classe " . get_class($this));
                }
            }
            $this->submenu = $submenu;
        }
        else {
            throw new PBX_Exception_BadArg("Argumento inválido para definir submenu, espera-se um array de itens de menu.");
        }
    }

}
