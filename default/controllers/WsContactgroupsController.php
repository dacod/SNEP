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
 * Controlador REST do WebService - Grupos de Contato
 *
 * @see Snep_Rest_Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class WsContactgroupsController extends Snep_Rest_Controller {
    
    /**
     * HTTP GET Request com ou sem parâmetros
     *
     * @param Object $data
     * @return array $response
     */
    public function get($data) {

        if( ! isset( $data->id ) ) {
             throw new Snep_Rest_Exception_BadRequest("Parametros inexistente: 'id' .");
        }

        if( $data->id == "" ) {
            $groups = Snep_Group_Manager::getAll();
        }else{
            $groups[] = Snep_Group_Manager::get( $data->id );
        }

        if( count($groups) < 1 ) {
            throw new Snep_Rest_Exception_BadRequest("Grupo de Contato inexistente.");
        }
        return $groups;
    }

    /**
     * HTTP POST Request
     *
     * @param Object $data
     * @return array $response
     */
    public function post($data) {
        
        $group = array();
        if( $data->nome ) {
            if( $data->nome ) {
                $group['name'] = $data->nome;
                Snep_Group_Manager::add($group);

            }else{
                throw new Snep_Rest_Exception_BadRequest("Parametro não informado: 'grupo'.");
            }
           
        }else{
            throw new Snep_Rest_Exception_BadRequest("Parametro não informados: 'nome'.");
        }
    }

    /**
     * HTTP PUT Request
     *
     * @param Object $data
     * @return array $response
     */
    public function put($data) {
        
        $group = array();
        if( $data->nome && $data->id) {

            $group_db = Snep_Group_Manager::get( $data->id );

            if(count($group_db) > 1) {
                $group['name'] = $data->nome;
                $group['id'] = $data->id;
                Snep_Group_Manager::edit($group);

            }else{
                throw new Snep_Rest_Exception_BadRequest("Grupo inexistente.");
            }  

        }else{
            throw new Snep_Rest_Exception_BadRequest("Você precisa especificar 'nome' e 'id'.");
        }
    }

    /**
     * HTTP DELETE Request
     *
     * @return array $response
     */
    public function delete($data) {

        if( $data->id ) {

            $db = Zend_Registry::get('db');
            $select = $db->select()
                ->from('ad_group_contact')
                ->where("ad_group_contact.group = '{$data->id}'" );

            $stmt = $db->query($select);
            $on_group = $stmt->fetchAll();
            
            if(count($on_group) > 0) {

                throw new Snep_Rest_Exception_BadRequest("O grupo não pode ser removido, existem contatos vinculados a ele. ");
            }else{
                Snep_Group_Manager::del( $data->id );  
            }

        }else{
            throw new Snep_Rest_Exception_BadRequest("Informe um 'id' do grupo. ");
        }
    }
    
}
