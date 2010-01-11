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
 * Classe SnepAction implementa o básico para ações de regras de negócio.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
abstract class PBX_Rule_Action {

    /**
     * Configurações da ação.
     *
     * A classe não precisa ter todos os parametros como opcional, mas não deve
     * deixar de inicializá-los caso não os receba para não gerar runtime
     * errors. O comportamento esperado é fazer um log critico usando o Zend_Log
     * que está no registro e sair sem executar qualquer ação, mas sair
     * normalmente (return;)
     *
     * @var array Configurações da ação
     */
    protected $config;

    /**
     * Configuração padrão das ações dessa classe
     * 
     * Deve ser a mesma para todas as ações
     *
     *
     * @var array Configuração padrão das ações dessa classe
     */
    protected $defaultConfig;

    /**
     * Regra de negócio dona da ação.
     *
     * Atributo opcional. Somente as ações que fazem uso de regra devem reclamar
     * da falta dessa informação.
     *
     * @var PBX_Rule $rule
     */
    protected $rule;

    /**
     * Inicializando config no construtor por padrão para evitar erros de
     * runtime mais graves.
     */
    public function __construct() {
        $this->config = array();
        $this->setDefaultConfig( PBX_Registry::getAll(get_class(self)) );
    }

    /**
     * Executa a ação. É chamado dentro de uma instancia usando AGI.
     *
     * @param Asterisk_AGI $asterisk
     * @param int $rule - A regra que chamou essa ação. É passado pra que
     * a ação possa restaurar as configurações dela para essa regra. Esse
     * parametro é opcional.
     */
    abstract public function execute($asterisk, $request);

    /**
     * Método que auxilia na geração da interface fornecendo os parametros
     * necessários pela ação.
     *
     * @return XML com as possíveis configurações da Ação.
     */
    public function getConfig() {
        return "";
    }

    /**
     * Retorna todas as configurações da interface para que seja feita a
     * persistencia.
     * 
     * @return array Configurações da Ação para persistencia
     */
    public function getConfigArray() {
        return $this->config;
    }

    /**
     * Configurações padrão para todas as ações dessa classe. Essas possuem uma
     * tela de configuração separada.
     *
     * Os campos descritos aqui podem ser usados para controle de timout,
     * valores padrão e informações que não pertencem exclusivamente a uma
     * instancia da ação em uma regra de negócio.
     *
     * @return string XML com as configurações default para as classes
     */
    public function getDefaultConfigXML() {
        return "";
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     *
     * @return string Nome da Ação
     */
    abstract public function getName();

    /**
     * Retorna, se houver, uma regra de negócio dona dessa ação.
     *
     * @return PBX_Rule
     */
    public function getRule() {
        return $this->rule;
    }

    /**
     * Retorna o numero da versão da classe.
     * 
     * @return string Versão da classe
     */
    abstract public function getVersion();

    /**
     * Retorna uma breve descrição de funcionamento da ação.
     *
     * @return string Descrição de funcionamento ou objetivo
     */
    abstract public function getDesc();

    /**
     * Define parametros da regra.
     *
     * @param array $config
     */
    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * Seta a configuração padrão para essa regra.
     *
     * @param array $config
     */
    public function setDefaultConfig( $config ) {
        $this->defaultConfig = $config;
    }

    /**
     * Regra de negócio para ser considerada dona(chamadora) dessa ação.
     *
     * @param PBX_Rule $rule
     */
    public function setRule($rule) {
        $this->rule = $rule;
    }
}
