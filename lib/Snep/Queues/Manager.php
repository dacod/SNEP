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
 * Classe to manager a Queues.
 *
 * @see Snep_Queues_Manager
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2011 OpenS Tecnologia
 * @author    Rafael Pereira Bozzetti <rafael@opens.com.br>
 * 
 */
class Snep_Queues_Manager {

    public function __construct() {}

    /**
     * Get a queue by id
     * @param int $id
     * @return Array
     */
    public function get($name) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('queues')
            ->where("queues.name = ?", $name);

        $stmt = $db->query($select);
        $queue = $stmt->fetch();

        return $queue;
    }

    /**
     * Add a Queue.
     * @param array $queue
     * @return int
     */
    public function add($queue) {

        $db = Zend_Registry::get('db');

        $insert_data = array('name'              => $queue['name'],
                             'musiconhold'       => $queue['musiconhold'],
                             'announce'          => $queue['announce'],
                             'context'           => $queue['context'],
                             'timeout'           => $queue['timeout'],
                             'queue_youarenext'  => $queue['queue_youarenext'],
                             'queue_thereare'    => $queue['queue_thereare'],
                             'queue_callswaiting'=> $queue['queue_callswaiting'],
                             'queue_thankyou'    => $queue['queue_thankyou'],
                             'announce_frequency'=> $queue['announce_frequency'],
                             'retry'             => $queue['retry'],
                             'wrapuptime'        => $queue['wrapuptime'],
                             'maxlen'            => $queue['maxlen'],
                             'servicelevel'      => $queue['servicelevel'],
                             'strategy'          => $queue['strategy'],
                             'joinempty'         => $queue['joinempty'],
                             'leavewhenempty'    => $queue['leavewhenempty'],
                             'reportholdtime'    => $queue['reportholdtime'],
                             'memberdelay'       => $queue['memberdelay'],
                             'weight'            => $queue['weight']
                              );

        $db->insert('queues', $insert_data);   
    }

    /**
     * Edit a Queue
     * @param array $queue
     */
    public function edit($queue) {

        $db = Zend_Registry::get('db');

        $update_data = array('musiconhold'       => $queue['musiconhold'],
                             'announce'          => $queue['announce'],
                             'context'           => $queue['context'],
                             'timeout'           => $queue['timeout'],
                             'queue_youarenext'  => $queue['queue_youarenext'],
                             'queue_thereare'    => $queue['queue_thereare'],
                             'queue_callswaiting'=> $queue['queue_callswaiting'],
                             'queue_thankyou'    => $queue['queue_thankyou'],
                             'announce_frequency'=> $queue['announce_frequency'],
                             'retry'             => $queue['retry'],
                             'wrapuptime'        => $queue['wrapuptime'],
                             'maxlen'            => $queue['maxlen'],
                             'servicelevel'      => $queue['servicelevel'],
                             'strategy'          => $queue['strategy'],
                             'joinempty'         => $queue['joinempty'],
                             'leavewhenempty'    => $queue['leavewhenempty'],
                             'reportholdtime'    => $queue['reportholdtime'],
                             'memberdelay'       => $queue['memberdelay'],
                             'weight'            => $queue['weight']
                              );

        $db->update('queues', $update_data, "name = '{$queue['name']}'");
    }

    /**
     * Remove a Queue
     * @param int $name
     */
    public function remove($name) {

        $db = Zend_Registry::get('db');

        $db->beginTransaction();
        $db->delete('queues', "name = '$name'");

        try {
            $db->commit();

        } catch (Exception $e) {
            $db->rollBack();
        }            
    }

    /**
     * Get queue members
     * @param string $queue
     * @return array
     */
    public function getMembers($queue) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('queue_members')
            ->where("queue_members.queue_name = ?", $queue);

        $stmt = $db->query($select);
        $queuemember = $stmt->fetchAll();

        return $queuemember;
    }

    /**
     * Get all members
     * @return array
     */
    public function getAllMembers() {

        $db = Zend_Registry::get('db');

        $select = $db->select()
                ->from('peers', array('name', 'canal', 'callerid', 'group'))
                ->where("peers.name != 'admin'")
                ->where("peers.peer_type = 'R'")
                ->where("peers.canal != ''")                
                ->order("group");

        $stmt = $db->query($select);
        $allMembers = $stmt->fetchAll();

        return $allMembers;
    }

    /**
     * Remove queue members
     * @param string $queue
     */
    public function removeAllMembers($queue) {

         $db = Zend_Registry::get('db');

         $db->beginTransaction();
         $db->delete('queue_members', "queue_name = '$queue'");

        try {
            $db->commit();

        } catch (Exception $e) {
            $db->rollBack();
        }
    }

    /**
     * Insert member on queue
     * @param string $queue
     * @param string $member
     */
    public function insertMember($queue,$member) {

            $db = Zend_Registry::get('db');

            $insert_data = array('membername' => $member,
                                 'queue_name'   => $queue,
                                 'interface'    => $member );

            $db->insert('queue_members', $insert_data);
    }
}
