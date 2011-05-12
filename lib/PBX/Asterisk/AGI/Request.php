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

require_once "Asterisk/AGI/Request.php";
require_once "PBX/Interfaces.php";
require_once "Snep/Logger.php";

/**
 * Requisições AGI.
 *
 * Essa classe implementa metodos e facilidades relacionadas a requisições
 * feitas pelo Asterisk via Gateway Interface (AGI).
 *
 * Esta classe agrega algumas coisas encontradas somente no código do snep
 * como objetos para Ramais, Troncos e Agentes.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_AGI_Request extends Asterisk_AGI_Request {

    /**
     * Objeto da entidade que está originando a chamada.
     *
     * Pode ser um Ramal, Agente ou Tronco.
     *
     * @var mixed srcObj
     */
    protected $srcObj;

    /**
     * Retorna o objeto que representa a entidade que originou a ligação.
     *
     * @return mixed srcObj Entidade que origina a ligação
     */
    public function getSrcObj() {
        return $this->srcObj;
    }

    /**
     * Define o objeto que faz a requisição da ligação. (Originador)
     *
     * @param Snep_Exten|Snep_Trunk $obj
     */
    public function setSrcObj( $obj ) {
        $this->srcObj = $obj;
    }

    /**
     * Facilita a busca de variáveis que vieram em requisição (channel,
     * exten, etc).
     *
     * @param string $varname
     * @return mixed valor da propriedade
     */
    public function __get($varname) {
        switch($varname) {
            case 'origem':
                $varname = 'callerid';
                break;
            case 'destino':
                $varname = 'extension';
                break;
            case 'contexto':
                $varname = 'context';
        }
        return parent::__get($varname);
    }

    public function __set($varname, $value) {
        switch($varname) {
            case 'origem':
                $varname = 'callerid';
                break;
            case 'destino':
                $varname = 'extension';
                break;
            case 'contexto':
                $varname = 'context';
        }
        parent::__set($varname, $value);
    }

    /**
     * Construtor da requisição.
     *
     * Para identificar o objeto de origem estamos considerando seu callerid
     * como número de ramal. Isso, obviamente, irá identificar somente ramais.
     * Importante ressaltar que a "falsidade ideológica" entre os canais é mais
     * fácil de ser praticada nesse sistema.
     *
     * @param int $origem
     * @param string $destino
     * @param string $contexto
     */
    public function __construct($agi_request) {
        parent::__construct($agi_request);
        $log = Snep_Logger::getInstance();

        // Descobrindo se esse canal criado pertence a alguma entidade
        // cadastrada no snep.
        $channel = $this->request['channel'];
        // removendo o hash de controle do asterisk
        // de TECH/ID-HASH para TECH/ID
        $channel = strpos($channel,'-') ? substr($channel, 0, strpos($channel,'-')) : $channel;

        $object = PBX_Interfaces::getChannelOwner($channel);

        if( $object instanceof Snep_Trunk && $object->allowExtensionMapping()) {
            try {
                $exten = PBX_Usuarios::get($this->origem);
                if( $exten->getInterface() instanceof PBX_Asterisk_Interface_VIRTUAL ) {
                    $object = $exten;
                }
            }
            catch ( PBX_Exception_NotFound $ex ) {
                // Ignore
            }
        }

        $this->setSrcObj($object);

        if( is_object($object) ) {
            $classname = get_class($this->getSrcObj());
            $log->info("Identified source: {$this->getSrcObj()} ($classname)");
        }
    }
}
