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
 * @file Script que faz uma ligação para um ramal usando a estrutura de Regras
 * do snep para funcionamento de features.
 */

// Importando as configurações para AGI's
require_once("agi_base.php");

$config = array(
    "dial_flags" => "t",
    "dial_timeout" => 60,
    "ramal" => $request->extension
);

$action = new PBX_Rule_Action_DiscarRamal();
$action->setConfig($config);

$action->execute($asterisk, $request);