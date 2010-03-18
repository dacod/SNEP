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
 * Classe que manipular informações do envio e recebimento de Fax.
 *
 * @see PBX_Fax
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Bozzetti <rafael@opens.com.br>
 *
 */
class PBX_Fax {

    private $rec_list;
    private $rec_path;
    private $env_list;
    private $env_path;
    private $enviado;
    private $recebido;
    private $totrec;
    private $totenv;

    // Contrutor da classe. Relaciona arquivos com datas para futura pesquisa.
    public function __construct($sended, $received) {
        $config = Zend_Registry::get('config');
        $this->rec_path = $config->system->path->hylafax . "/recvq";
        $this->env_path = $config->system->path->hylafax . "/sendq";

        $rec = exec("ls -lah --time-style=long-iso $this->rec_path > /tmp/recvq");
        $arrRec = explode("\n",ereg_replace( ' +', ' ', file_get_contents("/tmp/recvq")));

        $env = exec("ls -lah --time-style=long-iso $this->env_path > /tmp/sendq");
        $arrEnv = explode("\n",ereg_replace( ' +', ' ', file_get_contents("/tmp/sendq")));

        // Percorre resultados da listagem da pasta enviados
        if($sended) {
            $this->rec_list = array();
            foreach($arrRec as $reg => $val) {
                $sa = explode(" ", $val);
                if($sa[7] != "." && $sa[7] != ".." && count($sa) > 5 && $sa[7] != 'seqf') {
                    $this->rec_list[] = $sa;
                }
                unset($sa);
            }
            $this->enviado = 1;
        }
        // Percorre resultados da listagem da pasta recebidos
        if($received) {
            $this->env_list = array();
            foreach($arrEnv as $reg => $val) {
                $sa = explode(" ", $val);
                if($sa[7] != "." && $sa[7] != ".." && count($sa) > 5 && $sa[7] != 'seqf') {
                    $this->env_list[] = $sa;
                }
                unset($sa);
            }
            $this->recebido = 1;
        }        
    }

    // Função que retorna um determinado periodo de pesquisa.
    public function getPeriodo($dia_ini, $dia_fim, $hora_ini, $hora_fim) {
        // Dia Inicial
        $diai = substr($dia_ini, 0, 2);
        $mesi = substr($dia_ini, 3, 2);
        $anoi = substr($dia_ini, 6, 4);
        // Dia Final
        $diaf = substr($dia_fim, 0, 2);
        $mesf = substr($dia_fim, 3, 2);
        $anof = substr($dia_fim, 6, 4);
        // Hora Inicial
        $horai = substr($hora_ini, 0, 2);
        $mini = substr($hora_ini, 3, 2);
        // Hora Final
        $horaf = substr($hora_fim, 0, 2);
        $minf = substr($hora_fim, 3, 2);

        $retorno = array();

        if($this->enviado) {
            foreach($this->rec_list as $r => $x) {

            // Tratamento da data.
                if(substr($x[5], 0, 4) >= $anoi && substr($x[5], 0, 4) <= $anof) {
                    if(substr($x[5], 5, 2) >= $mesi && substr($x[5], 5, 2) <= $mesf) {
                        if(substr($x[5], 8, 2) >= $diai && substr($x[5], 8, 2)  <= $diaf ) {
                        // Tratamento de horário.
                            if(substr($x[6], 0, 2) >= $horai && substr($x[6], 0, 2) <= $horaf) {
                                if(substr($x[6], 3, 2) >= $mini && substr($x[6], 3, 2) <= $minf) {
                                    $x[] =  'Recebido';
                                    $x[] =  'recvq/' . $x[7];
                                    $x['data'] = substr($x[5], 8, 2) . "-" . substr($x[5], 5,2 ) . "-" . substr($x[5], 0, 4) . " - " . $x[6];
                                    $retorno[] = $x;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->totrec = count($retorno);
        unset($this->rec_list);

        if($this->recebido) {
            foreach($this->env_list as $r => $x) {

                // Tratamento da data.
                if(substr($x[5], 0, 4) >= $anoi && substr($x[5], 0, 4) <= $anof) {
                    if(substr($x[5], 5, 2) >= $mesi && substr($x[5], 5, 2) <= $mesf) {
                        if(substr($x[5], 8, 2) >= $diai && substr($x[5], 8, 2)  <= $diaf ) {
                        // Tratamento de horário.
                            if(substr($x[6], 0, 2) >= $horai && substr($x[6], 0, 2) <= $horaf) {
                                if(substr($x[6], 3, 2) >= $mini && substr($x[6], 3, 2) <= $minf) {
                                    $x[] =  'Enviado';
                                    $x[] =  'sendq/' . $x[7];
                                    $x['data'] = substr($x[5], 8, 2) . "-" . substr($x[5], 5,2 ) . "-" . substr($x[5], 0, 4) . " - " . $x[6];
                                    $retorno[] = $x;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->totenv = count($retorno) - $this->totrec;
        unset($this->env_list);
        return $retorno;
    }

    // Calcula os totalizadores.
    public function getTot() {
        $result = array(
            'recebidas' => $this->totrec,
            'enviadas'  => $this->totenv );
        return $result;
    }
}
