<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
abstract class Snep_Module_Descriptor {

    /**
     * ID do módulo
     *
     * @var string
     */
    protected $id = null;

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
     * Diretório onde ficam os arquivos do módulo. Importante para encontrar as
     * ações de regras de negócio do modulo.
     *
     * @var string
     */
    protected $moduleDir;

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

    public function getModuleDir() {
        return $this->moduleDir;
    }

    public function setModuleDir($moduleDir) {
        $this->moduleDir = $moduleDir;
    }

    /**
     * ID do módulo para ambiente zend do snep.
     *
     * IMPORTANTE: Essa opção sobreescreve a opção setModuleDir();
     *
     * @param string $id
     */
    public function setModuleId($id) {
        $this->setModuleDir('modules/'. $id);
        $this->id = $id;
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
     * ex:
     *    $menuTree = array(
     *        "register" => new Snep_Menu_Item('myModule', 'Telefones', '../mymdule/telefones.php')
     *    );
     * Adiciona no menu Registro do snep o item Telefones que direciona para
     * ../mymodule/telefones.php
     *
     * ex2:
     *    $menuTree = array(
     *        new Snep_Menu_Item('myModule', 'Telefones', null, array(
     *            new Snep_Menu_Item('myModule_cad', 'Cadastro', "../mymodule/1.php"),
     *            new Snep_Menu_Item('myModule_report', 'Relatório', "../mymodule/2.php")
     *        ))
     *    );
     * Adiciona um menu Telefones na raiz do menu do snep com dois subitens
     * Cadastro e Relatório.
     *
     *
     * Algumas chaves especiais podem ser útilizadas para colocar itens de menu
     * dentro dos que já existem no Snep. São elas:
     *     status  = Status
     *     config  = Configurações
     *     reports = Relatórios
     *     routing = Regras de Negócio
     *     billing = Tarifas
     *
     * @param array() $menuTree
     */
    protected function setMenuTree( $menuTree ) {
        if( is_array($menuTree) ) {
            $this->menuTree = $menuTree;
        }
        else {
            throw new PBX_Exception_BadArg("Arvore de menus para modulos deve ser um array");
        }
    }
}
