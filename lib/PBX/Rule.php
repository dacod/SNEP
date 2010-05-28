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
 * Regra de Negócio
 *
 * Classe que implementa as regra de negócio do snep. Estas são responsáveis
 * por executar as ações com a ligação através da interface de comunicação
 * com o asterisk.
 *
 * Suas capacidades variam de acordo com as ações que são executadas.
 *
 * Dentre as capacidades padrão está a gravação. Implementada de forma simples
 * ela grava os arquivos no /tmp do sistema usando o unix timestamp como nome de
 * arquivo.
 *
 * @category  Snep
 * @package   Snep_Rule
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Rule {

    /**
     * Array contendo os objetos que executam as ações da regra de negócio.
     * As classes aqui devem extender a classe PBX_Rule_Action
     *
     * @var array de Actions
     */
    private $acoes = array();

    /**
     * Define se a regra deve ser considerada ativada ou desativada.
     *
     * @var boolean ativa
     */
    private $active = true;

    /**
     * Interface de comunicação com o asterisk
     *
     * @var Asterisk_AGI asterisk
     */
    private $asterisk = null;

    /**
     * Descrição para usuários da regra de negócio.
     *
     * @var string descrição
     */
    private $desc = "";

    /**
     * Lista de destinos aos quais essa regra espera.
     *
     * @var array dst Destinos com que essa regra trabalha
     */
    private $dst = array();

    /**
     * ID para controle de regras persistidas em banco de dados.
     *
     * @var int id da regra.
     */
    private $id = -1;

    /**
     * Define se a regra vai ou não gravar a ligação.
     *
     * @var boolean isRecording
     */
    private $isRecording = false;

    /**
     * Prioridade com a qual deve ser trada essa regra em relação a outras
     * regras na hora de processar o plano de discagem.
     *
     * @var int prio Prioridade de execução da regra
     */
    private $priority = 0;

    /**
     * Define qual será a aplicação que efetuará a gravação da ligação.
     *
     * @var array recordApp
     */
    private $recordApp;

    /**
     * Requisição de ligação a qual essa regra deve usar para executar suas
     * ações.
     *
     * @var Asterisk_AGI_Request requisição
     */
    private $request = null;

    /**
     * Lista de origens as quais essa regra está apta a operar.
     *
     * @var array src Origens com que essa regra trabalha
     */
    private $src = array();

    /**
     * Array com range de horários, formato:
     * hh:ss-hh:ss
     *
     * início-fim, aceita também horários invertidos ex: 18:00-08:00.
     *
     * Exemplo para casar horário comercial:
     * 08:00-12:00
     * 13:30-18:00
     * 
     * @var string array Array com Range de horários
     */
    private $validade = array();

    /**
     * Ex:
     * mon
     * tue
     * wed
     * thu
     * fri
     *
     * @var string array Array com dias da semana em que a regra é válida
     */
    private $validWeekDays = array("sun", "mon", "tue", "wed", "thu", "fri", "sat");

    /**
     * Instance of PBX_Rule_Plugin_Broker
     * @var PBX_Rule_Plugin_Broker
     */
    protected $plugins = null;

    /**
     * Construtor do objeto.
     *
     * Inicia alguns atributos mais complexos.
     */
    public function __construct() {
        $recordFilename = "/tmp/" . time() . ".wav";
        $this->setRecordApp('MixMonitor', array($recordFilename, "b"));
        $this->plugins = new PBX_Rule_Plugin_Broker();
        $this->plugins->setRule($this);
    }

    /**
     * Register a plugin.
     *
     * @param  PBX_Rule_Plugin $plugin
     * @param  int $stackIndex Optional; stack index for plugin
     * @return PBX_Rule
     */
    public function registerPlugin(PBX_Rule_Plugin $plugin, $stackIndex = null)
    {
        $this->plugins->registerPlugin($plugin, $stackIndex);
        return $this;
    }

    /**
     * Unregister a plugin.
     *
     * @param  string|PBX_Rule_Plugin $plugin Plugin class or object to unregister
     * @return PBX_Rule
     */
    public function unregisterPlugin($plugin)
    {
        $this->plugins->unregisterPlugin($plugin);
        return $this;
    }

    /**
     * Is a particular plugin registered?
     *
     * @param  string $class
     * @return bool
     */
    public function hasPlugin($class)
    {
        return $this->plugins->hasPlugin($class);
    }

    /**
     * Retrieve a plugin or plugins by class
     *
     * @param  string $class
     * @return false|PBX_Rule_Plugin|array
     */
    public function getPlugin($class)
    {
        return $this->plugins->getPlugin($class);
    }

    /**
     * Retrieve all plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins->getPlugins();
    }

    /**
     * Adiciona Ações a fila de execução da regra.
     *
     * @param PBX_Rule_Action $acao - Ação a ser adicionada a fila de execução
     */
    public function addAcao(PBX_Rule_Action $acao) {
        $this->addAction($acao);
    }

    /**
     * Adiciona Ações a fila de execução da regra.
     *
     * @param PBX_Rule_Action $action - Ação a ser adicionada a fila de execução
     */
    public function addAction(PBX_Rule_Action $action) {
        $action->setRule($this);
        $this->acoes[] = $action;
    }

    /**
     * Adiciona elemento a lista de Destinos da regra
     *
     * Tipos válidos:
     *  R  - Ramal, aceita numero do ramal
     *  RX - Expressão Regular Asterisk
     *  X  - Qualquer Numero
     *  G  - Grupo de Destino
     *  CG - Grupo de contatos
     *
     * @param array $item array com tipo e valor do destino
     */
    public function addDst($item) {
        if(is_array($item) && isset($item['type']) && isset($item['value'])) {
            $this->dst[] = $item;
        }
        else {
            throw new PBX_Exception_BadArg("Argumento invalido para adicao de destino na regra {$this->getId()}: {$this->getDesc()})");
        }
    }

    /**
     * Adiciona elemento a lista de origens
     *
     * Tipos válidos:
     *  R  - Ramal, aceita numero do ramal
     *  T  - Tronco, id do tronco
     *  RX - Expressão Regular Asterisk
     *  X  - Qualquer Numero
     *  CG - Grupo de contatos
     *
     * @param array $item array com o tipo e valor da origem
     */
    public function addSrc($item) {
        if(is_array($item) && isset($item['type']) && isset($item['value'])) {
            $this->src[] = $item;
        }
        else {
            throw new PBX_Exception_BadArg("Argumento invalido para adicao de origem na regra {$this->getId()}: {$this->getDesc()})");
        }
    }

    /**
     * Adiciona tempo na lista de tempos.
     * @param string $time
     */
    public function addValidTime($time) {
        $this->validade[] = $time;
    }
    
    /**
     * Adiciona dia da semana na lista de dias válidos.
     * 
     * Formato: Dia em inglês abreviado em 3 letras:
     * sun
     * mon
     * tue
     * wed
     * thu
     * fri
     * sat
     *
     * @param string $weekDay 
     */
    public function addWeekDay($weekDay) {
        $weekDay = strtolower($weekDay);

        if( !in_array($weekDay, array("sun", "mon", "tue", "wed", "thu", "fri", "sat")) ) {
            throw new InvalidArgumentException("Dia da semana invalido");
        }
        
        if( !in_array($weekDay, $this->validWeekDays) ) {
            $this->validWeekDays[] = $weekDay;
        }
    }

    /**
     * Coverte expressões regulares do asterisk para o padrão posix
     *
     * @param expressão regular do asterisk
     * @return regra em expressão regular padrão posix
     */
    private function astrule2regex($astrule) {
        $astrule = str_replace("*", "\*", $astrule);
        $astrule = str_replace("|", "", $astrule);
        if(preg_match_all("#\[[^]]*\]#",$astrule, $brackets)){
            $brackets = $brackets[0];
            foreach($brackets as $key => $value){
                $new_bracket = "[";
                for($i = 1; $i < strlen($value)-1; $i++){
                    $char = (substr($value,$i,1) !== false) ? substr($value,$i,1) : -1;
                    $charnext = (substr($value,$i+1,1) !== false) ? substr($value,$i+1,1) : -1;
                    if($char != "-" && $charnext != "-" && $i < (strlen($value)-2)){
                        $new_bracket = $new_bracket . $char . "|";
                    } else {
                        $new_bracket = $new_bracket . $char;
                    }
                }
                $lists[$key] = $new_bracket . "]";
            }
            $astrule = str_replace($brackets, $lists, $astrule);
        }
        $sub  = array("_","X","Z","N", ".", "!");
        $exp  = array("","[0-9]","[1-9]","[2-9]", "[[0-9]|.*]", ".*");
        $rule = str_replace($sub, $exp, $astrule);
        return "^" . $rule . "\$";
    }

    /**
     * Checa se uma origem/destino casa com um numero
     *
     * @param string $type Tipo de origem/destino
     * @param string $expr Expressão do tipo, se houver
     * @param string $value Valor a ser confrontado com a expressão
     * @return boolean Resultado da checagem
     */
    private function checkExpr($type, $expr, $value) {
        switch($type) {
            case 'RX': // Expressão Regular
                return preg_match("/{$this->astrule2regex($expr)}/", $value);
                break;
            case 'G':
                if($this->request->getSrcObj() instanceof Snep_Usuario) {
                    return PBX_Usuarios::hasGroupInheritance($expr, $this->request->getSrcObj()->getGroup());
                }
                else {
                    return false;
                }
                break;
            case 'R': // Vinda de um Ramal específico
                return $value == $expr? true : false;
                break;
            case 'S': // Sem destino - Válido somente para destinos (duh!).
                return $value == 's'? true : false;
                break;
            case 'T': // Troncos
                $log = Zend_Registry::get('log');
                if( ($this->request->getSrcObj() instanceof Snep_Trunk) && $this->request->getSrcObj()->getId() == $expr) {
                    return true;
                }
                else {
                    return false;
                }
                break;
            case 'X': // Qualquer origem/destino
                return true;
                break;
            case 'CG':
                $db = Zend_Registry::get('db');
                $select = $db->select()
                             ->from('contacts_names')
                             ->where("`group` = '$expr' AND (phone_1 = '$value' OR cell_1 = '$value')");

                $stmt = $db->query($select);
                $groups = $stmt->fetchAll();
                if( count($groups) > 0 ) {
                    return true;
                }
                else {
                    return false;
                }
                break;
            case "AL":
                $aliases = PBX_ExpressionAliases::getInstance();

                $expression = $aliases->get( (int)$expr );

                $found = false;
                foreach ($expression["expressions"] as $expr_value) {
                    if(preg_match("/{$this->astrule2regex($expr_value)}/", $value)) {
                        $found = true;
                        break;
                    }
                }

                return $found;
                break;
            default:
                throw new PBX_Exception_BadArg("Tipo de expressao invalido '$type' para checagem de origem/destino, cheque a regra de negocio {$this->parsingRuleId}");
        }
    }

    /**
     * Limpa a lista de ações da regra
     */
    public function cleanActionsList() {
        $this->acoes = array();
    }

    /**
     * Limpa lista de Validade
     */
    public function cleanValidTimeList() {
        $this->validade = array();
    }

    /**
     * Limpa lista de dias da semana em que a regra é válida.
     */
    public function cleanValidWeekList() {
        $this->validWeekDays = array();
    }

    /**
     * Desativa a regra de negócio
     */
    public function disable() {
        $this->active = false;
    }

    /**
     * Não permite gravação da ligação na próxima execução da regra.
     */
    public function dontRecord() {
        $this->isRecording = false;
    }

    /**
     * Limpa o array de destinos
     */
    public function dstClean() {
        $this->dst = array();
    }

    /**
     * Habilita a regra de negócio.
     */
    public function enable() {
        $this->active = true;
    }

    /**
     * Executa Ações das Regras
     *
     * Este método inicia a execução das ações que a regra deve efetuar.
     */
    public function execute() {
        $asterisk = $this->asterisk; // facilitando o trabalho
        $log = Zend_Registry::get('log');

        $to_execute = true;
        try {
            $this->plugins->startup();
        }
        catch(PBX_Rule_Action_Exception_StopExecution $ex) {
            $log->info("Interrompendo execução a pedido de plugin: {$ex->getMessage()}");
            $to_execute = false;
        }

        if(count($this->acoes) == 0) {
            $log->warn("A regra nao possui nenhuma acao");
        }
        else {
            $requester = $this->request->getSrcObj();
            // Verificando se o telefone for um ramal e se ele não está bloqueado (cadeado).
            if($requester instanceof Snep_Ramal && $requester->isLocked()) {
                $log->info("Usuario $requester esta com cadeado habilitado");
                $asterisk->stream_file('ext-disabled');
            }
            else {
                if( $this->isRecording() ) {
                    $recordApp = $this->getRecordApp();
                    $log->info("Executando aplicacao de gravacao '{$recordApp['application']}'");
                    $this->asterisk->exec($recordApp['application'], $recordApp['options']);
                    // Usando função que corrige gravação em transferências quando não feitas pelo originador da ligação
                    // $this->asterisk->set_variable("AUDIOHOOK_INHERIT({$recordApp['application']})", "yes");
                }

                // A foreach don't do it because of PBX_Rule_Action_Exception_GoTo
                for($priority=0; $priority < count($this->acoes) && $to_execute; $priority++) {
                    $acao = $this->acoes[$priority];

                    try {
                        $this->plugins->preExecute($priority);
                    }
                    catch(PBX_Rule_Action_Exception_StopExecution $ex) {
                        $log->info("Interrompendo execução a pedido de plugin: {$ex->getMessage()}");
                        $to_execute = false;
                    }

                    if($to_execute === true) {
                        $log->debug("Executando acao $priority-" . get_class($acao));
                        try {
                            $acao->execute($asterisk, $this->request);
                        }
                        catch(PBX_Exception_AuthFail $ex) {
                            $log->info("Parando execucao devido a falha na autenticacao do ramal em $priority-" . get_class($acao) . ". Retorno: {$ex->getMessage()}");
                            $to_execute = false;
                        }
                        catch(PBX_Rule_Action_Exception_StopExecution $ex) {
                            $log->info("Parando execucao das acoes a pedido de: $priority-" . get_class($acao));
                            $to_execute = false;
                        }
                        catch(PBX_Rule_Action_Exception_GoTo $goto) {
                            $priority = $goto->getIndex() -1;
                            $log->info("Desviando fluxo para acao {$goto->getIndex()}.");
                        }
                        catch(Exception $ex) {
                            $log->crit("Problema ao processar acao $priority-" . get_class($acao) ." da regra $this->id-$this");
                            $log->crit($ex);
                        }
                    }
                    
                    try {
                        $this->plugins->postExecute($priority);
                    }
                    catch(PBX_Rule_Action_Exception_StopExecution $ex) {
                        $log->info("Interrompendo execução a pedido de plugin: {$ex->getMessage()}");
                        $to_execute = false;
                    }
                }
            }
        }
        $this->plugins->shutdown();
    }

    /**
     * Retorna a ação da regra pelo seu índice
     *
     * @param int $index
     * @return PBX_Rule_Action
     */
    public function getAcao($index) {
        return $this->getAction($index);
    }

    /**
     * Retorna a ação da regra pelo seu índice
     *
     * @param int $index
     * @return PBX_Rule_Action
     */
    public function getAction($index) {
        if(isset($this->acoes[$index])) {
            return $this->acoes[$index];
        }
        else {
            throw new PBX_Exception_NotFound("Nenhuma acao de indice $index na regra.");
        }
    }

    /**
     * Retorna as ações que a regra de negócio executa.
     *
     * @return array acoes da regra
     */
    public function getAcoes() {
        return $this->getActions();
    }

    /**
     * Retorna as ações que a regra de negócio executa.
     *
     * @return array acoes da regra
     */
    public function getActions() {
        return $this->acoes;
    }

    /**
     * Obter a descrição da regra de negócio
     *
     * @return string descrição
     */
    public function getDesc() {
        return $this->desc;
    }

    /**
     * Recupera a lista de destinos
     *
     * @return array dst
     */
    public function getDstList() {
        return $this->dst;
    }

    /**
     * Recupera o ID da regra de negócio
     *
     * @return id da regra
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Prioridade que a regra está requisitando
     *
     * @return int $prio
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * Retorna o nome da aplicação que será usada para gravar as ligações.
     *
     * @return string recordApp
     */
    public function getRecordApp() {
        return $this->recordApp;
    }

    /**
     * Recupera a lista de origens
     *
     * @return array src
     */
    public function getSrcList() {
        return $this->src;
    }

    /**
     * Verifica se um destino é válido para essa regra e retorna a expressão
     * válida.
     *
     * @param string $dst
     * @return null | string
     */
    public function getValidDstExpr($dst) {
        foreach ($this->getDstList() as $thisdst) {
            if($this->checkExpr($thisdst['type'], $thisdst['value'], $dst)) {
                return $thisdst;
            }
        }
        return null;
    }

    /**
     * Verifica se uma origem é válida para essa regra e retorna a expressão
     * válida.
     *
     * @param string $src
     * @return null | string
     */
    public function getValidSrcExpr($src) {
        foreach ($this->getSrcList() as $thissrc) {
            if($this->checkExpr($thissrc['type'], $thissrc['value'], $src)) {
                return $thissrc;
            }
        }
        return null;
    }

    /**
     * Pega a lista de tempos da regra
     * @return array string $time
     */
    public function getValidTimeList() {
        return $this->validade;
    }

    /**
     * Retorna um array com os dias da semana que são válidos para essa regra
     *
     * @return array string
     */
    public function getValidWeekDays() {
        return $this->validWeekDays;
    }

    /**
     * Checa se a regra gostari de estar ativa ou não
     *
     * @return boolean active
     */
    public function isActive() {
        return $this->active;
    }

    /**
     * Retorna a ordem de gravação da ligação.
     *
     * @return boolean isRecording;
     */
    public function isRecording() {
        return $this->isRecording;
    }

    /**
     * Verifica se um destino é válido para essa regra.
     *
     * @param string $extension
     * @return boolean validity
     */
    public function isValidDst($extension) {
        foreach ($this->getDstList() as $dst) {
            if( $dst['type'] == 'G' ) {
                try {
                    $peer = PBX_Usuarios::get($extension);
                }
                catch(PBX_Exception_NotFound $ex) { $peer = false; }

                if($peer instanceof Snep_Usuario && PBX_Usuarios::hasGroupInheritance($dst['value'], $peer->getGroup()) ){
                    return true;
                }
            }
            else if( $this->checkExpr($dst['type'], $dst['value'], $extension) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica se uma origem é válida para essa regra.
     *
     * @param string $src
     * @return boolean validity
     */
    public function isValidSrc($src) {
        foreach ($this->getSrcList() as $thissrc) {
            if($this->checkExpr($thissrc['type'], $thissrc['value'], $src))
                return true;
        }
        return false;
    }

    /**
     * Verifica se um tempo é válido para execução dessa regra.
     *
     * @param string $time
     * @param string $week Dia da semana em formato 3 letras e em inglês
     * @return boolean validity
     */
    public function isValidTime($time = null, $week = null) {
        if($time === null) {
            $time = date("H:i");
        }
        
        if($week === null) {
            $week = strtolower(date("D"));
        }
        else {
            $week = strtolower($week);
        }

        if( in_array($week, $this->validWeekDays) ) {
            foreach ($this->getValidTimeList() as $validTimeRange) {
                $validTimeRange = explode('-',$validTimeRange);
                $start = $validTimeRange[0];
                $end = $validTimeRange[1];
                if($start > $end){
                    if($start < $time OR $time <= $end) {
                        return true;
                    }
                }
                else {
                    if($start <= $time && $time < $end) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Ativa gravação da ligação na próxima execução da regra.
     */
    public function record() {
        $this->isRecording = true;
    }

    /**
     * Remove a ação da lista pelo índice dela.
     *
     * Remove ação do índice específicado e reordena os índices.
     *
     * @param int $indice
     * @return PBX_Rule_Action|null Regra removida.
     */
    public function removerAcao($indice) {
        $nova_ordem = array();
        $removed = null;
        // Loop necessário para manter a estrutura linear organizada das ações
        foreach ($this->acoes as $i => $acao) {
            if($i != $indice) {
                $nova_ordem[] = $acao;
            }
            else {
                $removed = $acao;
                $removed->setRegra(null);
            }
        }
        $this->acoes = $nova_ordem;
        
        return $removed;
    }

    /**
     * Remove um dia da semana da lista de dias válidos.
     *
     * @param string $weekDay
     */
    public function removeWeekDay( $weekDay ) {
        $weekDay = strtolower($weekDay);
        $index = array_search($weekDay, $this->validWeekDays);
        if( $index !== null ) {
            unset($this->validWeekDays[$index]);
        }
    }

    /**
     * Define se a regra está ativa ou não
     *
     * @param boolean $active
     */
    public function setActive( $active ) {
        $this->active = $active;
    }

    /**
     * Fornece uma interface de conexão com o asterisk.
     *
     * @param Asterisk_AGI $asterisk
     */
    public function setAsteriskInterface($asterisk) {
        $this->asterisk = $asterisk;
        $this->plugins->setAsteriskInterface($asterisk);
        if(!isset($this->request)) $this->request = $asterisk->requestObj;
    }

    /**
     * Define uma descrição para a regra
     *
     * @param string $desc descrição
     */
    public function setDesc($desc) {
        $this->desc = $desc;
    }

    /**
     * Define a lista de destinos para o ramal.
     *
     * Não há uma definição direta para que haja validação nos itens.
     *
     * @param array $list
     */
    public function setDstList($list) {
        $this->dstClean();
        foreach ($list as $dst) {
            $this->addDst($dst);
        }
    }

    /**
     * Seta o id da regra
     *
     * Define um numero de identificação para a regra.
     *
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Define uma prioridade para a regra
     *
     * @param int $prio
     */
    public function setPriority($prio) {
        $this->priority = $prio;
    }

    /**
     * Define o nome da aplicação que será executada para iniciar a gravação
     * das ligações.
     *
     * O parametro options será repassado como parametro para a aplicação de
     * gravação, pode, e será na maioria dos casos, um array contendo o nome
     * do arquivo de gravação e as flags (opções) para execução. ex:
     *
     * <code>
     * $recordApp = 'MixMonitor'
     * $options = array(
     *     "/tmp/filename.wav",
     *     "b"
     * );
     * </code>
     *
     * @param string $recordApp Application name
     * @param string $options Opções da applicação
     */
    public function setRecordApp($recordApp, $options) {
        $this->recordApp = array(
            "application" => $recordApp,
            "options" => $options
        );
    }

    /**
     * Requisição de conexão para ser usado na execução da regra.
     *
     * @param Asterisk_AGI_Request $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * Define a lista de origens para o ramal.
     *
     * Não há uma definição direta para que haja validação nos itens.
     *
     * @param array $list
     */
    public function setSrcList($list) {
        $this->srcClean();
        foreach ($list as $src) {
            $this->addSrc($src);
        }
    }

    /**
     * Limpa o array de origens
     */
    public function srcClean() {
        $this->src = array();
    }

    /**
     * Retorna um string imprimivel dessa regra
     */
    public function  __toString() {
        return $this->desc;
    }
}
