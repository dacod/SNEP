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

function update_channel_list() {
    selected_board = document.getElementById('khomp_boards').value;

    new Ajax.Request('../src/khomp_channels.php', {
        method:'get',
        parameters: {value: 'channels', board: selected_board},
        requestHeaders: {Accept: 'application/json'},
        onSuccess: function(transport){
            document.getElementById('khomp_channels').innerHTML = transport.responseText;
        }
    });
}

function load_khomp(placa, canal) {
    new Ajax.Request('../src/khomp_channels.php', {
        method:'get',
        parameters: {value: 'channels', board: placa, selected: canal},
        requestHeaders: {Accept: 'application/json'},
        onSuccess: function(transport){
            document.getElementById('khomp_channels').innerHTML = transport.responseText;
        }
    });
}
