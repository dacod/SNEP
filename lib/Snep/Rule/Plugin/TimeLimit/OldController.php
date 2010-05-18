<?php
/**
 * Classe de objetos que fazem o controle do crédito de tempos para ramais e
 * troncos
 */
class Snep_Rule_Plugin_TimeLimit_OldController {
    /**
     * @var Se a ligação será permitida ou não
     */
    public $status = "allow";

    /**
     * @var Se o objeto está sobre controle
     */
    private $controlling = false;

    /**
     * @var Total de tempo
     */
    private $total = false;

    /**
     * @var ID para histórico
     */
    private $history_id;

    private $ownerid = "Nobody";

    /**
     * Atualiza as tabelas com o tempo passado no parametro $duration
     * @param $duration - call duration
     * @param $db - database interface. PDO object
     */
    public function update($duration, $db) {
        if($this->controlling) {
            $log = Zend_Registry::get('log');
            $log->debug("Contabilizando $duration segundos para $this->ownerid");
            try {
                $sql = "SELECT used FROM time_history WHERE id='$this->history_id'";
                $history = $db->query($sql)->fetchAll();

                $used = $history[0]['used'] + $duration;

                $sql = "UPDATE time_history SET used='$used', changed=NOW() WHERE id='$this->history_id'";
                $db->query($sql);
            } catch (Exception $e) {
                throw new Exception("Fatal Error while updating the time: " . $e->getMessage());
            }
        }
    }

    /**
     * Inicia o objeto
     * @param $ownerid - the controller owner
     * @param $ownertype - the controller type
     * @param $db - database interface. PDO object
     */
    public function __construct($ownerid, $ownertype ,$db){
        // Acoes de banco de dados estao nesse bloco try/catch
        try {
            if($ownertype == 'T') {
                $sql = "SELECT time_total, time_chargeby FROM trunks WHERE id='$ownerid' AND time_total IS NOT NULL";
                $this->ownerid = "Tronco $ownerid";
            }
            else {
                $this->ownerid = "Ramal $ownerid";
                $sql = "SELECT time_total, time_chargeby FROM peers WHERE peer_type='R' AND name='$ownerid' AND time_total IS NOT NULL";
            }
            $owner_info = $db->query($sql)->fetchAll();

            if(count($owner_info) == 1) {
                $this->controlling = true;
                // Ver se temos dados, se nao (e for necessario) adicionamos
                $sql = "SELECT id, used FROM time_history WHERE owner='$ownerid' && owner_type='$ownertype' ";
                switch($owner_info[0]['time_chargeby']) {
                    case 'Y':
                    $sql .= "&& year=YEAR(NOW()) && month IS NULL && day IS NULL";
                    break;
                    case 'M':
                    $sql .= "&& year=YEAR(NOW()) && month=MONTH(NOW()) && day IS NULL";
                    break;
                    case 'D':
                    $sql .= "&& year=YEAR(NOW()) && month=MONTH(NOW()) && day=DAY(NOW())";
                    break;
                }
                $query_result = $db->query($sql)->fetchAll();


                if(count($query_result) == 0) {
                    $sql = "INSERT INTO time_history VALUES ";
                    $sql2 = "SELECT id, used FROM time_history WHERE owner='$ownerid' && owner_type='$ownertype' ";
                    switch($owner_info[0]['time_chargeby']) {
                        case 'Y':
                        $sql .= "('', '$ownerid', YEAR(NOW()), NULL, NULL, 0,NOW(), '$ownertype')";
                        $sql2 .= "&& year=YEAR(NOW()) && month IS NULL && day IS NULL";
                        break;
                        case 'M':
                        $sql .= "('', '$ownerid', YEAR(NOW()), MONTH(NOW()), NULL, 0, NOW(), '$ownertype')";
                        $sql2 .= "&& year=YEAR(NOW()) && month=MONTH(NOW()) && day IS NULL";
                        break;
                        case 'D':
                        $sql .= "('', '$ownerid', YEAR(NOW()), MONTH(NOW()), DAY(NOW()), 0, NOW(), '$ownertype')";
                        $sql2 .= "&& year=YEAR(NOW()) && month=MONTH(NOW()) && day=DAY(NOW())";
                        break;
                    }
                    // adicionando
                    $db->query($sql);
                    // pegando os dados novamente (?!)
                    $query_result = $db->query($sql2)->fetchAll();
                }
            }
        }
        catch(Exception $e) {
            throw new Exception("Fatal error resolving times: " . $e->getMessage());
        }
        // Inicializar tudo
        if($this->controlling) {
            $this->history_id = $query_result[0]['id'];
            $used = $query_result[0]['used'];
            $this->total = $owner_info[0]['time_total'];
            if($used >= $this->total)
            $this->status = "deny-notimeleft";
        }
    }
}