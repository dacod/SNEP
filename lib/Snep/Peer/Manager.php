<?php

class Snep_Peer_Manager extends Zend_Db_Table_Abstract {
	protected $_name = "peer";
	protected $_primary = "id_peer";
	
	protected $_dependentTables = array('Extension');
	
}