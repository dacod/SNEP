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

require "Snep/Menu/Item.php";

/**
 * Classe para controle do Menu do snep.
 *
 * @category  Snep
 * @package   Snep_Bootstrap
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class Snep_Menu {

    /**
     * Id para impressão do id="" do menu
     *
     * @var string id
     */
    private $id;

    /**
     * Itens do menu
     *
     * @var Snep_Menu_Item[]
     */
    private $items;

    /**
     * Arquivo XML para fazer parse do menu
     *
     * @param string $xml_file
     */
    public function __construct( $xml_file ) {
        $this->setItemsFromXML( $xml_file );
    }

    /**
     * Retorna o HTML do menu para impressão
     *
     * @return string
     */
    public function __toString() {
        return $this->render();
    }

    /**
     * Adiciona um item ao menu
     *
     * @param Snep_Menu_Item $item
     */
    public function addItem( Snep_Menu_Item $item ) {
        $this->items[] = $item;
    }

    /**
     * Retorna um item do menu a partir do seu id.
     *
     * @param string $id
     * @return Snep_Menu_Item item
     */
    public function getItemById( $id ) {
        foreach ($this->getItems() as $item) {
            if( $item->getId() == $id ) {
                return $item;
            }
        }
    }

    /**
     * Retorna os itens do menu
     *
     * @return Snep_Menu_Item[]
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Define os itens do menu
     *
     * @param Snep_Menu_Item[] $items
     */
    public function setItems($items) {
        $this->items = $items;
    }

    /**
     * Retorna o id do menu.
     *
     * @return string id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Define o id do menu
     *
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Define os items do menu a partir de um arquivo XML
     *
     * @param string $xml_file
     */
    public function setItemsFromXML( $xml_file ) {
        if( !file_exists($xml_file) ) {
            throw new PBX_Exception_BadArg("File: $xml_file not found.");
        }
        else {
            // Possibilitando o tratamento dos erros de XML
            libxml_use_internal_errors(true);

            $xml = simplexml_load_file( $xml_file );

            // Tratamento de erros de parse no XML
            if (!$xml) {
                $error_msg = "Malformed XML for $xml_file:\n";
                foreach(libxml_get_errors() as $error) {
                    $error_msg .= $error->message;
                }
                throw new PBX_Exception_BadArg($error_msg);
            }
            // Restaurando o comportamento padrão do php para erros de XML
            libxml_use_internal_errors(false);

            $items = array();
            foreach ($xml->item as $item) {
                $item = $this->parseXMLMenuItem($item);
                if($item !== null) {
                    $items[] = $item;
                }
            }

            $this->setItems( $items );
        }
    }

    private function parseXMLMenuItem( $xml_item ) {
        $resourceId = $xml_item['resourceid'] ? (string) $xml_item['resourceid'] : null;
        if( $resourceId !== null && !$this->isAllowed($resourceId) ) {
            return null;
        }

        $id    = $xml_item['id'] ? (string) $xml_item['id'] : null;
        $label = $xml_item['label'] ? (string) $xml_item['label'] : null;
        $uri   = $xml_item['uri'] ? (string) $xml_item['uri'] : null;
        
        $item = new Snep_Menu_Item($id, $label, $uri);

        if( count($xml_item->item) > 0 ) {
            foreach ($xml_item->item as $xml_subitem) {
                $item->addSubmenuItem($this->parseXMLMenuItem($xml_subitem));
            }
        }

        return $item;
    }

    /**
     * Verifica se um usuário tem permissão a acessar determinado recurso do sistema
     *
     * @param string $resourceId id do recurso
     * @return boolean isAllowed
     */
    private function isAllowed( $resourceId ) {
        global $id_user;
        
        if ($id_user == 1) {
            return True;
        }

        $db = Zend_Registry::get('db');

        $sql_ver = "SELECT permissao FROM permissoes";
        $sql_ver.= " WHERE cod_usuario = '$id_user' AND cod_rotina = '$resourceId'";

        $row = $db->query($sql_ver)->fetchObject();

        if( $row->permissao == "S" ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Percorre os itens do menu e cria um HTML para impressão.
     *
     * O html segue a estrutura de listas desordenadas aninhadas.
     *
     * @return string HTML para impressão do menu
     */
    public function render() {
        $items = "";

        foreach ($this->getItems() as $item) {
            if($item->getId() != "logout") {
                $items = $item->render() . $items;
            }
            else {
                $logout = $item;
            }
        }
        $items = $logout->render() . $items;

        if($this->id !== null) {
            return "<ul id='{$this->getId()}'>" . $items . "</ul>";
        }
        else {
            return "<ul>" . $items . "</ul>";
        }
    }

}
