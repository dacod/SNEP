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
    
/* Observa abertura da pagina e chama a função init() */    
Event.observe(window, 'load', init, false);
    

/* Observa objeto "authenticate" quando, onblur chama funcao recuperar */
function init() {
    Event.observe('authenticate', 'blur', recuperar, false);
}

    
/* Recupera valor do campo e submete ao var_auth.php */
function recuperar() {
    var url = '../includes/ver_auth.php';
    var params = 'authenticate='+escape( encodeURI($F('authenticate')))+'&user='+escape(encodeURI($F('name')));
        
    var retorno = new Ajax.Request (
        url, {
            method: 'post',
            parameters: params,
            onComplete: resposta
        }
        );
}

    
/* Retorna resultado para usuário */
function resposta( resp) {
    if ( resp.responseText != 0) {
        alert(decodeURI(resp.responseText));
        $('authenticate').value='';
    }
}