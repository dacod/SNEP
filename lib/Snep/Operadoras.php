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
 * Classe que abstrai Operadoras
 *
 * @see Snep_Operadoras
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Operadoras {

    private $codigo;
    private $nome;
    private $tpm;
    private $tdm;
    private $tbf;
    private $tbc;
    private $vpf;
    private $vpc;

    public function __construct() {

    }

    public function __destruct() {

    }

    public function __clone() {

    }

    // Acesso direto aos atributos.
    public function __get($atributo) {
        return $this->{$atributo};
    }

    // Acesso direto aos atributos.
    public function __set($atributo, $valor) {
        $this->{$atributo} = $valor;
    }

    // Retorna uma determinada Operadora
    // @param String - Id da Operadora
    // @return Array - Dados da Operadora
    public function get($codigo) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from('operadoras')
                        ->where("codigo = '$codigo'");

        $stmt = $db->query($select);
        $operadora = $stmt->fetchAll();

        return $operadora;
    }

    // Retorna todas as Operadoras
    // @param null
    // @return Array - Lista de Operadoras
    public function getAll() {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from('operadoras')
                        ->order('nome');

        $stmt = $db->query($select);
        $operadoras = $stmt->fetchAll();

        return $operadoras;
    }

    // Retorna todas Operadoras possibilitando filtro no select
    // @param String - Representando campo da tabela
    // @param String - Representando valor para consulta
    // @return Array - Listagem de Operadoras
    public function getFiltrado($filtro, $valor) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from('operadoras')
                        ->order('codigo');

        if (!is_null($filtro)) {
            $select->where("" . $filtro . " like '%" . $valor . "%'");
        }

        $stmt = $db->query($select);
        $operadoras = $stmt->fetchAll();

        return $operadoras;
    }

    // Retorna os Centros de Custo de determinada operadora
    // @param String - Id da Operadora
    // @return Array - Centro de Custos
    public function getCcustoOperadora($id) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from(array('o' => 'oper_ccustos'))
                        ->join(array('c' => 'ccustos'), 'c.codigo = o.ccustos', array('codigo', 'nome', 'tipo'))
                        ->where("o.operadora = '$id'");

        $stmt = $db->query($select);
        $op_ccustos = $stmt->fetchAll();

        return $op_ccustos;
    }

    public function getOperadoraCcusto($ccusto) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from(array('o' => 'operadoras'))
                        ->from(array('oc' => 'oper_ccustos'))
                        ->join(array('c' => 'ccustos'), "o.codigo=oc.operadora AND oc.ccustos=c.codigo AND c.codigo='$ccusto'");

        $stmt = $db->query($select);
        $ccusto_op = $stmt->fetch();

        return $ccusto_op;
    }

    // Registra determinada operadora no banco
    // @param Objeto Snep_Operadoras
    // @return String - Id de registro no banco
    public static function register($operadora) {
        $db = Zend_Registry::get('db');

        $insert_data = array("codigo" => $operadora->codigo,
            "nome" => $operadora->nome,
            "tpm" => $operadora->tpm,
            "tdm" => $operadora->tdm,
            "tbf" => $operadora->tbf,
            "tbc" => $operadora->tbc,
            "vpf" => $operadora->vpf,
            "vpc" => $operadora->vpc
        );

        $db->insert('operadoras', $insert_data);

        return $db->lastInsertId();
    }

    // Registra determinados Centros de Custo para determinada Operadora
    // @param String - id da Operadora
    // @param Array - Centro de Custos
    // @return null
    public function setCcustoOperadora($operadora, $ccustos) {
        $db = Zend_Registry::get('db');

        $db->beginTransaction();
        $db->delete('oper_ccustos', "operadora = '$operadora'");

        try {
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }

        foreach ($ccustos as $ccusto) {
            $insert_data = array("operadora" => $operadora,
                "ccustos" => $ccusto
            );
            $db->insert('oper_ccustos', $insert_data);
        }
    }

    // Atualiza informações de determinada Operadora
    // @param String - id da Operadora
    // @return null
    public static function update($operadora) {

        $oper = self::get($operadora->codigo);

        if ($oper) {

            $update_data = array("codigo" => $operadora->codigo,
                "nome" => $operadora->nome,
                "tpm" => $operadora->tpm,
                "tdm" => $operadora->tdm,
                "tbf" => $operadora->tbf,
                "tbc" => $operadora->tbc,
                "vpf" => $operadora->vpf,
                "vpc" => $operadora->vpc
            );

            $db = Zend_Registry::get('db');
            $db->update("operadoras", $update_data, "codigo = '$operadora->codigo'");
        }
    }

    // Remove determinada Operadora
    // @param String - id da Operadora
    // @return null
    public static function remove($operadora) {
        $db = Zend_Registry::get('db');

        $oper = self::get($operadora);

        if ($oper) {

            $db->delete('oper_ccustos', "operadora = '$operadora'");
            $db->delete('operadoras', "codigo = '$operadora'");

            $db->beginTransaction();

            try {
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
    }

}
