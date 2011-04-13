<?php
/**
 *  This file is part of SNEP.
 *
 *  SNEP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as
 *  published by the Free Software Foundation, either version 3 of
 *  the License, or (at your option) any later version.
 *
 *  SNEP is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with SNEP.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 */

/**
 *  Class that  controls  the  persistence  in  database  of business rules
 * the Snep.
 *
 * Note about  persistence: The  persistence  control  is  done  in  the  SNEP
 * separate classes. Not in the constructor of the class model as is seen in other
 * Frameworks and architectures. The reason is that if a change in
 * how it is made ​​the persistence of these objects need not be the same
 * changed. This increases the compactness with legacy code and facilitates
 * migration of code between versions.
 * ~henrique
 *
 * @category  Snep
 * @package   Snep
 * @copyright Copyright (c) 2010 OpenS Tecnologia
 * @author Henrique Grolli Bassotto
 */
class Snep_ConferenceRooms_Manager {

    private function __construct() { /* Protegendo métodos dinâmicos */ }
    private function __destruct() { /* Protegendo métodos dinâmicos */ }
    private function __clone() { /* Protegendo métodos dinâmicos */ }


}
?>
