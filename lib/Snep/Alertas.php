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
 * Classe que abstrai os Alertas
 *
 * @see Snep_Alertas
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class Snep_Alertas {

    public function __construct() {}
    public function __destruct() {}
    public function __clone() {}

    public function __get($atributo) {
        return $this->{$atributo};
    }

    /**
     * setAlerta - Cadastra alerta no banco
     * @param <string> $tipo
     * @param <array> $arrAlerts
     */
    public function setAlerta($fila, $arrAlerts) {
        
        $db = Zend_Registry::get('db');
        $insert_data = array("recurso"  =>  $fila,
                             "tipo"     =>  $arrAlerts['tipo'],
                             "tme"      =>  $arrAlerts['tme'],
                             "sla"      =>  $arrAlerts['sla'],
                             "item"     =>  $arrAlerts['item'],
                             "alerta"   =>  $arrAlerts['alerta'],
                             "destino"  =>  $arrAlerts['destino'],
                             "ativo"    =>  1 );
        
        $db->insert('alertas', $insert_data);
    }

    /**
     * getAlertas - Retorna alertas da fila informada
     * @param <string> $name
     * @return <type>
     */
    public function getAlertas($name) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('alertas')
        ->where("item = '$name'");

        $stmt = $db->query($select);
        $alertas = $stmt->fetchAll();

        return $alertas;
    }

    /**
     * resetAlertas - Limpa qualquer registro de alerta de determinada fila
     * @param <string> $name
     */
    public function resetAlertas($name) {
        $db = Zend_Registry::get('db');

        $db->beginTransaction() ;
        try {
            $db->delete('alertas', "item = '$name'");
            $db->commit();
        }
        catch(Exception $e) {
            $db->rollBack();
        }

    }

    /**
     *  getTimeOut - Retorna Tempo maximo de espera das filas
     * @return <array>  $ret
     */
    public function getTimeOut() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('queues', array('name','servicelevel') )
        ->order('name');

        $stmt = $db->query($select);
        $timeout = $stmt->fetchAll();

        $ret = array();
        foreach($timeout as $x) {
            if($x['servicelevel'] > 0) {
                $ret[$x['name']] = $x['servicelevel'];
            }
        }

        return $ret;
    }

    public function SendAlerta($fila, $alertas) {

        foreach($alertas as $alerta) {
            switch ($alerta['tipo']) {
                case "email":

            }

        }

    }

    public function SendEmail($alerta) {

        $msg = "SNEP - A fila {$alerta['recurso']} ";
        $mail = new Zend_Mail();
        $mail->setBodyText($_POST['texto']);
        $mail->setFrom($_POST['email']);
        $mail->addTo('rafael@localhost');
        $mail->setSubject('Contato Site');
        $mail->send();
     

    }

}

?>
