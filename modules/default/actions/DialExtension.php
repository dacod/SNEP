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
 * Dials to an Extension
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class DialExtension extends PBX_Rule_Action {

    /**
     * Dial flags
     * @var string $dial_flags parameters passed to asterisk Dial app.
     */
    private $dial_flags;

    /**
     * Consider timeout after X seconds of ringing.
     *
     * @var int $dial_timeout Timeout in seconds
     */
    private $dial_timeout;

    /**
     * Time limit for the call after answer.
     *
     * @var int $dial_limit Limit in milliseconds
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

    /**Tempo limite da ligação
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
     * @return string
     */
    public function getName() {
        return $this->i18n->translate("Dial Extension");
    }

    /**
     * @return string
     */
    public function getVersion() {
        return SNEP_VERSION;
    }

    /**
     * @return string
     */
    public function getDesc() {
        return $this->i18n->translate("Dial to a snep extension.");
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
        <unit>{$i18n->translate("in seconds")}</unit>
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
        <label>{$i18n->translate("Differ ring")}</label>
        $diff_ring
    </boolean>
    <boolean>
        <id>allow_voicemail</id>
        <default>false</default>
        <label>{$i18n->translate("Allow voicemail")}</label>
        $allow_voicemail
    </boolean>
    <boolean>
        <id>dont_overflow</id>
        <default>false</default>
        <label>{$i18n->translate("Do not overflow (busy and no answer)")}</label>
        $dont_overflow
    </boolean>
</params>
XML;
    }

    /**
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
        <unit>{$i18n->translate("in seconds")}</unit>
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
            $log->info("Extension $ramal have do not disturb enabled.");
        }
        else if($ramal->getFollowMe() != null) {
            $log->info("Follow-me, trying to find: " . $ramal->getFollowMe());
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
                    throw new PBX_Rule_Action_Exception_StopExecution("End of call detected");
                    break;
                case 'CHANUNAVAIL':
                    $log->warn($dialstatus['data'] . " dialing to extension $ramal");
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
                $log->info("Executing voicemail to extension $ramal due to {$dialstatus['data']}");
                $vm_params = array(
                    $ramal->getMailBox(),
                    "u"
                );
                $asterisk->exec('voicemail', $vm_params);
                throw new PBX_Rule_Action_Exception_StopExecution("End of call");
            }

            switch($dialstatus['data']) {
                case 'ANSWER':
                case 'CANCEL':
                    throw new PBX_Rule_Action_Exception_StopExecution("End of call");
                    break;
                case 'NOANSWER':
                case 'BUSY':
                    if( $this->dont_overflow ) {
                        throw new PBX_Rule_Action_Exception_StopExecution("End of call");
                    }
                    break;
                default:
                    $log->err($dialstatus['data'] . " ao discar para $request->destino");
            }
        }
    }
}
