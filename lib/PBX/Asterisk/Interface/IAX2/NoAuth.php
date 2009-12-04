<?php
/**
 * Interface IAX2
 *
 * Representação de uma Interface IAX2 do asterisk dentro da estrutura do snep.
 *
 * Interface de saída sem autenticação
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_Interface_IAX2_NoAuth extends PBX_Asterisk_Interface_IAX2 {
    /**
     * Construtor da classe
     * @param array $config Array de configurações da interface
     */
    public function __construct($config) {
        $this->tech = 'IAX2';
        $this->config = $config;
    }

    /**
     * Eliminando uso de getCanal para essa interface.
     */
    public function getCanal() {
        throw new Exception(get_class($this) . ' dont support channel returning, use $obj->getTech()/$your_exten@$obj->getHost() to dial');
    }

    /**
     * Devolve a configuração de hostname da interface.
     *
     * @return string host
     */
    public function getHost() {
        return $this->config['host'];
    }
}
?>