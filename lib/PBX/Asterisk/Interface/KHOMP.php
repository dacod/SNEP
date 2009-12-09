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
 * Interface Khomp
 *
 * Representação de uma Interface Khomp do asterisk dentro da estrutura do snep.
 *
 * @category  Snep
 * @package   PBX_Asterisk
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Asterisk_Interface_KHOMP extends PBX_Asterisk_Interface {

    /**
     * Construtor da classe
     * @param array $config Array de configurações da interface
     */
    public function __construct($config) {
        $this->tech = 'KHOMP';
        $this->config = $config;
    }

    /**
     * Devolve o canal que identifica essa interface no asterisk.
     *
     * Usado para discagem e pesquisa. Para interfaces khomp o canal é geralmente:
     * KHOMP/bXcY
     *
     * onde X é o numero da placa e Y o canal dela
     *
     * O canal é opcional, já que se pode ligar tanto para placa como para links.
     * Lembrando que links fazem mais sentido sendo usados em E1's.
     *
     * @return string Canal
     */
    public function getCanal() {
        $link = 0;
        $channel = 0;

        if(isset($this->config['channel']) && $this->config['channel'] != "") {
            if(isset($this->config['link']) && $this->config['link'] != "")
                $channel = $this->config['link'] * 30;
            else
                $link = "";
            $channel = "c" . ($channel + $this->config['channel']);
        }
        else if(isset($this->config['link']) && $this->config['link'] != "") {
            $link = "l" . $this->config['link'];
            $channel = "";
        }
        else {
            $channel = "";
            $link = "";
        }

        $canal = "KHOMP/b" . $this->config['board'] . $link . $channel;

        return $canal;
    }

    /**
     * Método que retorna expressão de identificação do canal no asterisk
     * para que se possa identificar ligações entrantes da interface.
     *
     * Em troncos IP esse será o mesmo que o canal de saída (getCanal).
     *
     * @return Expressão para identificação de chamadas
     */
    public function getIncomingChannel() {
        $channel = 0;

        if(isset($this->config['channel'])) {
            $channel = $this->config['channel'];
        }
        else if(isset($this->config['link'])) {
            // Caso seja um link
            if($this->config['link'] == 0)
                $channel = "[0-2]?[0-9]";
            else {
                $channel = "[3-5][0-9]";
            }
        }
        else {
            // Caso seja a placa toda
            $channel = "[0-9]?[0-9]";
        }

        $canal = "KHOMP/b" . $this->config['board'] . "c" . $channel . "$";

        return $canal;
    }
}
