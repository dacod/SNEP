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
 * Classe que controla a persistencia de usuarios dentro do banco de dados
 * do snep.
 *
 * Essa classe diz muito sobre a filosofia de funcionamento do snep
 * onde todo ramal/agente é um usuário, essa classe é especializada nesse
 * tipo de persistência.
 *
 * Nota sobre a persistencia: O controle de persistencia é feito no snep em
 * classes separadas. Não no construtor da classe modelo como se ve em outros
 * frameworks e arquiteturas. O motivo disso é que se ocorrer uma mudança na
 * forma como é feita a persistencia desses objetos os mesmos não precisam ser
 * alterados. Isso aumenta a compactibilidade com código legado.
 * ~henrique
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author    Henrique Grolli Bassotto
 */
class PBX_Usuarios {

    /**
     * Retorna um usuário em sua classe nativa.
     *
     * ex: PBX_Asterisk_Ramal e no futuro Snep_Agent
     *
     * @param int $userid
     * @return Snep_Usuario usuario
     */
    public static function get($userid) {
        $db = Zend_Registry::get('db');

        $userid = str_replace("'", "\'", $userid);
        $select = $db->select()->from('peers')->where("name = '$userid' AND peer_type='R'");
        $stmt = $db->query($select);
        $usuario = $stmt->fetchObject();
        if(!$usuario) {
            throw new PBX_Exception_NotFound("Usuario $userid nao encontrado");
        }

        $tech = substr($usuario->canal, 0, strpos($usuario->canal, '/'));

        if($tech == "SIP") {
            $interface = new PBX_Asterisk_Interface_SIP(array("username"=>$usuario->name, "secret"=>$usuario->secret));
        }
        else if($tech == "IAX2") {
            $interface = new PBX_Asterisk_Interface_IAX2(array("username"=>$usuario->name, "secret"=>$usuario->secret));
        }
        else if($tech == "MANUAL") {
            $interface = new PBX_Asterisk_Interface_VIRTUAL(array("channel"=> substr($usuario->canal, strpos($usuario->canal, '/')+1)));
        }
        else if($tech == "VIRTUAL") {
            $trunk = PBX_Trunks::get(substr($usuario->canal,strpos($usuario->canal, '/') +1 ));
            $interface = new PBX_Asterisk_Interface_VIRTUAL(array("channel"=> $trunk->getInterface()->getCanal() . "/" . $userid));
        }
        else if($tech == "KHOMP") {
            $khomp_id = substr($usuario->canal, strpos($usuario->canal, '/')+1);
            $khomp_board = substr($khomp_id, 1, strpos($khomp_id, 'c')-1);
            $khomp_channel = substr($khomp_id, strpos($khomp_id, 'c')+1);
            $interface = new PBX_Asterisk_Interface_KHOMP(array("board" => $khomp_board, "channel" => $khomp_channel));
        }
        else {
            throw new Exception("Tecnologia $tech desconhecida ou invalida.");
        }

        $user = new Snep_Ramal($usuario->name, $usuario->secret, $usuario->callerid, $interface);

        $user->setGroup($usuario->group);

        if($usuario->authenticate) {
            $user->lock();
        }

        if($usuario->dnd) {
            $user->DNDEnable();
        }

        if($usuario->sigame != "") {
            $user->setFollowMe($usuario->sigame);
        }

        if(is_numeric($usuario->pickupgroup)) {
            $user->setPickupGroup($usuario->pickupgroup);
        }

        if($usuario->usa_vc) {
            $user->setMailBox($usuario->mailbox);
            $user->setEmail($usuario->email);
        }

        return $user;
    }

    /**
     * Retorna todos os usuários do banco.
     *
     * @return Snep_Usuario array
     */
    public static function getAll() {
        $db = Zend_Registry::get('db');

        $select = $db->select('name')->from('peers')->where("peer_type='R' AND name != 'admin'");

        $stmt = $db->query($select);
        $usuarios = $stmt->fetchAll();

        $objetos = array();
        foreach($usuarios as $userid) {
            $objetos[] = self::get($userid['name']);
        }

        return $objetos;
    }

    /**
     * Retorna um array com todos os usuários pertencentes a determinado grupo.
     *
     * @param string $group
     * @return array Snep_Usuario $objetos
     */
    public static function getByGroup($group) {
        $db = Zend_Registry::get('db');

        $select = $db->select('name','group')->from('peers')->where("peer_type='R' AND name != 'admin'");

        $stmt = $db->query($select);
        $usuarios = $stmt->fetchAll();

        $objetos = array();
        foreach($usuarios as $usuario) {
            if(self::hasGroupInheritance($group, $usuario['group'])) {
                $objetos[] = self::get($usuario['name']);
            }
        }

        return $objetos;
    }

    /**
     * Verifica se um grupo sofre herança de outro. Se um grupo é filho de outro.
     *
     * A forma mais fácil que encontrei de fazer essa checagem é criar uma
     * instancia do Zend_Acl e colocar as informações todas lá e fazer uma
     * checagem simples.
     *
     * @param string $parent suposto pai
     * @param string $node  suposto filho
     * @return boolean resultado do teste
     */
    public static function hasGroupInheritance($parent, $node) {
                $db = Zend_Registry::get('db');
        $select = $db->select()
             ->from('groups')
             ->where("name != 'admin' AND name != 'users' AND name != 'all'");

        $stmt = $db->query($select);
        $groups = $stmt->fetchAll();

        $acl = new Zend_Acl();
        $acl->addRole(new Zend_Acl_Role('all'),null);
        $acl->addRole(new Zend_Acl_Role('users'), 'all');
        $acl->addRole(new Zend_Acl_Role('admin'), 'all');
        foreach ($groups as $group) {
            $inherit = ($group['inherit']) ? $group['inherit'] : null;
            $acl->addRole(new Zend_Acl_Role($group['name']), $inherit);
        }
        $acl->deny();
        $acl->allow($parent);
        return $acl->isAllowed($node);
    }
}
