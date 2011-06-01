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
 * Classe logs faz verificação dono do arquivo e permissões dos arquivos de log do Snep.
 *
 * @see Snep_Inspector_Test
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */

class Logs extends Snep_Inspector_Test {

    /**
     * Lista de arquivos a serem verificados
     * @var Array
     */
    public $paths = array('ui.log'      => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          'agi.log'     => array('exists' => 1, 'writable' => 1, 'readable' => 1)
        );

    /**
     * Executa teste na criação do objeto.
     */
    public function __contruct() {
        self::getTests();
    }

    /**
     * Testes de dono de arquivo e permissão de escrita e leitura.
     * @return Array
     */
    public function getTests() {

        // Seta erro como falso.
        $result['logs']['error'] = 0;

        // Registra indice de mensagem no array.
        $result['logs']['message'] = '';

        // Pega array do setup.conf do Zend_Registry.
        $config = Zend_Registry::get('config');

        // Pega registro path.log do setup.conf
        $core_path = $config->system->path->log . "/";

        // Percorre array de arquivos.
        foreach($this->paths as $path => $logs) {

            // Verifica existência do arquivo.
            if( ! file_exists( $core_path . $path ) ) {
                // Não existindo concatena mensagem de erro.
               $core_path.$path.Zend_Registry::get("Zend_Translate")->translate(" doesn't exist. ") ."\n";
                // Seta erro como verdadeiro.
                $result['logs']['error'] = 1;

                // Existindo arquivo
            }else{

                // Verifica se existe exigência de verificação de escrita.
                if($logs['writable']) {
                    if( ! is_writable($core_path . $path) ) {
                        // Não existindo concatena mensagem de erro.
                        $result['logs']['message'] .= $core_path.$path.Zend_Registry::get("Zend_Translate")->translate(" doesn't have editing permissions. ") ."\n";
                        // Seta erro como verdadeiro.
                        $result['logs']['error'] = 1;
                    }
                }

                // Verifica se existe exigência de verificação de leitura
                if($logs['readable']) {
                    if( ! is_readable($core_path . $path) ) {
                        // Não existindo concatena mensagem de erro.
                        $result['logs']['message'] .=$core_path.$path.Zend_Registry::get("Zend_Translate")->translate(" doesn't have reading permissions. ") ."\n";
                        // Seta erro como verdadeiro.
                        $result['logs']['error'] = 1;
                    }
                }

            }

            // Transforma newline em br
            $result['logs']['message'] = $result['logs']['message'];

            // Retorna Array
            return $result['logs'];
        }

    }

    public function getTestName() {
        return Zend_Registry::get("Zend_Translate")->translate("Log Files");
    }
}
