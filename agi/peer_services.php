#!/usr/bin/php -q
<?php
/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file Script agi que faz a resolução do canal (interface) de um ramal do snep
 */

// Importando as configurações para AGI's
require_once("agi_base.php");

if($argc != 2) {
    $asterisk->verbose("Este scripts aceita somente um ramal como parametro");
    exit(1);
}

$sigame = "";
// Procurando no banco pelo canal do peer
try {
    $ramal = PBX_Usuarios::get($argv[1]);

    if($ramal->getFollowMe() != "") {
        $ramal2 = PBX_Usuarios::get($ramal->getFollowMe());
        $sigame = $ramal2->getInterface()->getCanal();
    }

} catch (Exception $e) {
    $asterisk->verbose("[$requestid] Erro na resolucao de ramal: " . $e->getMessage(), 1);
    exit(1);
}

$asterisk->set_variable("DND", $ramal->isDNDActive()?"1":"0");
$asterisk->set_variable("SIGAME", "\"$sigame\"");
