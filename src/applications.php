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
      <title>Applicações Instaladas</title>
      <link rel="stylesheet" href="../css/1024x768.css" type="text/css" />
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
   </head>
   <body>
       <div id="contentHelp">
           <?php

           $asterisk = PBX_Asterisk_AMI::getInstance();
           $result = $asterisk->Command("core show applications");
           preg_match_all("/(?<app>\w+): (?<desc>.*)/i", $result['data'], $applications);

           for($i = 1; $i < count($applications[0]); $i++) {
               $app = $applications['app'][$i];
               echo "<dt><a href=\"application_detail.php?app=$app\">$app</a></dt>\n";
               echo "<dd>" . $applications['desc'][$i] . "</dd>\n\n";
           }

           ?>
       </div>
   <div id="footer" style="padding-top: 10px;padding-left: 5px;">

      <input style="float:right;" type="button" class="new_button" value="Fechar" onClick="parent.close()"/>

      <?php echo $LANG['goto'] ?>: <a  href="../src/ajuda.php?script=agi_rules">Regras de Negócio</a>
   </div>

 </body>
</html>