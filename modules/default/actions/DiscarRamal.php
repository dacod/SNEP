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
 * Discar para Ramal
 *
 * Ação snep para Regras de Negócio que disca para ramais internos.
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class DiscarRamal extends PBX_Rule_Action {

    /**
     * Parametros do Dial do asterisk
     * @var string $dial_flags parametros da app_dial do asterisk
     */
    private $dial_flags;

    /**
     * @var int $dial_timeout Tempo limite de chamada (ring)
     */
    private $dial_timeout;

    /**
     * @var int $dial_limit Tempo limite da ligação
     */
    private $dial_limit;

    /**
     * @var int $dial_limit_warn Tempo restante da ligação para que o alerta
     * de que o limite está próximo seja disparado.
     */
    private $dial_limit_warn;

    /**
     * @var boolean $diff_ring Se deve-se tentar usar toque diferenciado
     */
    private $diff_ring;

    /**
     * @var string $ramal para discar, vazio significa usar o destino padrão da
     * requisição.
     */
    private $ramal;


    /**
     * Determina se haverá ou não transbordo em não atende e ocupado
     * 
     * @var boolean dont_overflow
     */
    private $dont_overflow;

    /**
     * @var Internacionalização
     */
    private $i18n;

    /**
     * Construtor
     * @param array $config configurações da ação
     */
    public function __construct() {
        $this->i18n = Zend_Registry::get("i18n");
    }

    /**
     * Define as configurações da ação
     * @param array $config
     */
    public function setConfig($config) {
        parent::setConfig($config);

        $default_dial_timeout = isset($this->defaultConfig['dial_timeout']) ? $this->defaultConfig['dial_timeout'] : 60;

        // inicializando parametros opcionais
        $this->dial_flags      = ( isset($config['dial_flags']) )   ? $config['dial_flags'] : "";
        $this->dial_timeout    = ( isset($config['dial_timeout']) ) ? $config['dial_timeout'] : $default_dial_timeout;
        $this->dial_limit      = ( isset($config['dial_limit']) )   ? $config['dial_limit'] : "0";
        $this->dial_limit_warn = ( isset($config['dial_limit_warn']) ) ? $config['dial_limit_warn'] : "0";
        $this->diff_ring       = ( isset($config['diff_ring']) && $config['diff_ring'] == 'true') ? true:false;
        $this->allow_voicemail = ( isset($config['allow_voicemail']) && $config['allow_voicemail'] == 'true') ? true:false;
        $this->dont_overflow   = ( isset($config['dont_overflow']) && $config['dont_overflow'] == 'true') ? true:false;
        $this->ramal           = ( isset($config['ramal']) && $config['ramal'] != "" ) ? PBX_Usuarios::get($config['ramal']) : "";
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Discar para Ramal");
    }

    /**
     * Retorna o numero da versão da classe.
     * @return Versão da classe
     */
    public function getVersion() {
        return "1.0";
    }

    /**
     * Retorna uma breve descrição de funcionamento da ação.
     * @return Descrição de funcionamento ou objetivo
     */
    public function getDesc() {
        return $this->i18n->translate("Disca para um ramal cadastrado no banco de dados do SNEP");
    }

    /**
     * Retorna um array com as configurações da ação para persistencia em banco
     * de dados.
     *
     * @return array $config Configurações para persistencia.
     */
    public function getConfigArray() {
        $config = array(
            "dial_flags"      => $this->dial_flags == null ? 'twk' : $this->dial_flags,
            "dial_timeout"    => $this->dial_timeout == null ? '60' : $this->dial_timeout,
            "diff_ring"       => $this->diff_ring ? 'true' : 'false',
            "dont_overflow"   => $this->dont_overflow ? 'true' : 'false',
            "allow_voicemail" => $this->allow_voicemail ? 'true' : 'false'
        );

        if( $this->ramal != "" ) {
            $config["ramal"] = $this->ramal;
        }

        return $config;
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $i18n = $this->i18n;
        /* Descomente para geração de tradução. Parser ruim do poedit
        $i18n->translate("Dial Flags");
        $i18n->translate("Dial Timeout");
        $i18n->translate("segundos");
         */
        $ramal           = (isset($this->config['ramal']))?"<value>{$this->config['ramal']}</value>":"";
        $dial_timeout    = (isset($this->config['dial_timeout']))?"<value>{$this->config['dial_timeout']}</value>":"";
        $dial_flags      = (isset($this->config['dial_flags']))?"<value>{$this->config['dial_flags']}</value>":"";
        $diff_ring       = (isset($this->config['diff_ring']))?"<value>{$this->config['diff_ring']}</value>":"";
        $allow_voicemail = (isset($this->config['allow_voicemail']))?"<value>{$this->config['allow_voicemail']}</value>":"";
        $dont_overflow   = (isset($this->config['dont_overflow']))?"<value>{$this->config['dont_overflow']}</value>":"";

        $default_dial_timeout = isset($this->defaultConfig['dial_timeout']) ? $this->defaultConfig['dial_timeout'] : 60;

        return <<<XML
<params>
    <ramal>
        <id>ramal</id>
        <description>Deixe em branco para usar o numero vindo do destino.</description>
        $ramal
    </ramal>
    <int>
        <id>dial_timeout</id>
        <default>$default_dial_timeout</default>
        <label>{$i18n->translate("Dial Timeout")}</label>
        <unit>{$i18n->translate("segundos")}</unit>
        <size>2</size>
        $dial_timeout
    </int>
    <string>
        <id>dial_flags</id>
        <default>twk</default>
        <label>{$i18n->translate("Dial Flags")}</label>
        <size>10</size>
        $dial_flags
    </string>
    <boolean>
        <id>diff_ring</id>
        <default>false</default>
        <label>{$i18n->translate("Diferenciar toque")}</label>
        $diff_ring
    </boolean>
    <boolean>
        <id>allow_voicemail</id>
        <default>false</default>
        <label>{$i18n->translate("Permitir Voicemail")}</label>
        $allow_voicemail
    </boolean>
    <boolean>
        <id>dont_overflow</id>
        <default>false</default>
        <label>{$i18n->translate("Não Transbordar (ocupado e não atende)")}</label>
        $dont_overflow
    </boolean>
</params>
XML;
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
        $i18n = $this->i18n;

        $dial_timeout = isset($this->defaultConfig['dial_timeout']) ? $this->defaultConfig['dial_timeout'] : 60;

        return <<<XML
<params>
    <int>
        <id>dial_timeout</id>
        <default>$dial_timeout</default>
        <label>{$i18n->translate("Dial Timeout")}</label>
        <unit>{$i18n->translate("segundos")}</unit>
        <size>2</size>
    </int>
</params>
XML;
    }

    /**
     * Executa a ação.
     * @param Asterisk_AGI $asterisk
     * @param Asterisk_AGI_Request $request
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');

        try {
            if (!$this->ramal instanceof Snep_Exten) {
                $ramal = PBX_Usuarios::get($request->destino);
            }
            else {
                $ramal = $this->ramal;
            }
        }
        catch(PBX_Exception_NotFound $ex) {
            $log->info($ex->getMessage());
            return;
        }

        if($ramal->isDNDActive()) {
            $log->info("Ramal $ramal esta com nao perturbe habilitado.");
        }
        else if($ramal->getFollowMe() != null) {
            $log->info("Siga-me, tentando encontrar: " . $ramal->getFollowMe());
            $fake_request = $asterisk->request;
            $fake_request['agi_callerid'] = $ramal->getNumero();
            $fake_request['agi_extension'] = $ramal->getFollowMe();

            $request = new PBX_Asterisk_AGI_Request($fake_request);

            $dialplan = new PBX_Dialplan();
            $dialplan->setRequest($request);
            $dialplan->parse();

            $regra = $dialplan->getLastRule();

            $original_request = $asterisk->requestObj;
            $asterisk->requestObj = $request;
            $regra->setAsteriskInterface($asterisk);

            $regra->execute();

            $asterisk->requestObj = $original_request;

            $dialstatus = $asterisk->get_variable("DIALSTATUS");
            $log->debug("DIALSTATUS: " . $dialstatus['data']);

            switch($dialstatus['data']) {
                case 'ANSWER':
                case 'CANCEL':
                    throw new PBX_Rule_Action_Exception_StopExecution("Fim de ligacao detectado");
                    break;
                case 'CHANUNAVAIL':
                    $log->warn($dialstatus['data'] . " ao discar para o ramal $ramal");
            }
        }
        else {
            $canal = $ramal->getInterface()->getCanal();

            if($this->diff_ring) {
                if($ramal->getInterface()->getTech() == "SIP") {
                    $asterisk->exec('SIPAddHeader', 'Alert-Info: Bellcore-r3');
                }
                else if($ramal->getInterface()->getTech() == "KHOMP") {
                    $canal .= "/ring=400.200:ring_ext=400.2000";
                }
            }

            if($ramal->getPickupGroup() != null) {
                $asterisk->set_variable('__PICKUPMARK', $ramal->getPickupGroup());
            }

            $log->info("Discando para ramal $ramal no canal $canal.");
            $asterisk->exec_dial($canal,$this->dial_timeout,$this->dial_flags);

            $dialstatus = $asterisk->get_variable("DIALSTATUS");
            $log->debug("DIALSTATUS: " . $dialstatus['data']);

            if($dialstatus['data'] != "ANSWER" && $dialstatus['data'] != "CANCEL" && $this->allow_voicemail && $ramal->hasVoiceMail()) {
                $log->info("Executando voicemail para ramal $ramal devido a {$dialstatus['data']}");
                $vm_params = array(
                    $ramal->getMailBox(),
                    "u"
                );
                $asterisk->exec('voicemail', $vm_params);
                // Nada mais deve ser executado depois do voicemail
                throw new PBX_Rule_Action_Exception_StopExecution("Fim da ligacao");
            }

            switch($dialstatus['data']) {
                case 'ANSWER':
                case 'CANCEL':
                    throw new PBX_Rule_Action_Exception_StopExecution("Fim da ligacao");
                    break;
                case 'NOANSWER':
                case 'BUSY':
                    if( $this->dont_overflow ) {
                        throw new PBX_Rule_Action_Exception_StopExecution("Fim da ligacao");
                    }
                    break;
                default:
                    $log->err($dialstatus['data'] . " ao discar para $request->destino");
            }
        }
    }
}
