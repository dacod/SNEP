#!/usr/bin/php -q
<?php
require_once('agi_base.php');

// Definindo nome do arquivo de gravação.
// Formato: Timestamp_aaaammdd_hhmm_src_dst.wav
$filename = implode("_", array(
    time(),
    date("Ymd"),
    date("Hi"),
    $request->getOriginalCallerid(),
    $request->getOriginalExtension()
));
// Definindo userfield com o nome do arquivo para que se possa encontrar a
// gravação a partir do registro no CDR.
$lastuserfield = $asterisk->get_variable('CDR(userfield)');
if($lastuserfield['data'] != "") {
    $asterisk->set_variable("CDR(userfield)", $lastuserfield['data']);
}
else {
    $asterisk->set_variable("CDR(userfield)", $filename);
}


// Variaveis sendo definidas para manutenção da compactibilidade com recurso
// legado do snep.
$asterisk->set_variable("__CALLFILENAME", $filename); // setando a variavel callfilename
$asterisk->set_variable("__TOUCH_MONITOR", $filename); // setando a variavel touch_monitor