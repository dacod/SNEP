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
 * Classe permissions faz verificação dono do arquivo e permissões dos arquivos do Snep.
 *
 * @see Snep_Inspector_Test
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class Permissions extends Snep_Inspector_Test {

    /**
     * Array de arquivos a serem verificados.
     * @var Array
     */
    public $paths = array('includes/setup.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          'sounds/moh' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          'sounds/pt_BR' => array('exists' => 1, 'writable' => 1, 'readable' => 1) );

    /**
     * Executa teste na criação do objeto.
     */
    public function __contruct() {
        self::getTests();
    }

    /**
     * Realiza testes de permissões e dono do arquivo.
     * @return Array
     */
    public function getTests() {

        // Registra erro como falso
        $result['permissions']['error'] = 0;

        // Inicia indice do array.
        $result['permissions']['message'] = '';

        // Pega array do setup.conf do Zend_Registry.
        $config = Zend_Registry::get('config');

        // Pega registro path.base do setup.conf
        $core_path = $config->system->path->base . "/";

        // Percorre array de arquivos.
        foreach($this->paths as $path => $permission) {

            // Verifica exigencia de existencia do arquivo.
            if($permission['exists']) {

                if( ! file_exists( $core_path . $path ) ) {
                    // Não existindo arquivo concatena mensagem de erro
                    $result['permissions']['message'] .= " \n";
                    $result['permissions']['message'] .=$core_path.$path.Zend_Registry::get("Zend_Translate")->translate(" does not exists. ") ."\n";
                    // Seta erro como verdadeiro
                    $result['permissions']['error'] = 1;

                    // Existindo arquivo
                }else{

                    // Verifica exigencia de verificação de escrita
                    if($permission['writable']) {
                        if( ! is_writable($core_path . $path) ) {
                            // Não existindo permissão de gravacao concatena mensagem de erro.
                            $result['permissions']['message'] .= $core_path.$path.Zend_Registry::get("Zend_Translate")->translate(" does not have permition to be modified. ") ."\n";
                            // Seta erro como verdadeiro
                            $result['permissions']['error'] = 1;
                        }
                    }

                    // Verifica existencia de verificação de leitura
                    if($permission['readable']) {
                        if( ! is_readable($core_path . $path) ) {
                            // Não existindo permissão de leitura concatena mensagem de erro.
                            $result['permissions']['message'] .= " \n";
                            $result['permissions']['message'] .= $core_path.$path.Zend_Registry::get("Zend_Translate")->translate(" does not have permition to be viewed. ") ."\n";
                            // Seta erro como falso.
                            $result['permissions']['error'] = 1;
                        }
                    }
                }
            }
        }

        // Transforma newline em br
        $result['permissions']['message'] = $result['permissions']['message'] ;

        // Retorna Array
        return $result['permissions'];
    }

    public function getTestName() {
        return Zend_Registry::get("Zend_Translate")->translate("File Permissions");
    }

}
