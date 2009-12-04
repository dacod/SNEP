<?php

/**
 * Restaura a Origem/Destino de uma ligação.
 *
 * @see PBX_Rule
 * @see PBX_Rule_Action
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_Restore extends PBX_Rule_Action {

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
        return $this->i18n->translate("Restaurar Requisição");
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
        return $this->i18n->translate("Restaura a origem ou destino da ligação.");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $i18n = $this->i18n;
        $origem  = (isset($this->config['origem']))?"<value>{$this->config['origem']}</value>":"";
        $destino = (isset($this->config['destino']))?"<value>{$this->config['destino']}</value>":"";
        return <<<XML
<params>
    <boolean>
        <id>origem</id>
        <label>{$i18n->translate("Restaurar Origem")}</label>
        <default>false</default>
        $origem
    </boolean>
    <boolean>
        <id>destino</id>
        <label>{$i18n->translate("Restaurar Destino")}</label>
        <default>false</default>
        $destino
    </boolean>
</params>
XML;
    }

    /**
     * Executa a ação.
     * @param Asterisk_AGI $asterisk
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');
        $i18n = $this->i18n;
        if(isset($this->config['origem']) && $this->config['origem']) {
            $log->info(sprintf($i18n->translate("Restaurando origem para %s"), $request->getOriginalCallerid()));
            $request->origem = $request->getOriginalCallerid();
            $asterisk->set_callerid($request->origem);
        }
        if(isset($this->config['destino']) && $this->config['destino']) {
            $log->info(sprintf($i18n->translate("Restaurando destino para %s"), $request->getOriginalExtension()));
            $request->destino = $request->getOriginalExtension();
        }
    }
}

?>
