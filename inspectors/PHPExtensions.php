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
 * Classe extensions faz verificação de extensões do php.
 *
 * @see Snep_Inspector_Test
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class PHPExtensions extends Snep_Inspector_Test {

    /**
     * Array de extensões a serem verificadas.
     * @var Array
     */
    public $extensions = array('pdo_mysql',
                               'json'
        );

    /**
     * Executa teste na criação do objeto.
     */
    public function __contruct() {
        self::getTests();
    }

    /**
     * Executa teste de verificação de extensões do php.
     */
    public function getTests() {

        // Seta erro como falso.
        $result['extension']['error'] = 0;

        // Registra indice de mensagem no array.
        $result['extension']['message'] = '';

        // Percorre lista de extensões.
        foreach($this->extensions as $extension) {
            if( ! extension_loaded( $extension ) ) {
                // Não estando carregada e extensão, concatena mensagem de erro.
                $result['extension']['message'] .= Zend_Registry::get("Zend_Translate")->translate("The extension") .$extension.Zend_Registry::get("Zend_Translate")->translate("is not present on the system, check it. ") ."\n";
                // Seta erro como verdadeiro.
                $result['extension']['error'] = 1;
            }
        }

        // Transforma newline em br
        $result['extension']['message'] = $result['extension']['message'] ;

        // Retorna Array
        return $result['extension'];
    }

    public function getTestName() {
        return Zend_Registry::get("Zend_Translate")->translate("PHP Extensions");
    }
    
}
