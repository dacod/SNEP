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
 * Classe para descrição de modulos do Snep
 *
 * Para criar ações de regras de negócio em um módulo basta colocá-las em um
 * sub-diretório actions/ dentro do seu moduleDir/
 * 
 *
 * @category  Snep
 * @package   Snep_Module
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
abstract class Snep_Module_Descriptor {

    /**
     * Nome do modulo
     */
    protected $name = "Unamed Module";

    /**
     * Versão do módulo
     *
     * @var string
     */
    protected $version = "";

    /**
     * Descrição do módulo
     *
     * @var string
     */
    protected $description = "";

    /**
     * Arvore de menus criada pelo módulo.
     *
     * @see Snep_Module_Descriptor::setMenuTree()
     * @var array
     */
    protected $menuTree = array();

    /**
     * Diretório onde ficam os arquivos do módulo.
     *
     * @var string
     */
    protected $moduleDir;

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

    public function getModuleDir() {
        return $this->moduleDir;
    }

    public function setModuleDir($moduleDir) {
        $this->moduleDir = $moduleDir;
    }

    /**
     * Retorna uma árvore de menu's para ser inserida no menu principal do Snep
     * caso o módulo o queira.
     *
     * @return array menu
     */
    public function getMenuTree() {
        return $this->menuTree;
    }

    /**
     * Define uma árvore de menu para o módulo
     *
     * Formato de ex:
     * array(
     *     "ModuleName" => array(
     *         "Opt1" => "caminho/para/o/opt1",
     *         "Opt2" => "caminho/para/o/opt2",
     *     )
     * );
     *
     * Algumas chaves especiais podem ser útilizadas para colocar itens de menu
     * dentro dos que já existem no Snep. São elas:
     *     status  = Status
     *     config  = Configurações
     *     reports = Relatórios
     *     rules   = Regras de Negócio
     *
     * @param array() $menuTree
     */
    protected function setMenuTree( $menuTree ) {
        $this->menuTree = $menuTree;
    }
}
