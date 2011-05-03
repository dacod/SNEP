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

require_once("../includes/verifica.php");
require_once("../configs/config.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Detalhe de aplicação</title>
        <link rel="stylesheet" href="../css/1024x768.css" type="text/css" />
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    </head>
    <body>
        <div id="contentHelp">
            <p>
                <pre>
            <?php

            $application = isset($_GET['app']) ? mysql_escape_string($_GET['app']) : "Dial";

            $asterisk = PBX_Asterisk_AMI::getInstance();
            $result = $asterisk->Command("core show application $application");

            echo $result['data'];

            ?>
                </pre>
            </p>
        </div>
        <div id="footer" style="padding-top: 10px;padding-left: 5px;">

            <input style="float:right;" type="button" class="new_button" value="Fechar" onClick="parent.close()"/>
            <?php echo $LANG['goto'] ?>: <a  href="../src/applications.php">Lista de Aplicações</a>
        </div>

    </body>
</html>