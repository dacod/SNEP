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
 * @file Script agi que faz a resolução do ramal do snep baseado na interface
 */

// Importando as configurações para AGI's
require_once("agi_base.php");

if($argc != 3) {
    $asterisk->verbose("Este scripts espera dois parametro");
    exit(1);
}

// Procurando no banco pelo canal do peer
try {
    $peer = PBX_Interfaces::getChannelOwner($argv[1]);
} catch (Exception $e) {
    $asterisk->verbose("[$requestid] Erro na resolucao de ramal: " . $e->getMessage(), 1);
    exit(1);
}

$asterisk->set_variable($argv[2], $peer->getNumero());