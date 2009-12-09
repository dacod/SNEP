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

 function movimento(elemento, direcao) {
    var sel = document.getElementById(elemento);
    var len, i;
    if (!sel) {
       return;
    }
    if (direcao == 'passar' && arguments[2] == undefined) {   
       return;
    } else if (direcao == 'passar') {
       var sel_pai = document.getElementById(arguments[2]);
       var selecionados = new Array();
       if (!sel_pai) {
          return;
       }
       len = sel_pai.options.length;
       for (i = 0; i < len; i++) {
          if (sel_pai.options[i].selected) {
             sel.options[sel.options.length] = new Option(sel_pai.options[i].text, sel_pai.options[i].value);
             selecionados.push(i);
          }
       }
       len = selecionados.length;
       for (i = len-1; i >= 0; i--) {
          sel_pai.options[selecionados[i]] = null;
       }
    } else if (direcao == 'cima' || direcao == 'baixo') {
       var selecionado = sel.selectedIndex;
       var comparacao = direcao == 'cima' ? selecionado - 1 : selecionado;
       var opts_values = new Array();
       var opts_texts = new Array();
       var tam = sel.options.length;
       var i;
       if (selecionado == -1) {
          return;
       }
       if (direcao == 'cima' && selecionado == 0) {
          return;
       }
       if (direcao == 'baixo' && selecionado == tam - 1) {
          return;
       }
       selecionado = direcao == 'cima' ? selecionado - 1 : selecionado + 1;
       for (i = 0; i < sel.options.length; i++) {
           if (i == comparacao) {
              opts_values.push(sel.options[i+1].value);
              opts_texts.push(sel.options[i+1].text);
              sel.options[i + 1] = null;
           }
           opts_values.push(sel.options[i].value);
           opts_texts.push(sel.options[i].text);
       }
       for (i = 0; i < tam; i++) {
            sel.options[i] = new Option(opts_texts[i], opts_values[i]);
       }
       sel.selectedIndex = selecionado;
   }
}
 
 