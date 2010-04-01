#!/usr/bin/php -q
<?php
/**
 *  This file is part of SNEP.
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
 * @file Script agi que faz a resolu��o do ramal do snep baseado na interface
 */

// Importando as configura��es para AGI's
require_once("agi_base.php");

if($argc < 2) {
    $asterisk->verbose("Este scripts espera um nome de variavel como parametro");
    exit(1);
}

if($argc == 3) {
    $raw_channel = $argv[2];
}
else {
    $raw_channel = $asterisk->request['agi_channel'];
}

$channel = substr($raw_channel, 0, strpos($raw_channel, '-'));

$asterisk->set_variable($argv[1], $channel);
