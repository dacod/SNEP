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
 * Classe agi faz teste de permissão e existência nos arquivos referentes ao
 * asterisk
 *
 * @see Snep_Inspector_Test
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class AGI extends Snep_Inspector_Test {

    /**
     * Array de arquivos e permissões exigidas.
     * @var Array
     */
    public $paths = array('/var/lib/asterisk/agi-bin/snep' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/var/lib/asterisk/moh' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-agentes.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-agents.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-authconferences.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-conferences.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-features.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-iax2.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-iax2-trunks.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-sip.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1),
                          '/etc/asterisk/snep/snep-sip-trunks.conf' => array('exists' => 1, 'writable' => 1, 'readable' => 1)
    );

    /**
     * Executa teste na criação do objeto.
     */
    public function __contruct() {
        self::getTests();
    }

    /**
     * Realiza testes de dono do arquivo e permissões de leitura e escrita
     * @return Array
     */
    public function getTests() {

        $result['agi']['error'] = 0;
        $result['agi']['message'] = '';

        // Percorre array de arquivos
        foreach ($this->paths as $path => $agi) {

            // Verifica existencia do mesmo
            if ($agi['exists']) {
                if (!file_exists($path)) {
                    // Não existindo o arquivo registra concatena mensagem de erro.
                    $result['agi']['message'] .= $path.Zend_Registry::get("Zend_Translate")->translate(" does not exists. ") ."\n";
                    // Seta erro com verdadeiro.
                    $result['agi']['error'] = 1;

                    // Existindo o arquivo, realiza testes.
                } else {

                    // Verifica se existe exigencia de gravação.
                    if ($agi['writable']) {
                        if (!is_writable($path)) {
                            // Não existindo permissão de gravação concatena mensagem de erro.
                            $result['agi']['message'] .= $path. Zend_Registry::get("Zend_Translate")->translate(" does not have permition to be modified. ") ."\n";
                            // Seta erro como verdadeiro.
                            $result['agi']['error'] = 1;
                        }
                    }

                    // Verifica se existe exigênca de leitura.
                    if ($agi['readable']) {
                        if (!is_readable($path)) {
                            // Não existindo permissão de gravação concatena mensagem de erro.
                            $result['agi']['message'] .= $path.Zend_Registry::get("Zend_Translate")->translate(" does not have permition to be viewed. ") ."\n";
                            // Seta erro como verdadeiro.
                            $result['agi']['error'] = 1;
                        }
                    }
                }
            }
        }
        // Transforma newline em br
        $result['agi']['message'] = $result['agi']['message'];

        // Retorna array.
        return $result['agi'];
    }

    public function getTestName() {
        return Zend_Registry::get("Zend_Translate")->translate("AGI SNEP Environment");
    }

}
