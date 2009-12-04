<?php
/**
 * Setar Centro de Custos.
 *
 * Ação das regras do snep que define um centro de cusos para classificar a
 * ligação.
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Rule_Action_CCustos extends PBX_Rule_Action {

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
     * Retorna o nome da Ação. Geralmente o nome da classe.
     *
     * @return Nome da Ação
     */
    public function getName() {
        return $this->i18n->translate("Definir Centro de Custos");
    }

    /**
     * Retorna o numero da versão da classe.
     *
     * @return Versão da classe
     */
    public function getVersion() {
        return "1.0";
    }

    /**
     * Seta as configurações da ação.
     *
     * @param array $config configurações da ação
     */
    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * Retorna uma breve descrição de funcionamento da ação.
     * @return Descrição de funcionamento ou objetivo
     */
    public function getDesc() {
        return $this->i18n->translate("Define um centro de custos para classificação da ligação");
    }

    /**
     * Devolve um XML com as configurações requeridas pela ação
     * @return String XML
     */
    public function getConfig() {
        $ccustos = (isset($this->config['ccustos']))?"<value>{$this->config['ccustos']}</value>":"";

        return <<<XML
<params>
    <ccustos>
        <id>ccustos</id>
        $ccustos
    </ccustos>
</params>
XML;
    }

    /**
     * Executa a ação. É chamado dentro de uma instancia usando AGI.
     *
     * @param AGI $asterisk
     * @param int $rule - A regra que chamou essa ação. É passado pra que
     * a ação possa restaurar as configurações dela para essa regra. Esse parametro
     * á opcional.
     */
    public function execute($asterisk, $request) {
        $log = Zend_Registry::get('log');

        $log->info("Definindo centro de custos para {$this->config['ccustos']}.");
        $asterisk->set_variable('CDR(accountcode)', $this->config['ccustos']);
    }
}
?>
