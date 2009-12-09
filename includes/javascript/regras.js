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

function regras(par) {
        
    if($('id' + par).hasClassName('regra0')) {

        troca_status(par)
        $('id' + par).removeClassName('regra0');
        $('id' + par).addClassName('regra1');

    }else{

        troca_status(par)
        $('id' + par).removeClassName('regra1');
        $('id' + par).addClassName('regra0');

    }
}

function troca_status(id) {
    var url = '../includes/troca_status.php';
    var params = 'id='+id;

    var retorno = new Ajax.Request (
        url, {
            method: 'post',
            parameters: params

        }
        );
}
