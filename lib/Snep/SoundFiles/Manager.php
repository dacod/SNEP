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
 * Classe to manager a Carrier.
 *
 * @see Snep_SoundFiles_Manager
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_SoundFiles_Manager {

    public function __construct() {}

    /**
     * Get all carrier
     */
    public function getAll() {

        $db = Zend_registry::get('db');

        $select = $db->select()
            ->from("operadoras");
            
        $stmt = $db->query($select);
        $carrier = $stmt->fetchAll();

        return $carrier;
        
    }

    /**
     * Get a carrier by id
     * @param int $id
     * @return Array
     */
    public function get($file) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('sounds')
            ->where("sounds.arquivo = ?", $file);

        try {
            $stmt = $db->query($select);
            $sound = $stmt->fetch();

        }catch(Exception $e) {
            return false;
        }
        
        return $sound;
    }

    /**
     * Add a sound file
     * @param array $file
     */
    public function add($file) {

        $db = Zend_Registry::get('db');

        $insert_data = array('arquivo'   => $file['arquivo'],
                             'descricao' => $file['descricao'],
                             'data'      => new Zend_Db_Expr('NOW()'),
                             'tipo'      => $file['tipo'] );

        $db->insert('sounds', $insert_data);

        return $db->lastInsertId();
   
    }

    /**
     * Remove a Sound File register
     * @param int $id
     */
    public function remove($file, $class = false) {

            $db = Zend_Registry::get('db');

            $db->beginTransaction();
            if($class){
                $db->delete('sounds', "arquivo = '$file' and secao = '$class'");
            }else{
                $db->delete('sounds', "arquivo = '$file'");
            }
            
            try {
                $db->commit();
                
            } catch (Exception $e) {
                $db->rollBack();                
            }



    }

    public function addClass($class) {

        $classes = self::getClasses();
        $classes[$class['name']] = $class;


        $header =  ";----------------------------------------------------------------\n ";
        $header .= "; Arquivo: snep-musiconhold.conf - Cadastro Musicas de Espera    \n";
        $header .="; Sintaxe: [secao]                                               \n";
        $header .=";          mode=modo de leitura do(s) arquivo(s)                 \n";
        $header .=";          directory=onde estao os arquivos                      \n";
        $header .= ";          [application=aplicativo pra tocar o som] - opcional   \n";
        $header .= "; Include: em /etc/asterisk/musiconhold.conf                     \n";
        $header .= ";          include /etc/asterisk/snep/snep-musiconhold.conf      \n";
        $header .= "; Atualizado em: 26/05/2008 11:22:18                             \n";
        $header .= "; Copyright(c) 2008 Opens Tecnologia                             \n";
        $header .= ";----------------------------------------------------------------\n";
        $header .= "; Os registros a Seguir sao gerados pelo Software SNEP.          \n";
        $header .= "; Este Arquivo NAO DEVE ser editado Manualmente sob riscos de    \n";
        $header .= "; causar mau funcionamento do Asterisk                           \n";
        $header .= ";----------------------------------------------------------------\n\n";

        $body = '';
        foreach ($classes as $classe) {
            $body .= "[" . $classe['name'] . "] \n";
            $body .= "mode=" . $classe['mode'] . "\n";
            $body .= "directory=" . $classe['directory'] . "\n\n";
        }

        if( ! file_exists($class['directory'] )) {

            exec("mkdir {$class['directory']}");
            exec("mkdir {$class['directory']}/tmp");
            exec("mkdir {$class['directory']}/backup");

            file_put_contents('/etc/asterisk/snep/snep-musiconhold.conf', $header . $body );

            return true;

        }
        return false;
    }

    public function syncFiles() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('sounds')
            ->where('sounds.tipo = ?', 'MOH');

        try {
            $stmt = $db->query($select);
            $sounds = $stmt->fetchAll();

        }catch(Exception $e) {
            return false;
        }

        $_sound = array();
        foreach($sounds as $sound) {
            $_sound[$sound['arquivo']] = $sound['arquivo'];
        }
        

        $allClasses = Snep_SoundFiles_Manager::getClasses();
        $classesFolder = array();

        foreach($allClasses as $id => $xclass) {


            $classesFolder[$id]['name'] = $xclass['name'];
            $classesFolder[$id]['directory'] = $xclass['directory'];

            if(file_exists($xclass['directory'])) {

                $allFiles = array();
                $files = array();
                foreach( scandir($xclass['directory']) as $thisClass => $file ) {

                    if( ! preg_match("/^\.+.*/", $file)  ) {

                        if( ! preg_match('/^tmp+.*/', $file)) {

                            if( ! preg_match('/^backup+.*/', $file)) {

                                if( ! in_array( $file, array_keys( $allClasses ) )) {

                                    if( ! in_array($file, $_sound)  ) {

                                        $newfile = array('arquivo' => $file,
                                                         'descricao' => $file,
                                                         'data' => new Zend_Db_Expr('NOW()'),
                                                         'tipo' => 'MOH',
                                                         'secao' => $id);


                                        Snep_SoundFiles_Manager::addClassFile($newfile);
                                    }
 
                                }
                            }
                        }
                    }
                }
            }
            
        }
        return true;
    }

    public function getClasses() {

        $sections = new Zend_Config_Ini('/etc/asterisk/snep/snep-musiconhold.conf');
        $_section =  array();
        foreach($sections->toArray() as $class => $info) {
            $_section[$class]['name'] = $class;
            $_section[$class]['mode'] = $info['mode'];
            $_section[$class]['directory'] = $info['directory'];
        }

        return $_section;
    }

    public function getClasse($name) {

        $sections = new Zend_Config_Ini('/etc/asterisk/snep/snep-musiconhold.conf');
        $_section =  array();
        foreach($sections->toArray() as $class => $info) {

            if($class == $name) {
                $_section['name'] = $class;
                $_section['mode'] = $info['mode'];
                $_section['directory'] = $info['directory'];
            }
        }

        return $_section;
    }

        /**
     * Add a sound file
     * @param array $file
     */
    public function addClassFile($file) {

        $db = Zend_Registry::get('db');

        $insert_data = array('arquivo'   => $file['arquivo'],
                             'descricao' => $file['descricao'],
                             'data'      => new Zend_Db_Expr('NOW()'),
                             'tipo'      => 'MOH',
                             'secao'     => $file['secao']);

        try {
            $db->insert('sounds', $insert_data);
            
        }catch(Exception $e) {
            
            return false;

        }
        

        return $db->lastInsertId();

    }

    public function editClassFile($file) {

        $db = Zend_Registry::get('db');

        $insert_data = array('arquivo'   => $file['arquivo'],
                             'descricao' => $file['descricao'],
                             'data'      => new Zend_Db_Expr('NOW()'),
                             'tipo'      => 'MOH',
                             'secao'     => $file['secao']);

        try {
            $db->update('sounds', $insert_data, "arquivo='{$file['arquivo']}' and secao='{$file['secao']}'");

        }catch(Exception $e) {

            return false;

        }

        return $db->lastInsertId();
    }

    public function getClassFiles($class) {

        $allClasses = Snep_SoundFiles_Manager::getClasses();
       
        $classesFolder = array();
        foreach($allClasses as $id => $xclass) {
            $classesFolder[$id] = $id;
        }

        if(file_exists($class['directory'])) {
            $allFiles = array();
            $files = array();
            foreach( scandir($class['directory']) as $file ) {

                if( ! preg_match("/^\.+.*/", $file) && ! in_array($file, $classesFolder) ) {
                    if( preg_match("/^backup+.*/", $file) ) {

                        foreach( scandir($class['directory'] .'/'. $file ) as $backup) {
                             if( ! preg_match("/^\.+.*/", $backup) ) {
                        //        $files[] = $class['directory'] .'/backup/'. $backup;
                             }
                        }
                    }
                    elseif( preg_match("/^tmp+.*/", $file) ) {

                        foreach( scandir($class['directory'] .'/'. $file ) as $tmp) {
                             if( ! preg_match("/^\.+.*/", $tmp) ) {
                         //       $files[] = $class['directory'] .'/tmp/'. $tmp;
                             }
                        }
                    }
                    else {
                        $files[$file] = $class['directory'] .'/'. $file;
                        //$allFiles[$file] = $file;

                    }
                }
            }
            
            
            $resultado = array();
            foreach($files as $name => $file) {
                $resultado[$name] = Snep_SoundFiles_Manager::get($name);
                $resultado[$name]['full'] = $file;
                
            }
            

            return $resultado;
        }

    }

    public function editClass($originalName, $newClass) {

        $classes = self::getClasses();

        $directory = '';
        foreach($classes as $class => $item) {

            if( $originalName == $item['name']) {
                $classes[$class]['name'] = $newClass['name'];
                $classes[$class]['mode'] = $newClass['mode'];
                $directory = $classes[$class]['directory'];
                $classes[$class]['directory'] = $newClass['directory'];
            }
            
        }



        $header =  ";----------------------------------------------------------------\n ";
        $header .= "; Arquivo: snep-musiconhold.conf - Cadastro Musicas de Espera    \n";
        $header .="; Sintaxe: [secao]                                               \n";
        $header .=";          mode=modo de leitura do(s) arquivo(s)                 \n";
        $header .=";          directory=onde estao os arquivos                      \n";
        $header .= ";          [application=aplicativo pra tocar o som] - opcional   \n";
        $header .= "; Include: em /etc/asterisk/musiconhold.conf                     \n";
        $header .= ";          include /etc/asterisk/snep/snep-musiconhold.conf      \n";
        $header .= "; Atualizado em: 26/05/2008 11:22:18                             \n";
        $header .= "; Copyright(c) 2008 Opens Tecnologia                             \n";
        $header .= ";----------------------------------------------------------------\n";
        $header .= "; Os registros a Seguir sao gerados pelo Software SNEP.          \n";
        $header .= "; Este Arquivo NAO DEVE ser editado Manualmente sob riscos de    \n";
        $header .= "; causar mau funcionamento do Asterisk                           \n";
        $header .= ";----------------------------------------------------------------\n\n";

        $body = '';
        foreach ($classes as $classe) {
            $body .= "[" . $classe['name'] . "] \n";
            $body .= "mode=" . $classe['mode'] . "\n";
            $body .= "directory=" . $classe['directory'] . "\n\n";
        }

        if( ! file_exists($newClass['directory'] )) {

            exec("mkdir {$newClass['directory']}");
            exec("mkdir {$newClass['directory']}/tmp");
            exec("mkdir {$newClass['directory']}/backup; ");

            exec("cp  {$directory}/* {$newClass['directory']}/");
            exec("cp  {$directory}/tmp/* {$newClass['directory']}/tmp/");
            exec("cp  {$directory}/backup/* {$newClass['directory']}/backup/");

            exec("rm -rf {$directory}");

            file_put_contents('/etc/asterisk/snep/snep-musiconhold.conf', $header . $body );

            return true;

        }
        return false;

    }


    public function removeClass($classRemove) {


        $classes = self::getClasses();

        $directory = '';
        foreach($classes as $class => $item) {

            if( $classRemove['name'] == $item['name']) {

                if(file_exists($classRemove['directory'])) {
                    exec("rm -rf {$classRemove['directory']}");
                }
                unset($classes[$class]);

            }

        }

        $header =  ";----------------------------------------------------------------\n ";
        $header .= "; Arquivo: snep-musiconhold.conf - Cadastro Musicas de Espera    \n";
        $header .="; Sintaxe: [secao]                                               \n";
        $header .=";          mode=modo de leitura do(s) arquivo(s)                 \n";
        $header .=";          directory=onde estao os arquivos                      \n";
        $header .= ";          [application=aplicativo pra tocar o som] - opcional   \n";
        $header .= "; Include: em /etc/asterisk/musiconhold.conf                     \n";
        $header .= ";          include /etc/asterisk/snep/snep-musiconhold.conf      \n";
        $header .= "; Atualizado em: 26/05/2008 11:22:18                             \n";
        $header .= "; Copyright(c) 2008 Opens Tecnologia                             \n";
        $header .= ";----------------------------------------------------------------\n";
        $header .= "; Os registros a Seguir sao gerados pelo Software SNEP.          \n";
        $header .= "; Este Arquivo NAO DEVE ser editado Manualmente sob riscos de    \n";
        $header .= "; causar mau funcionamento do Asterisk                           \n";
        $header .= ";----------------------------------------------------------------\n\n";

        $body = '';
        foreach ($classes as $classe) {
            $body .= "[" . $classe['name'] . "] \n";
            $body .= "mode=" . $classe['mode'] . "\n";
            $body .= "directory=" . $classe['directory'] . "\n\n";
        }

        file_put_contents('/etc/asterisk/snep/snep-musiconhold.conf', $header . $body );
 

    }

    /**
     * Update a carrier data
     * @param Array $data
     */
    public function edit($carrier) {

        $db = Zend_Registry::get('db');

        $update_data = array('nome'     => $carrier['name'],
                             'tpm'      => $carrier['ta'],
                             'tdm'      => $carrier['tf'],
                             'tbf'      => $carrier['tbf'],
                             'tbc'      => $carrier['tbc'] );


        $db->update("operadoras", $update_data, "codigo = '{$carrier['id']}'");

    }

    /**
     * Set CostCenter to Carrier
     * @param int $idCarrier
     * @param int $costCenter
     */
    public function setCostCenter($idCarrier, $costCenter) {

        $db = Zend_Registry::get('db');

        $db->insert('oper_ccustos', array('operadora' => $idCarrier,
                                          'ccustos'   => $costCenter));

    }

    public function getLocale() {

        $locale = Zend_Registry::get('config')->system->language;
        $sound_path = Zend_Registry::get('config')->path->asterisk->sounds;

        
        

    }

}
