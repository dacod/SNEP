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
 * Classe que abstrai os Vínculos
 *
 * @see Snep_Vinculos
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class Snep_Vinculos {    

    public function __construct() {}
    public function __destruct() {}
    public function __clone() {}

    public function __get($atributo) {
        return $this->{$atributo};
    }

    public function getVinculos($ramal) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('permissoes_vinculos', array('id_peer', 'tipo', 'id_vinculado'))
        ->where("permissoes_vinculos.id_peer='$ramal'");

        $stmt = $db->query($select);
        $vinculos = $stmt->fetchAll();

        $doramal = array();
        
        foreach($vinculos as $reg) {
            $doramal[$reg['id_vinculado']] = $reg['id_vinculado'];            
        }
        
        return $doramal;
    }

    public function getRamaisDoGrupo($grupo) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('peers', array('name') )
        ->where("peers.peer_type = 'R' ")
        ->where("peers.group = '$grupo'");

        $stmt = $db->query($select);
        $vinculos = $stmt->fetchAll();

        $return = array();
        foreach($vinculos as $ramais) {
            $return[$ramais['name']] = $ramais['name'];
        }        
        return $return;
    }

    public function getNivelVinculos($ramal) {

        if ($ramal == "admin") {
            return 1;
        }
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('permissoes_vinculos' )
        ->where("permissoes_vinculos.id_peer = '$ramal'")
        ->order('permissoes_vinculos.tipo');

        $stmt = $db->query($select);
        $vinculos = $stmt->fetchAll();

        $ramais = '';
        $grupos = '';
        
        foreach($vinculos as $vinculo) {
            if($vinculo['tipo'] == "R") {
                $ramais .= "{$vinculo['id_vinculado']}, ";
            }else {
                $grupos .= "{$vinculo['id_vinculado']}, ";
            }
        }

        ( $ramais == '' ? 0 : $ramais = " Ramais: ( " . substr($ramais, 0, -2) ." )" );
        ( $grupos == '' ? 0 : $grupos = " Grupos: ( " . substr($grupos, 0, -2) ." )" );

        return $ramais . $grupos;
    }

    public function setVinculos($ramal, $tipo, $vinculo) {

        $db = Zend_Registry::get('db');

        $insert_data = array("id_peer" => $ramal,
                             "tipo"    => $tipo,
                             "id_vinculado"   =>  trim( $vinculo )
        );
        $db->insert('permissoes_vinculos', $insert_data);
        
    }
    
    public function getVinculados($ramal) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('permissoes_vinculos', array('id_peer', 'id_vinculado'))
        ->from('peers', array('callerid'))
        ->where("permissoes_vinculos.id_peer='$ramal'")
        ->where("permissoes_vinculos.tipo = 'R' ")
        ->where("peers.name=permissoes_vinculos.id_peer");
        
        $stmt = $db->query($select);
        $vinculos = $stmt->fetchAll();

        return $vinculos;
    }

    public function getDesvinculados($ramal) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('permissoes_vinculos', array('id_vinculado'))
        ->where("permissoes_vinculos.id_peer='$ramal' ")
        ->where("permissoes_vinculos.tipo='R'");

        $stmt = $db->query($select);
        $vinculos = $stmt->fetchAll();        

        $des = $db->select()
        ->from("peers", array('name'))
        ->where("name != 'admin' ")
        ->where("peer_type = 'R' ");

        if($vinculos) {
            $des->where('name NOT IN (?)', $vinculos);
        }        

        $stmt = $db->query($des);
        $desvinc = $stmt->fetchAll();
        
        return $desvinc; 
    }

    public function resetVinculos($name) {

        $db = Zend_Registry::get('db');

        $db->beginTransaction() ;
        try {
            $db->delete('permissoes_vinculos', "id_peer = '$name'");
            $db->commit();
        }
        catch(Exception $e) {
            $db->rollBack();
        }
    }
    
    public function setVinculosGrupo($ramal, $arrGrupos) {

        $db = Zend_Registry::get('db');

        foreach($arrGrupos as $num => $grupo) {
            $insert_data = array("id_peer" => $ramal,
                                 "tipo"   => "G",
                                 "id_vinculado"   => trim( $grupo )
            );
            $db->insert('permissoes_vinculos', $insert_data);
        }
    }
    
    public function getVinculadosGrupo($ramal) {
        
        $db = Zend_Registry::get('db');
        
        $select = $db->select()
        ->from("permissoes_vinculos")
        ->where("tipo = 'G' ")
        ->where("id_peer = '$ramal'");
        
        $stmt = $db->query($select);
        $grupo_vinculado = $stmt->fetchAll();

        return $grupo_vinculado;
    }
    
    public function getDesvinculadosGrupo($ramal) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from("permissoes_vinculos")
        ->where("tipo = 'G' ")
        ->where("id_peer = '$ramal'");

        $stmt = $db->query($select);
        $vinculo = $stmt->fetchAll();

        $des = $db->select()
        ->from("groups", array('name'));

        if($vinculo) {
            $des->where('name NOT IN (?)', $vinculo);
        }
        
        $stmt = $db->query($des);
        $desvinc = $stmt->fetchAll();

        return $desvinc;
    }

    public function getVinculadosAgente($ramal) {

        $agentes = self::getAgente();

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from("permissoes_vinculos")
        ->where("tipo = 'A' ")
        ->where("id_peer = '$ramal'");        

        $stmt = $db->query($select);
        $agentes_vinculado = $stmt->fetchAll();

        return $agentes_vinculado;
    }

    public function getDesvinculadosAgente($ramal) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from("permissoes_vinculos")
        ->where("tipo = 'A' ")
        ->where("id_peer = '$ramal'");

        $stmt = $db->query($select);
        $vinculado = $stmt->fetchAll();

        $agentes = self::getAgente();
        $desvinculados = array();

        foreach($agentes as $agent) {
            if(!array_key_exists($agent, $vinculado)) {
                $desvinculados[$agent] = $agent;
            }            
        }


        return $desvinculados;        
    }

    public function getAgente() {

        $arquivo =  file_get_contents('/etc/asterisk/snep/snep-agents.conf');
        $teste = explode("\n", $arquivo);
        $f = false;

        $agents = array();
        foreach($teste as $id => $agent) {
            if($id > 12 ) {
                if (! strlen( trim($agent) ) < 1 ) {
                    $swp = substr($agent, strpos($agent, "=>")+3);
                    $numero = substr($swp, 0, strpos($swp, ","));
                    $agents[$numero] = $numero;
                }                    
            }            
        }

        return  $agents;
    }
}

?>
