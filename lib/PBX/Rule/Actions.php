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

/**
 * Ações do Snep
 *
 * Classe que facilita e abstrai o controle das ações instaladas no sistema.
 *
 * @category  Snep
 * @package   PBX_Rule
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Rule_Actions {

    private $actions = array();

    /**
     * Construtor
     *
     * Aqui é feita a leitura do diretório para determinar quais ações estão
     * instaladas.
     */
    public function __construct() {
        $config = Zend_Registry::get('config');

        $actions_dir = $config->system->path->base . "/lib/PBX/Rule/Action";

        foreach( scandir($actions_dir) as $filename ) {
            // Todos os arquivos .php devem ser classes de Ações
            if( ereg(".*\.php$", $filename) ) {
                // Tentar instanciar e Adicionar no array
                $classname = 'PBX_Rule_Action_' . basename($filename, '.php');
                if(class_exists($classname)) {
                    $this->actions[] = $classname;
                }
            }
        }
    }

    /**
     * Retorna um array com todas as ações instaladas no sistema.
     *
     * @return array $actions ações instaladas no sistema
     */
    public function getInstalledActions() {
        return $this->actions;
    }
}
