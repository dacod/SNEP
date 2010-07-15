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
 * Classe que abstrai a Manutencao de arquivos
 *
 * @see Snep_Manutencao
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 *
 */
class Snep_Manutencao {

    public function __construct() {}
    public function __destruct() {}
    public function __clone() {}

    public function __get($atributo) {
        return $this->{$atributo};
    }

    /**
     * Lista calldate, userfield das ligações do periodo.
     *
     * @param <string> $data_inicio aaaa-dd-mm hh:ii:ss
     * @param <string> $data_fim aaaa-dd-mm hh:ii:ss
     * @return <array> (calldate, userfield)
     */
    public function listaPeriodo($data_inicio, $data_fim) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from('cdr', array('calldate', 'userfield'))
        ->where("calldate >= '$data_inicio'")
        ->where("calldate <= '$data_fim'")
        ->where("userfield != '' ")
        ->group('userfield');

        $stmt = $db->query($select);
        $registros = $stmt->fetchAll();

        return $registros;
    }

    /**
     * Lista Unidade montadas como Storage
     *
     * @param <string> $arquivos
     * @return <array>
     */
    public function listaStorage($arquivos) {

        $root = scandir( $arquivos );
        $return  = array();

        foreach($root as $files) {
            if( preg_match('/^storage/', $files ) ) {
                $return[] = $files;
            }
        }

        return $return;
    }

    /**
     * Busca arquivo de gravacao, retorna caminho do mesmo ou não
     * @param <string> $calldate
     * @param <string> $userfield
     * @param <string> $arquivos
     * @return <string> Caminho para o arquivo.
     */
    public function arquivoExiste($calldate, $userfield) {

        $data = substr($calldate, 0, 10);

        $config = Zend_Registry::get('config');

        $arquivos = $config->ambiente->path_voz;
        
        if( file_exists($arquivos) ) {

                // Se existir pasta com data, já organizado pelo movefiles.
                if( file_exists($arquivos ."/". $userfield .".wav") ) {
                    return $arquivos ."/". $userfield .".wav";
                }
                elseif( file_exists($arquivos ."/". $userfield .".mp3") ) {
                    return $arquivos ."/". $userfield .".mp3";
                }
                elseif( file_exists($arquivos ."/". $userfield .".WAV")) {
                    return $arquivos ."/". $userfield .".WAV";
                }
                elseif( file_exists($arquivos ."/". $data ."/". $userfield .".wav") ) {
                    return $arquivos ."/". $data ."/". $userfield .".wav";
                }
                elseif( file_exists($arquivos ."/". $data ."/". $userfield .".mp3") ) {
                    return $arquivos ."/". $data ."/". $userfield .".mp3";
                }
                elseif( file_exists($arquivos ."/". $data ."/". $userfield .".WAV")) {
                    return $arquivos ."/". $data ."/". $userfield .".WAV";
                    
                }else{

                    $storages = self::listaStorage($arquivos);

                    foreach($storages as $storage) {

                        if( file_exists($arquivos ."/". $storage ."/". $data ."/". $userfield .".wav") ) {
                            return $arquivos ."/". $storage ."/". $data ."/". $userfield .".wav";
                        }
                        elseif( file_exists($arquivos ."/". $storage ."/". $data ."/". $userfield .".mp3") ) {
                            return $arquivos ."/". $storage ."/". $data ."/". $userfield .".mp3";
                        }
                        elseif( file_exists($arquivos ."/". $storage ."/". $data ."/". $userfield .".WAV")) {
                            return $arquivos ."/". $storage ."/". $data ."/". $userfield .".WAV";
                        }
                    }

                }
        }else{
            return false;
        }
    }

    /**
     * Remove arquivo de gravação.
     * @param <string> $arquivo
     * @return <bool> 
     */
    public function removeBackup($arquivo) {

        if(file_exists($arquivo)) {
            return (unlink($arquivo));
        }       

    }
}

?>
