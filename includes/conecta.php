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

$dbname    = 'snep25';
$user      = 'snep';
$passwd    = 'sneppass';
$type_bd   = 'mysql';
$host      = 'localhost';
$dsn = "$type_bd:host=$host;dbname=$dbname" ;

try {
    $db = new PDO($dsn, $user, $passwd, array(PDO::ATTR_PERSISTENT => True)) ;
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
} catch (Exception $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    exit(1);
}
?>
