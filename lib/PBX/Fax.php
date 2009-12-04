<?php
/**
 * Classe que manipular informações do envio e recebimento de Fax.
 *
 * @see PBX_Fax
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author    Rafael Bozzetti <rafael@opens.com.br>
 *
 */
class PBX_Fax {

    private $rec_list;
    private $rec_path = '/var/log/hylafax/recvq';
    private $env_list;
    private $env_path = '/var/log/hylafax/sendq';
    private $enviado;
    private $recebido;
    private $totrec;
    private $totenv;

    // Contrutor da classe. Relaciona arquivos com datas para futura pesquisa.
    public function __construct($sended, $received) {
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
?>