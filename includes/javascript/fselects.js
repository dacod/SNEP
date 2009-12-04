/*-----------------------------------------------------------------------------
 * Programa: fselects.js - Funcoes em JS para tropcar dados entre 2 <selects>
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ----------------------------------------------------------------------------*/
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
 
 