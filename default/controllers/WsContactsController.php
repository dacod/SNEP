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
 * Controlador REST do WebService - Contatos
 *
 * @see Snep_Rest_Controller
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class WsContactsController extends Snep_Rest_Controller {
    
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
            $contacts = Snep_Contact_Manager::getAll();
            
        }else{            
            $contacts[] = Snep_Contact_Manager::get( $data->id );
            
        }
        
        if( count($contacts[0]) < 2 ) {
            throw new Snep_Rest_Exception_BadRequest("Contato inexistente.");
        }

        $retorno = array();


        foreach($contacts as $k => $contact) {

            $return[$k]['id'] = $contact['idCont'];
            $return[$k]['nome'] = $contact['nameCont'];
            $return[$k]['grupo'] = $contact['name'];            
            $return[$k]['telefones'] = Snep_Contact_Manager::getPhones($contact['idCont']);

            $fields = Snep_Field_Manager::getFields(true, $contact['idCont']);
            foreach($fields as $field) {
                $return[$k][$field['name']] = $field['value'];
            }
        }

        return $return;        
    }

    /**
     * HTTP POST Request
     *
     * @param Object $data
     * @return array $response
     */
    public function post($data) {
        
        $contact = array();
        if( $data->nome && $data->telefones ) {
            if( $data->grupo ) {

                $contact['nameCont'] = $data->nome;                
                $contact['group'] = $data->grupo;

                $phones = array();
                if( is_array($data->telefones)) {
                    foreach($data->telefones as $k => $fone) {
                        $phones[$k] = $fone;
                    }
                }else{
                    throw new Snep_Rest_Exception_BadRequest("Telefone deve ser informado como array. Ex: \"telefones\":[\"99999999\",\"88888888\",\"33333333\"] \n");
                }

                $contact['phones'] = $phones;
                $fields = Snep_Field_Manager::getFields(false, null);

                foreach($fields as $field) {
                    if( $field['required'] ) {
                        if( $data->{$field['name']} ){                            
                                $contact[$field['id']] = $data->{$field['name']};                            
                        }else{
                                throw new Snep_Rest_Exception_BadRequest("Parâmetros requerido não informado: {$field['name']} ");
                        }
                    }else{
                        if( $data->{$field['name']} ) {
                            $contact[$field['id']] = $data->{$field['name']};
                        }
                    }
                }
                
                Snep_Contact_Manager::add($contact);

            }else{
                throw new Snep_Rest_Exception_BadRequest("Parametros não informados: 'grupo'.");
            }
           
        }else{
            throw new Snep_Rest_Exception_BadRequest("Parametros não informados: 'nome' ou 'fone'. Não informados.");
        }
    }

    /**
     * HTTP PUT Request
     *
     * @param Object $data
     * @return array $response
     */
    public function put($data) {

        $contact_db = Snep_Contact_Manager::get( $data->id );

        if( isset( $contact_db['idCont'] )) {

            $contact = array();
            if( $data->nome && $data->telefones ) {
                if( $data->grupo && $data->id ) {

                    $grupo_db = Snep_Group_Manager::get($data->grupo);
                    if( is_null( $grupo_db['id'] ) ) {
                        throw new Snep_Rest_Exception_BadRequest("Grupo informado não existe.");
                    }

                    $contact['id'] = $data->id;
                    $contact['nameCont'] = $data->nome;

                    $phones = array();
                    if( is_array($data->telefones)) {
                        foreach($data->telefones as $k => $fone) {
                            $phones[$k] = $fone;
                        }
                    }else{
                        throw new Snep_Rest_Exception_BadRequest("Telefone deve ser informado como array. Ex: \"telefones\":[\"99999999\",\"88888888\",\"33333333\"] \n\n");
                    }

                    $contact['phones'] = $phones;
                    $contact['group'] = $data->grupo;

                    $fields = Snep_Field_Manager::getFields(false, null);

                    foreach($fields as $field) {
                        if( $field['required'] ) {
                            if( $data->{$field['name']} )
                                $contact[$field['id']] = $data->{$field['name']};
                        }else{
                            if(  isset( $data->{$field['name']} ) ) {
                                $contact[$field['id']] = $data->{$field['name']};
                            }
                        }
                    }
                    
                    Snep_Contact_Manager::edit($contact);

                }else{
                    throw new Snep_Rest_Exception_BadRequest("Parametros não informados: 'grupo'.");
                }

            }else{
                throw new Snep_Rest_Exception_BadRequest("Parametros não informados: 'nome' ou 'telefones'. Não informados.");
            }

        }else{
                throw new Snep_Rest_Exception_BadRequest("O Contato do 'id' informado não existe");
        }
        
    }

    /**
     * HTTP DELETE Request
     *
     * @return array $response
     */
    public function delete($data) {

        if( $data->id ) {
            Snep_Contact_Manager::del( $data->id );
        }else{
            throw new Snep_Rest_Exception_BadRequest("Parametro 'id' não informado.");
        }
    }
    
}
