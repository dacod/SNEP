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
 * Classe que manipular os arquivos de log agi.log
 *
 * @see Snep_Log
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Log {

    private $log;
    private $tail;
    private $dia_ini;
    private $dia_fim;
    private $hora_ini;
    private $hora_fim;

    // Contrutor da classe - Faz a leitura do arquivo de log.
    public function __construct($log, $arq) {

        $arquivo = $log . '/' . $arq;

        if (file_exists($arquivo)) {
            $this->log = file_get_contents($arquivo);
        } else {
            return 'error';
        }
    }

    public function returnLog() {
        return $this->log;
    }

    // Função para extrair um relatório conforme parametros passados.
    public function getLog($dia_ini, $dia_fim, $hora_ini, $hora_fim, $st, $src, $dst) {
        $dia_ini = new Zend_Date($dia_ini);
        $dia_fim = new Zend_Date($dia_fim);
        $this->hora_ini = $hora_ini;
        $this->hora_fim = $hora_fim;
        $this->status = $st;
        $this->src = $src;
        $this->dst = $dst;

        $this->log = explode("\n", $this->log);

        $result = array();
        $tsIni = strtotime($dia_ini->toString('MM/dd/yyyy hh:mm'));
        $tsFim = strtotime($dia_fim->toString('MM/dd/yyyy hh:mm'));
        foreach ($this->log as $valor) {
            $timeValorTmp = explode("T", substr($valor, 0, 16));
            try {
                $timeValor = $timeValorTmp[0] . ' ' . $timeValorTmp[1];
                $tsValue = strtotime($timeValor);
                if ($tsValue >= $tsIni && $tsValue <= $tsFim) {
                    foreach ($this->status as $status) {

                        if ($status == 'ALL') {
                            $result[] = $valor;
                        } else {
                            switch (strpos($valor, $status)) {
                                case true:
                                    $result[] = $valor;
                                    break;

                                case false:
                                    break;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                
            }
        }

        // tratamento de origens e destinos

        $filtro = array();
        foreach ($result as $filter) {
            $put = true;
            // src
            if ($this->src == '') {
               $put = true;
            } else {
                $filtrosrc = trim(substr($filter, 28, strpos($filter, "->") - 30));
                if ($this->src == $filtrosrc) {
                    $put = true;
                }
                else{
                    $put = false;
                }
            }


            // dst
            if ($this->dst == '' && $put) {
               $put = true;
            } else {
                $tmp = substr($filter, strpos($filter, "->") + 2);
                $tmp2 = explode(" ", $tmp);
                $filtrodst = trim($tmp2[1]);
                if ($this->dst == $filtrodst && $put) {
                    $put = true;
                }
                else{
                    $put = false;
                }
            }
            
            if($put)
                $filtro[] = $filter;
        }

        return $filtro;
    }

    // Função que extraí do arquivo as ultimas linhas conforme passado por parametro.
    public function getTail($n) {

        $n = ( $n ? (int) $n : 30 );
        $lines = explode("\n", self::returnLog());

        $linhas = count($lines);
        $reverso = array_reverse($lines);

        unset($lines);
        $tail = array();

        for ($i = 0; $i < $n; $i++) {
            $tail[] = $reverso[$i];
        }

        return implode("<br />", array_reverse($tail));
    }

}
