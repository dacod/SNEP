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
 * Controls the rendering of the breadcumb
 *
 * @author Henrique Grolli Bassotto
 */
class Snep_Breadcrumb {

    public static function renderPath($path) {
        if (is_array($path)) {
            $path[count($path)-1] = "<strong>" .mb_strtoupper($path[count($path)-1], 'utf-8') . "</strong>";
            $data = implode(" » ", $path);
            return $data;
        } else {
            return (string) $path;
        }
    }

}
