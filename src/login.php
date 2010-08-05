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

 session_start() ;
 session_destroy() ;
 require_once("../configs/config.php");
 session_start() ;
 if (array_key_exists ('login', $_POST)) {        
    try {
       $sql = "SELECT id,name,password,callerid,vinculo FROM peers " ;
       $sql.= " WHERE name = '".$_POST['user_login']."'";
       $row = $db->query($sql)->fetch();
    } catch  (Exception $e) {;
       display_error($LANG['error'].$e->getMessage(),true) ;
       $ERR= True ;
    }
    if (!isset($ERR)) {
       if ($_POST['user_login'] != $row['name'] || $_POST['user_senha'] !=    $row['password']) {
          display_error($LANG['msg_loginerror'],true);
          exit ;
       } else {
          $_SESSION['id_user'] = $id_user = $row['id'] ;
          $_SESSION['name_user'] = $name_user = $row['name'] ;
          $_SESSION['active_user'] = $active_user = $row['callerid'] ;
          $_SESSION['vinculos_user'] = $vinculos_user = $row['vinculo'] ;
          header ("Location: ../src/sistema.php");
       }
    }
 }
 display_template("login.tpl",$smarty);
?>
