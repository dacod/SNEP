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

 require_once("../configs/config.php");
 $auth = md5($_POST['authenticate']);
 $name = $_POST['user'];
 
  if(!empty($auth))
  {
      try 
      {
           $sql = "SELECT name, authenticate FROM peers WHERE authenticate = '$auth' AND name != '$name' " ;
           $resultado = $db->query($sql)->fetch();
           
           if ($resultado > 0) {
                echo $LANG['error'].$LANG['senha_cadeado'];
           }else{
               echo '0';
           }
      } catch (Exception $e)
      {
           display_error($LANG['error'].$e->getMessage(),true) ;
           exit ;
      }
  }
