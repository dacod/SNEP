#!/usr/bin/php-cgi -q
<?php

require_once('./agi_base.php');

$ramal = PBX_Usuarios::get($request->callerid);

if(!$ramal->isLocked()) {
    $db->update('peers', array("authenticate" => true));
    $asterisk->answer();
    $asterisk->stream_file('activated');
}
else if($ramal->isLocked()){
    $auth = $asterisk->exec('AUTHENTICATE', array($ramal->getPassword(),'',strlen((string)$ramal->getPassword())));
    if($auth['result'] == -1) {
        $log->info("Senha errada para desativar ramal $ramal");
    }
    else {
        $db->update('peers', array("authenticate" => false));
        $asterisk->answer();
        $asterisk->stream_file('de-activated');
    }
}