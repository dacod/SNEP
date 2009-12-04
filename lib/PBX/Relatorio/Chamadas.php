<?php

/**
 * Controle lógico do relatório de chamadas do SNEP.
 *
 * Este relatório é, em inglês conhecido como CDR (Call Detail Record)
 *
 * @TODO Adaptar o relatório para um real uso em todo o sistema. Otimizar SQL's
 * e tratamentos de resultados para as diferentes midias (CSV, Tela, PDF)
 *
 * @category  Snep
 * @package   PBX_Rule_Action
 * @copyright Copyright (c) 2009 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Relatorio_Chamadas {

    /**
     * Define se o tempo será usado como continuo ou diário.
     *
     * Exemplo em contínuo:
     *  de 21/08/2009 as 18:00:00 até 22/08/2009 as 18:00
     * Todas as ligações entre esses horários, Incluindo a noite de 21 para 22.
     *
     * Modo não contínuo:
     *  de 21/08/2009 até 22/08/2009 das 08:00 as 18:00
     * Todas as ligações desses dois dias que foram entre as 8 até as 18.
     *
     * @var boolean
     */
    protected $continuousTime;

    /**
     * Data para início do relatório
     *
     * @var timestamp startDate
     */
    protected $startDate;

    /**
     * Data para final do relatório
     *
     * @var timestamp endDate
     */
    protected $endDate;

    protected $startTime;

    protected $endTime;

    /**
     * Construtor
     */
    public function __construct() {
        $this->startDate = strtotime("-1 day");
        $this->endDate = strtotime("now");

        $this->startTime = date("H:i:s", strtotime($this->endDate));
        $this->endTime   = date("H:i:s", strtotime($this->endDate . " -1 second"));

        $this->continuousTime = true;
    }

    /**
     * Gera e imprime o relatório na tela em formato CSV.
     *
     * Este metodo foi gerado para que possa ser gerado relatórios grandes sem
     * estourar o limite de memoria de um script. Os dados são tratados conforme
     * vem do buffer do mysql usando o fetch() e não o fetchAll();
     *
     * Nota: O limite de *tempo* do script ainda pode ser estourado e depende
     * de configuração no php.ini ou .htacesss.
     */
    public function printCsv() {
        $db = Zend_Registry::get('db');

        $select = $this->prepare();
        $stmt = $db->query($select);

        while ($call = $stmt->fetch()) {
            printf("%s,%s,%s,%s,%s,%d,%d,%s\n",
                $call['data'],
                $call['hora'],
                $call['tipo'],
                $call['src'],
                $call['dst'],
                $call['duration'],
                $call['billsec'],
                $call['userfield'] . ".wav"
            );
        }
    }

    /**
     * Gera e armazena o relatório em arquivo formato CSV.
     *
     * Este metodo foi gerado para que possa ser gerado relatórios grandes sem
     * estourar o limite de memoria de um script. Os dados são tratados conforme
     * vem do buffer do mysql usando o fetch() e não o fetchAll();
     *
     * Nota: O limite de *tempo* do script ainda pode ser estourado e depende
     * de configuração no php.ini ou .htacesss.
     *
     * @param string $file caminho para arquivo
     */
    public function printCsvToFile($file) {
        if(!file_exists($file)) {
            touch($file);
        }
        else if(!is_writable($file)) {
            throw new PBX_Exception_IO("Nao e possivel escrever no arquivo $file, verifique permissoes.");
        }
        $db = Zend_Registry::get('db');

        $select = $this->prepare();
        $stmt = $db->query($select);

        while ($call = $stmt->fetch()) {
            $line = sprintf("%s,%s,%s,%s,%s,%d,%d,%s\n",
                $call['data'],
                $call['hora'],
                $call['tipo'],
                $call['src'],
                $call['dst'],
                $call['duration'],
                $call['billsec'],
                $call['userfield'] . ".wav"
            );
            file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Retorna a data definida para final do periodo do relatório
     *
     * @return timestamp endDate
     */
    public function getEndDate() {
        return $this->endDate;
    }

    public function getEndTime() {
        return $this->endTime;
    }

    /**
     * Retorna a data definida para início do relatório
     *
     * @return timestamp startDate
     */
    public function getStartDate() {
        return $this->startDate;
    }

    public function getStartTime() {
        return $this->startTime;
    }

    /**
     * Retorna se o relatório será feito usando tempo continuo ou hora diária.
     *
     * @return boolean continuous
     */
    public function isContinuous() {
        return $this->continuousTime;
    }

    /**
     * Prepara o sql para seleção dos dados no banco
     *
     * @return Zend_Db_Select $select para os dados
     */
    protected function prepare() {
        $db = Zend_Registry::get('db');

        // Select base
        $select = $db->select()->from(
            "cdr",
            array(
                "data" => 'date_format(calldate,"%d/%m/%Y")',
                "hora" => 'date_format(calldate,"%T")',
                "src",
                "dst",
                "duration",
                "billsec",
                "userfield"
            )
        );

        // Juntando tipo de ligação vinda do centro de custos
        $select->join('ccustos', 'cdr.accountcode = ccustos.codigo', array('tipo'));

        // Condicional de data
        if($this->isContinuous()) {
            $select->where("calldate BETWEEN '" . date("Y-m-d", $this->startDate) . " $this->startTime' AND '" . date("Y-m-d", $this->endDate) . " $this->endTime'");
        }
        else {
            $select->where("calldate BETWEEN DATE('" . date("Y-m-d", $this->startDate) . "') AND DATE(ADDDATE('" . date("Y-m-d", $this->endDate) . "', 1)) AND DATE_FORMAT(calldate,'%T') BETWEEN '" . $this->startTime . "' AND '" . $this->endTime . "'");
        }
        
        // Eliminando ligações sem histórico
        $select->where("duration > 0");

        // Eliminando features
        $select->where("dst not like '*%'");

        // Elimintando dsts específicas
        $config = Zend_Registry::get("config");
        $dsts = "dst NOT IN (";
        foreach (explode(";", $config->ambiente->dst_exceptions) as $badDst) {
            $dsts .= "'$badDst',";
        }
        $dsts .= "'')";
        $select->where($dsts);

        return $select;
    }

    /**
     * Define que o tempo será continuo entre as datas
     */
    public function setContinuous() {
        $this->continuousTime = true;
    }

    /**
     * Faz o controle de horários de forma diária e não continua.
     */
    public function setDaily() {
        $this->continuousTime = false;
    }

    /**
     * Define umadata para fim do relatório
     *
     * @param timestamp $endDate
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    public function setEndTime($endTime) {
        $this->endTime = $endTime;
    }

    public function setStartTime($startTime) {
        $this->startTime = $startTime;
    }
    
    /**
     * Define umadata para início do relatório
     *
     * @param timestamp $startDate
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

}