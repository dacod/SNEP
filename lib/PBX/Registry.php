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
 * Acesso ao registro do Snep
 *
 * O registro do Snep permite a armazenagem de informações em um registro
 * genérico. Útil para módulos simples e pequenas funcionalidades que precisam
 * armazenar suas configurações sem interferir na estrutura de banco de dados
 * do sistema.
 *
 * Os registro são constituidos de um contexto, uma chave e um valor. Através do
 * contexto e chave você pode encontrar/armazenar valores para serem resgatados
 * em qualquer hora ou parte do sistema.
 *
 * AVISO: As informações armazenadas no registro são persistidas em banco de
 * dados e NÃO são excluidas a não ser que seja ordenado. Cuidado para manter
 * o sistema limpo, se a informação não é mais necessária, apague.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
final class PBX_Registry {

    /**
     * Contexto que está sendo usado no registro
     *
     * @var string
     */
    private $context;

    /**
     * Classe de interface com o banco de dados
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $db;

    /**
     * Instancia única desta classe
     *
     * @var PBX_Registry
     */
    private static $instance;

    /**
     * Conteúdo do contexto que está sendo processado pela classe.
     *
     * Funciona como cache para acesso.
     *
     * @var array
     */
    private $registryData;

    /**
     * Construtor de objetos de registro
     *
     * @param string $context
     */
    private function __construct( $context ) {
        $this->db = Zend_Registry::get('db');
        $this->setContext($context);
    }

    /**
     * Tornando clones privados para evitar violação de Singleton
     */
    private function __clone() { /* Nada aqui */ }

    /**
     * Retorna um valor do registro
     *
     * @param string $key chave
     * @return mixed valor
     */
    public function __get( $key ) {
        if(isset($this->registryData[$key])) {
            return $this->registryData['$key'];
        }
        else {
            throw new PBX_Exception_NotFound("Chave não existe neste contexto");
        }
    }

    /**
     * Verifica se um valor existe no registro
     *
     * @param string $key
     * @return mixed valor
     */
    public function  __isset( $key ) {
        if(isset($this->registryData[$key])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Seta/Atualiza um valor do registro
     *
     * @param string $key chave do valor
     * @param mixed $value novo valor
     */
    public function __set( $key, $value ) {
        if( isset($this->{$key}) ) {
            $this->db->update("registry", array("value"=>$value), "context='{$this->getContext()}' AND `key`='$key'");
        }
        else {
            $this->db->insert("registry", array(
                "context" => $this->getContext(),
                "key"     => $key,
                "value"   => $value
            ));
        }
        $this->update();
    }

    /**
     * Remove um valor do registro
     *
     * @param string $key
     */
    public function __unset( $key ) {
        if( isset($this->{$key}) ) {
            $this->db->delete("registry", "context='{$this->getContext()}' AND `key`='$key'");
            $this->update();
        }
    }

    /**
     * Remove um valor do registro
     *
     * @param string $context
     * @param string $key
     */
    public static function delete( $context, $key ) {
        $registry = self::getInstance();

        if($registry->getContext() != $context ) {
            $registry->setContext($context);
        }

        unset($registry->{$key});
    }

    /**
     * Retorna um valor do registro
     *
     * @param string $context contexto do registro
     * @param string $key chave do registro
     * @return mixed valor do registro
     */
    public static function get( $context, $key ) {
        $registry = self::getInstance();
        
        if($registry->getContext() != $context ) {
            $registry->setContext($context);
        }
        
        return $registry->{$key};
    }

    /**
     * Retorna todos os valores de um contexto
     *
     * @param string $context
     * @return array valores que foram encontrados no contexto
     */
    public static function getAll( $context ) {
        $registry = self::getInstance();

        if($registry->getContext() != $context ) {
            $registry->setContext($context);
        }

        return $registry->getAllValues();
    }

    /**
     * Retorna um array associativo com todos os valors do atual contexto
     *
     * @return array valores
     */
    public function getAllValues() {
        return $this->registryData;
    }

    /**
     * Retorna o contexto que essa classe está usando
     *
     * @return string contexto
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * Retorna a instancia dessa classe
     *
     * @param string context
     * @return PBX_Registry instancia única desta classe
     */
    public static function getInstance( $context ) {
        if( !isset( self::$instance ) ) {
            self::$instance = new self($context);
        }
        return self::$instance;
    }

    /**
     * Armazena um valor no registro
     *
     * @param string $context contexto do registro
     * @param string $key chave do registro
     * @param mixed $value valor a ser guardado
     */
    public static function set( $context, $key, $value) {
        $registry = self::getInstance();

        if($registry->getContext() != $context ) {
            $registry->setContext($context);
        }

        $registry->{$key} = $value;
    }

    /**
     * Define o contexto a ser usado pela classe
     *
     * @param string $context novo contexto
     */
    public function setContext( $context ) {
        if($this->getContext() != $context) {
            $old_context = $this->getContext();
            $this->context = $context;
            try {
                $this->update();
            }
            catch( PBX_Registry_Exception_ContextNotFound $ex ) {
                $this->context = $old_context;
                throw $ex;
            }
        }
    }

    /**
     * Atualiza o cache da classe
     */
    private function update() {
        $select = $this->db->select()
                ->from('registry',array("key","value"))
                ->where("context = '$this->context'");

        $raw_data = $this->db->query($select)->fetchAll();

        if( count($raw_data) == 0 ) {
            throw new PBX_Registry_Exception_ContextNotFound("'{$this->getContext()}': No such context");
        }

        $this->registryData = array();
        foreach ($raw_data as $entry) {
            $this->registryData[$entry['key']] = $entry['value'];
        }
    }

}
