<?php

/**
 * Reescrever Origem/Destino de uma ligação.
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_Rewrite extends PBX_Rule_Action {

    /**
     * @var Internacionalização
     */
    private $i18n;

    /**
     * Construtor
     * @param array $config configurações da ação
     */
    public function __construct() {
        $path = Zend_Registry::get('config');
        // Especificando caminho para arquivo de tradução
        $this->i18n = new Zend_Translate('gettext', $path->system->path->base . "/lang/actions/" . get_class($this) . "/" . "pt_BR.mo" , 'pt_BR');
    }

    /**
     * Define as configurações da ação
     * @param array $config
     */
    public function setConfig($config) {
        parent::setConfig($config);
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Reescrever Requisição");
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
        return $this->i18n->translate("Reescreve Origem/Destino da ligação.");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $i18n = $this->i18n;
        $type    = (isset($this->config['type']))?"<value>{$this->config['type']}</value>":"";
        $replace = (isset($this->config['replace']))?"<value>{$this->config['replace']}</value>":"";
        $prefix  = (isset($this->config['prefix']))?"<value>{$this->config['prefix']}</value>":"";
        $sufix   = (isset($this->config['sufix']))?"<value>{$this->config['sufix']}</value>":"";
        return <<<XML
<params>
    <radio>
        <id>type</id>
        <label>{$i18n->translate("Tipo de edição")}</label>
        <default>dst</default>
        $type
        <option>
            <label>{$i18n->translate("Origem")}</label>
            <value>src</value>
        </option>
        <option>
            <label>{$i18n->translate("Destino")}</label>
            <value>dst</value>
        </option>
    </radio>
    <radio>
        <id>cut</id>
        <label>{$i18n->translate("Corte")}</label>
        <default>nocut</default>
        $cut
        <option>
            <label>{$i18n->translate("Não cortar")}</label>
            <value>nocut</value>
        </option>
        <option>
            <label>{$i18n->translate("Cortar no pipe '|'")}</label>
            <value>pipecut</value>
        </option>
    </radio>
    <string>
        <id>replace</id>
        <label>{$i18n->translate("Substituir por")}</label>
        <size>10</size>
        $replace
    </string>
    <string>
        <id>prefix</id>
        <label>{$i18n->translate("Prefixo")}</label>
        <size>10</size>
        $prefix
    </string>
    <string>
        <id>sufix</id>
        <label>{$i18n->translate("Sufixo")}</label>
        <size>10</size>
        $sufix
    </string>
</params>
XML;
    }

    /**
     * Executa a ação
     *
     * @param Asterisk_AGI $asterisk
     * @param Asterisk_AGI_Request $request
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');
        $i18n = $this->i18n;

        $num = isset($this->config['type']) && $this->config['type'] == 'src' ? $request->origem : $request->destino;

        // Cortando numero
        if(isset($this->config['cut']) && $this->config['cut'] == 'pipecut') {
            if(!is_null($this->getRule())) {
                $expr = $this->config['type'] == 'src' ? $this->getRule()->getValidSrcExpr($num) : $this->getRule()->getValidDstExpr($num);
                if($expr['type'] == 'RX') {
                    // Removendo da contagem as expressoes como [13-6] e removendo a contagem do _
                    $cut_inst = strpos(ereg_replace("\[[0-9\-]*\]", "#", $expr['value']), "|");
                    if($cut_inst > 0) { // caso haja algo para cortar
                        // Cortando
                        $num = substr($num, $cut_inst);
                    }
                }
                else {
                    $log->debug(sprintf($i18n->translate("Tipo de expressao que casa com essa regra nao permite corte por |, esta casa com: %s"), $expr['type'] . '-' . $expr['value']));
                }
            }
            else {
                $log->err($i18n->translate("Erro ao processar instrucao para corte em |, impossivel encontrar Regra que executa esta acao."));
            }
        }

        // Adicionando prefixo
        if(isset($this->config['prefix'])) {
            $num = $this->config['prefix'] . $num;
        }

        // Adicionando sufixo
        if(isset($this->config['sufix'])) {
            $num .= $this->config['sufix'];
        }

        // Reescrevendo numero
        if(isset($this->config['replace']) && $this->config['replace'] != '') {
            $num = $this->config['replace'];
        }

        // Aplicando modificações no callerid/extension do asterisk
        if(isset($this->config['type']) && $this->config['type'] == 'src') {
            $log->info(sprintf($i18n->translate("Reescrevendo origem para %s"), $num));
            $request->origem = $num;
            $asterisk->set_callerid($num);
        }
        else {
            $log->info(sprintf($i18n->translate("Reescrevendo destino para %s"), $num));
            $request->destino = $num;
            $asterisk->set_extension($request->destino);
        }
    }
}
