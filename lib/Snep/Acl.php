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
 * Controle ACL para o snep.
 *
 * @author Henrique Grolli Bassotto
 */
class Snep_Acl extends Zend_Acl {

    /**
     * Construtor Snep_Acl.
     *
     * @param boolean $whitelist se o ACL será whitelist (true) ou blacklist (false).
     */
    public function __construct($whitelist = true) {
        if ($whitelist === true) {
            $this->deny();
        } else {
            $this->allow();
        }
    }

    /**
     * Método que faz o carregamento real do XML com os nodos.
     *
     * @param SimpleXMLElement $node
     * @param string $parent id do parente do nodo analisado
     */
    protected function _loadResourcesXml($node, $parent = null) {
        if ($node->getName() == "resource") {

            if ($node['id'] === null) {
                throw new Exception("Nodo sem parâmetro 'id'");
            }

            $this->addResource(new Zend_Acl_Resource($node['id']), $parent);
            if ($node['default'] == "allow") {
                $this->allow(null, $node['id']);
            }

            foreach ($node as $child) {
                $this->_loadResourcesXml($child, (string) $node['id']);
            }
        } else {
            throw new Exception("Nodo inválido em estrutura XML para recursos de Snep_Acl: {$node->getName()}");
        }
    }

    /**
     * Carrega resources de um XML
     *
     * Implementado com busca em profundidade para análise recursiva dos nodos.
     *
     * @param SimpleXMLElement $xml nodo xml.
     */
    public function loadResourcesXml(SimpleXMLElement $xml) {
        $this->_loadResourcesXml($xml);
    }

}
