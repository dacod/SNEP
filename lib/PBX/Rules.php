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
 * Rule Persistency Manager.
 *
 * Class responsible for managing the persistency for all routing rules of the
 * system. It requires Snep_Db to be set and working.
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class PBX_Rules {
    private function __construct() {}
    private function __clone() {}

    /**
     * Remove uma regra do banco de dados baseado no ID dela.
     *
     * @param int $id
     */
    public static function delete($id) {
        $db = Snep_Db::getInstance();
        $db->delete("regras_negocio","id='{$id}'");
    }

    /**
     * Obtém Regras de negócio do banco de dados.
     *
     * A REGRA É DEVOLVIDA SEM A CLASSE DE COMUNICAÇÂO COM O ASTERISK
     *
     * @param int $id Numero de identificação da regra de negócio que se deseja
     * obter do banco de dados.
     * @return PBX_Rule  $regra de negócio corresponde ao id da chamada
     */
    public static function get($id) {
        $db = Snep_Db::getInstance();

        $select = $db->select()
                     ->from('regras_negocio')
                     ->where("id = '$id'");

        $regra_raw = $db->query($select)->fetchObject();

        if(!$regra_raw) {
            throw new PBX_Exception_NotFound("Regra $id nao encontrada");
        }

        $regra = new PBX_Rule();
        $regra->setPriority($regra_raw->prio);
        $regra->setDesc($regra_raw->desc);
        $regra->setId($id);

        foreach (explode(',', $regra_raw->origem) as $src) {
            if(!strpos($src, ':')) {
                $regra->addSrc(array("type" => $src, "value" => ""));
            }
            else {
                $info = explode(':', $src);
                if(!is_array($info) OR count($info) != 2) {
                    throw new PBX_Exception_DatabaseIntegrity("Valor errado para origem da regra de negocio $regra_raw->id: {$regra_raw->origem}");
                }
                $regra->addSrc(array("type" => $info[0], "value" => $info[1]));
            }
        }
        foreach (explode(',', $regra_raw->destino) as $dst) {
            if(!strpos($dst, ':')) {
                $regra->addDst(array("type" => $dst, "value" => ""));
            }
            else {
                $info = explode(':', $dst);
                if(!is_array($info) OR count($info) != 2) {
                    throw new PBX_Exception_DatabaseIntegrity("Valor errado para destino da regra de negocio $regra_raw->id: {$regra_raw->destino}");
                }
                $regra->addDst(array("type" => $info[0], "value" => $info[1]));
            }
        }

        $regra->cleanValidWeekList();
        foreach (explode(',', $regra_raw->diasDaSemana) as $diaDaSemana) {
            if($diaDaSemana != "") {
                $regra->addWeekDay($diaDaSemana);
            }
        }

        foreach (explode(',', $regra_raw->validade) as $time) {
            $regra->addValidTime($time);
        }
        
        if(!$regra_raw->ativa) {
            $regra->disable();
        }

        if($regra_raw->record == "1") {
            $regra->record();
        }

        $select = $db->select()
                     ->from('regras_negocio_actions')
                     ->where("regra_id = $id")
                     ->order('prio');

        $actions = $db->query($select)->fetchAll();

        // Processing rule actions
        if( count($actions) > 0 ) {
            $select = $db->select()
                     ->from('regras_negocio_actions_config')
                     ->where("regra_id = $id")
                     ->order('prio');

            $configs_raw = $db->query($select)->fetchAll();

            // Rearranging configuration array
            $configs = array();
            if( count($configs_raw) > 0 ) {
                foreach( $configs_raw as $config ) {
                    $configs[$config['prio']][$config['key']] = $config['value'];
                }
            }


            foreach($actions as $acao_raw) {
                $acao = $acao_raw['action'];
                if( class_exists($acao) ) {
                    $config = isset($configs[$acao_raw['prio']]) ? $configs[$acao_raw['prio']] : array();
                    $acao_object = new $acao();
                    $acao_object->setConfig($config);
                    $acao_object->setDefaultConfig(PBX_Registry::getAll($acao));
                    $regra->addAcao($acao_object);
                }
            }
        }

        return $regra;
    }

    /**
     * Retorna um array com todas as regras de negócio persistidas no snep;
     *
     * @param string $where
     * @return array $regras com todas as regras de negócio
     */
    public static function getAll($where = null) {
        $db = Snep_Db::getInstance();

        $select = $db->select()
                  ->from('regras_negocio')
                  ->order(array("prio DESC", "id"));

        if($where !== null) {
            $select->where($where);
        }

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();

        $regras = array();
        foreach($result as $regra) {
            $regras[] = self::get($regra['id']);
        }

        return $regras;
    }

    /**
     * Atualiza uma regra de negócio no banco de dados.
     *
     * Para usar esse método, basta pegar o objeto da regra do banco de dados.
     * Edit seus atributos e passá-lo a esse método.
     * 
     * @param PBX_Rule $rule
     */
    public static function update($rule) {

        if($rule->getId() == -1) {
            throw new PBX_Exception_BadArg("Regra nao possui um id valido.");
        }

        $srcs = "";
        foreach ($rule->getSrcList() as $src) {
            $srcs .= "," . trim($src['type'] . ":" . $src['value'], ':');
        }
        $srcs = trim($srcs, ',');

        $dsts = "";
        foreach ($rule->getDstList() as $dst) {
            $dsts .= "," . trim($dst['type'] . ":" . $dst['value'], ":");
        }
        $dsts = trim($dsts, ',');

        $validade = implode(",", $rule->getValidTimeList());

        $diasDaSemana = implode(",", $rule->getValidWeekDays());

        $update_data = array(
            "prio" => $rule->getPriority(),
            "desc" => $rule->getDesc(),
            "ativa" => ($rule->isActive())?'1':'0',
            "origem"  => $srcs,
            "destino"  => $dsts,
            "record"   => $rule->isRecording(),
            "diasDaSemana" => $diasDaSemana,
            "validade" => $validade
        );

        $db = Snep_Db::getInstance();

        $db->beginTransaction();

        try {
            $db->update("regras_negocio",$update_data,"id='{$rule->getId()}'");

            $db->delete("regras_negocio_actions","regra_id='{$rule->getId()}'");
            $action_prio = 0;
            foreach ($rule->getAcoes() as $acao) {
                $action_update_data = array(
                    "regra_id" => $rule->getId(),
                    "prio"     => $action_prio,
                    "action"   => get_class($acao)
                );
                $db->insert("regras_negocio_actions",$action_update_data);

                foreach ($acao->getConfigArray() as $chave => $valor) {
                    if(!is_null($valor)) {
                        $action_config_data = array(
                            "regra_id" => $rule->getId(),
                            "prio"     => $action_prio,
                            "key"      => $chave,
                            "value"    => $valor
                        );
                        $db->insert("regras_negocio_actions_config",$action_config_data);
                    }
                }

                $action_prio++;
            }
            $db->commit();
        }
        catch(Exception $ex) {
            $db->rollBack();
            throw $ex;
        }

    }

    /**
     * Cadastra uma regra de negócio no banco de dados do Snep.
     * 
     * @param PBX_Rule $rule
     */
    public static function register($rule) {

        $srcs = "";
        foreach ($rule->getSrcList() as $src) {
            $srcs .= "," . trim($src['type'] . ":" . $src['value'], ':');
        }
        $srcs = trim($srcs, ',');

        $dsts = "";
        foreach ($rule->getDstList() as $dst) {
            $dsts .= "," . trim($dst['type'] . ":" . $dst['value'], ':');
        }
        $dsts = trim($dsts, ',');

        $validade = implode(",", $rule->getValidTimeList());

        $diasDaSemana = implode(",", $rule->getValidWeekDays());

        $insert_data = array(
            "prio"     => $rule->getPriority(),
            "desc"     => $rule->getDesc(),
            "origem"   => $srcs,
            "destino"  => $dsts,
            "validade" => $validade,
            "diasDaSemana" => $diasDaSemana,
            "record"   => $rule->isRecording()
        );

        $db = Snep_Db::getInstance();

        $db->beginTransaction();

        try {
            $db->insert("regras_negocio",$insert_data);

            $rule->setId((int)$db->lastInsertId('regras_negocio_id'));

            $action_prio = 0;
            foreach ($rule->getAcoes() as $acao) {
                $action_insert_data = array(
                    "regra_id" => $rule->getId(),
                    "prio"     => $action_prio,
                    "action"   => get_class($acao)
                );
                $db->insert("regras_negocio_actions",$action_insert_data);

                foreach ($acao->getConfigArray() as $chave => $valor) {
                    $action_config_data = array(
                        "regra_id" => $rule->getId(),
                        "prio"     => $action_prio,
                        "key"      => $chave,
                        "value"    => $valor
                    );
                    $db->insert("regras_negocio_actions_config",$action_config_data);
                }
                $action_prio++;
            }
            $db->commit();
        }
        catch(Exception $ex) {
            $db->rollBack();
            throw $ex;
        }
    }
}
