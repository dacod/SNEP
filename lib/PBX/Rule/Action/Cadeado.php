<?php

/**
 * Pede a senha do usuário ou outra senha.
 *
 * Caso uma senha manual não tenha sido definida a senha será pedida somente se
 * o objeto da requisição for da classe Snep_Usuario.
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_Cadeado extends PBX_Rule_Action {

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
        if(isset($config['tipo']) && $config['tipo'] == "ramal")
            unset($config['senha']);

        parent::setConfig($config);
    }

    /**
     * Retorna o nome da Ação. Geralmente o nome da classe.
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Cadeado");
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
        return $this->i18n->translate("Trava a execução de acoes posteriores por senha.");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $i18n  = $this->i18n;
        $tipo  = (isset($this->config['tipo']))?"<value>{$this->config['tipo']}</value>":"";
        $senha = (isset($this->config['senha']))?"<value>{$this->config['senha']}</value>":"";

        $lbl_radio = $i18n->translate("Usar:");
        $lbl_ramal = $i18n->translate("Senha do Ramal");
        $lbl_static = $i18n->translate("Senha Estática (digite abaixo):");
        return <<<XML
<params>
    <radio>
        <label>$lbl_radio</label>
        <id>tipo</id>
        <default>ramal</default>
        $tipo
        <option>
            <label>$lbl_ramal</label>
            <value>ramal</value>
        </option>
        <option>
            <label>$lbl_static</label>
            <value>static</value>
        </option>
    </radio>
    <password>
        <id>senha</id>
        <depends>tipo.static</depends>
        $senha
    </password>
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

        $senha = "";
        if((!isset($this->config['senha']) || isset($this->config['senha']) && $this->config['senha'] == "") && $request->getSrcObj() instanceof Snep_Usuario) {
            $senha = $request->getSrcObj()->senha;
        }
        else if(isset($this->config['senha']) && $this->config['senha'] != ""){
            $senha = $this->config['senha'];
        }
        else {
            $log->warn("Impossivel determinar qual senha usar para a regra");
            return;
        }

        $auth = $asterisk->exec('AUTHENTICATE', array($senha,'',strlen((string)$senha)));
        if($auth['result'] == -1)
            throw new PBX_Exception_AuthFail();
    }
}

?>
