<?php

/**
 * Classe para coleta de informa��es sobre o asterisk.
 * Essa classe tem a inten��o de ser uma classe de compactibilidade para o
 * snep ou seja, ainda precisa ser reescrito algum c�digo de outras classes
 * para uma melhor utiliza��o do c�digo, isso inclui a classe
 * AGI_AsteriskManager que precisa ser adaptada para Singleton
 */
class AsteriskInfo {
    /**
     * Objeto para coleta de informa��es via AMI. (Singleton)
     * @var AGI_AsteriskManager $asterisk
     */
    private static $asterisk;

    /**
     * Construtor do objeto
     */
    public function AsteriskInfo() {
        global $SETUP, $LANG;
        // Verificando se j� existe alguma conex�o
        if(!isset(self::$asterisk)) {
            // Criando a primeira instancia da conex�o
            self::$asterisk = PBX_Asterisk_AMI::getInstance();
        }
    }

    /**
     * M�todo para compactibilidade com c�digo do snep, aqui h� uma melhoria
     * nas consultas simples reutilizando socket onde � poss�vel.
     *
     * @param string $comando - Comando a ser executado
     *
     * @param string $quebra - Para retornar somente as linhas que contenham
     * o conteudo dessa vari�vel
     *
     * @param boolean $tudo - Esse parametro n�o � usado (?!)
     *
     * @return Dados da consulta
     */
    public function status_asterisk($comando, $quebra,  $tudo=False) {
        if($comando != "Agents" && $comando != "Status") {
            $cmd = self::$asterisk->command($comando);

            $retorno = $cmd['data'];
            if ($quebra != "") {
                $ret_quebrado = " ";

                foreach(explode("\n", $cmd['data']) as $line) {
                    if (ereg($quebra, $line)) {
                        $ret_quebrado .= $line;
                        break;
                    }
                }
                return $ret_quebrado;
            }
            else {
                return $cmd['data'];
            }
        }
        else {
            // Enviando requisi��o de status
            self::$asterisk->send_request($comando, array());
            // Enviando esse objeto para cuidar dos responses
            self::$asterisk->wait_event($this);

            return $this->return;
        }
    }

    /**
     * @var String que guardara o retorno do que for processado no metodo
     * processEvent()
     */
    private $return;

    /**
     * Metodo que faz o tratamento dos eventos Agent, n�o deveria existir
     * j� que essas informa��es deveriam vir em respostas e n�o em events.
     * Mas como isso n�o est� nas minhas m�os essa "gambiarra"(minha opini�o)
     * teve que ser implementada.
     * @param $event evento que esta sendo processado
     * @param $param parametros que vieram com o evento
     * @return boolean - true se o processamento de eventos deve parar.
     */
    function processEvent($event, $param) {
        if($event == "agents" || $event == "status") {
            foreach($param as $key => $value) {
                $this->return .= "$key: $value\r\n";
            }
            $this->return .= "\r\n\r\n";
        }
        else if($event == "agentscomplete" || $event == "statuscomplete")
            return true;
    }
}
