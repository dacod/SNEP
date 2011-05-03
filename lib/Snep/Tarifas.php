<?php

/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
 * Classe que abstrai as tarifas
 *
 * @see Snep_Tarifas
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Tarifas {

    private $operadora;
    private $ddi;
    private $pais;
    private $ddd;
    private $cidade;
    private $estado;
    private $prefixo;
    private $data;
    private $vcel;
    private $vfix;
    private $vpf;
    private $vpc;

    public function __construct() {

    }

    public function __destruct() {

    }

    public function __clone() {

    }

    // Retorna determinado atributo
    public function __get($atributo) {
        return $this->{$atributo};
    }

    // Atribui valor a determinado atributo
    public function __set($atributo, $valor) {
        $this->{$atributo} = $valor;
    }

    // Retorna tarifas de acordo com o id passado
    public function get($codigo) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from('tarifas')
                        ->where("codigo = '$codigo'");

        $stmt = $db->query($select);
        $arrTarifa = $stmt->fetchAll();

        return $arrTarifa;
    }

    // Retorna valor de tarifa referente ao id passado
    public function getValor($codigo) {
        $db = Zend_registry::get('db');

        $select = $db->select()
                        ->from('tarifas_valores')
                        ->where("codigo = ?", $codigo)
                        ->order('data');

        $stmt = $db->query($select);
        $arrValores = $stmt->fetchAll();

        return $arrValores;
    }

    public function getTarifaDisp($op, $ddd, $cidade) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from('tarifas')
                        ->where('operadora = ?', $op);
                        //->where('ddd = ?', $ddd)
                        //->where('cidade = ?', $cidade);

        if( $cidade == "Celular" || is_null( $cidade ) ) {
            $select->where('cidade = ?', $cidade);
        }
        
        if($ddd != "") {
            $select->where('ddd = ?', $ddd);
        }

        $stmt = $db->query($select);
        $tarifa = $stmt->fetch();

        if (!$tarifa) {
            return 0;
        } else {
            return $tarifa;
        }
    }

    public function getTarifaReaj($tarifa) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from('tarifas_valores')
                        ->where('codigo = ?', $tarifa['codigo'])
                        ->where("data <= ? ", $tarifa[0] . "23:59:59")
                        ->order('data DESC')
                        ->limit('1');

        $stmt = $db->query($select);
        $reajuste = $stmt->fetch();

        if (count($reajuste) > 0) {
            return $reajuste;
        } else {
            return 0;
        }
    }

    // Retorna tarifas - com ou sem filtro (rel_tarifas.php)
    public function getFiltrada($campo, $valor) {
        $db = Zend_registry::get('db');

        $tab = ($campo == 'nome' ? 'c' : 't' );

        $select = $db->select()
                        ->from(array('o' => 'operadoras'), array('nome'))
                        ->from(array('t' => 'tarifas'))
                        ->join(array('tv' => 'tarifas_valores'), 't.codigo = tv.codigo AND o.codigo = t.operadora');

        if (!is_null($valor)) {
            //$select->where(" $tab.". $campo ." like '%". $valor ."%'");
        }

        $stmt = $db->query($select);
        $arrFiltro = $stmt->fetchAll();

        return $arrFiltro;
    }

    // Retorna todas as tarifas
    public function getAll() {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from('tarifas');

        $stmt = $db->query($select);
        $arrTarifas = $stmt->fetchAll();

        return $arrTarifas;
    }

    // Registra objeto tarifa - informacoes sobre tarifa (tarifas)
    public static function register($tarifa) {
        $db = Zend_Registry::get('db');

        $insert_data = array("operadora" => $tarifa->operadora,
            "ddi" => $tarifa->ddi,
            "pais" => $tarifa->pais,
            "ddd" => $tarifa->ddd,
            "cidade" => $tarifa->cidade,
            "estado" => $tarifa->estado,
            "prefixo" => $tarifa->prefixo
        );

        $db->insert('tarifas', $insert_data);
        $id = $db->lastInsertId();

        self::registerValores($tarifa, $id);
    }

    // Registra objeto tafira - informacoes de valores (tarifas_valores)
    public static function registerValores($tarifa, $id) {
        $db = Zend_Registry::get('db');

        $exist = self::verifyValores($tarifa->data);

        if (!$exist) {
            $tarifa->data = date("Y-m-d H:i:s");
        }

        $insert_data = array("codigo" => $id,
            "data" => $tarifa->data,
            "vcel" => $tarifa->vcel,
            "vfix" => $tarifa->vfix,
            "vpf" => $tarifa->vpf,
            "vpc" => $tarifa->vpc
        );
        if (!$exist) {
            $db->insert('tarifas_valores', $insert_data);
        } else {
            $db->update('tarifas_valores', $insert_data, "data='$tarifa->data'");
        }
    }

    // Verifica existencia de determinado valor já registrado verifica data
    public static function verifyValores($data) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from('tarifas_valores')
                        ->where("data='$data'");

        $stmt = $db->query($select);
        $tarifa = $stmt->fetchAll();

        if (count($tarifa) > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    // Atualiza informacoes de um objeto tarifa no banco
    public static function update($tarifa) {

        $tarifa = self::get($tarifa->codigo);

        if ($tarifa) {

            $update_data = array("codigo" => $tarifa->codigo,
                "data" => date("Y-m-d H:i:s"),
                "vcel" => $tarifa->vcel,
                "vfix" => $tarifa->vfix,
                "vpf" => $tarifa->vpf,
                "vpc" => $tarifa->vpc
            );


            $db = Zend_Registry::get('db');
            $db->insert("tarifas_valores", $update_data);
        }
    }

    // Remove determinada tarifa e valores de tarifa do banco
    public static function remove($codigo) {

        $tarifa = self::get($codigo);
        $db = Zend_Registry::get('db');

        $db->beginTransaction();
        try {
            $db->delete('tarifas', "codigo = $codigo");
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }

        $db->beginTransaction();
        try {
            $db->delete('tarifas_valores', "codigo = $codigo");
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }
    }

    /**
     * Calcula a tarifa
     *
     * @param int $duracao da ligação
     * @param int $tempo_primeiro_min tempo primeiro minuto
     * @param int $fracionamento
     * @param float $tarifa_base
     * @return float
     */
    public static function calcula($duracao, $tempo_primeiro_min, $fracionamento, $tarifa_base, $tp = NULL) {
        if($duracao <= 2) {
            return 0.0;
        }

        // Valor corresponde ao valor do arranque
        $valor = $tarifa_base * ($tempo_primeiro_min / 60);
        $valor_fracao = $tarifa_base * ($fracionamento / 60);

        // Divide tempo da Chamada em 2: t_arq = tempo de arranque ; t_rst = tempo restante
        if ($duracao <= $tempo_primeiro_min) {
            $tempo_restante = 0;
        } else {
            $tempo_restante = $duracao - $tempo_primeiro_min;
        }

        if ($tempo_restante > 0) {
            $fracoes = ceil($tempo_restante / $fracionamento);
            $valor += $fracoes * $valor_fracao;
        }

        return $valor;
    }

}
