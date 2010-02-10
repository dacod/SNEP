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

/**
 * Protótipo para campo de Origens e Destinos.
 */
function Field(id) {
    this.id = id;
    this.type = "X";
    this.value = '';
    this.typeList = new Array();
    this.lastReference = null;

    this.typeList[0]  = new Array('RX',str_regex ,true);
    this.typeList[1]  = new Array('X',str_any, false);

    this.render = function() {
        $(this.id).innerHTML = this.getHtml(this.lastReference);
    }

    this.getHtml = function(objReference) {
        this.lastReference = objReference;
        var html = '<span id="' + this.id + '">';
        html += '<select class="campos" onchange="' + objReference + '.type = this.value; ' + objReference + '.value = \'\'; ' + objReference + '.render()">';
        var showfield = true;
        for(var i=0;i<this.typeList.length;i++) {
            if(this.typeList[i][0] == this.type) {
                html += '<option selected="selected" value="' + this.typeList[i][0] + '">';
                showfield = (this.typeList[i][2])? true : false;
            }
            else {
                html += '<option value="' + this.typeList[i][0] + '">';
            }
            html += this.typeList[i][1] +'</option>';
        }
        html += "</select>";

        if(this.type == "T") { // Campo tronco
            html += ' <select class="campos" onchange="' + objReference + '.value = this.value;">';
            html += '<option> - - </option>';
            for(i=0; i < trunk_list.length; i++) {
                if(trunk_list[i][0] == this.value) {
                    html += '<option selected="selected" value="' + trunk_list[i][0] + '">';
                }
                else {
                    html += '<option value="' + trunk_list[i][0] + '">';
                }
                html += trunk_list[i][1] + '</option>';
            }
            html += "</select>";
        } // fim campo tronco
        else if(this.type == "G") { // Campo grupo
            html += ' <select class="campos" onchange="' + objReference + '.value = this.value;">';
            html += '<option> - - </option>';
            for(i=0; i < group_list.length; i++) {
                if(group_list[i][0] == this.value) {
                    html += '<option selected="selected" value="' + group_list[i][0] + '">';
                }
                else {
                    html += '<option value="' + group_list[i][0] + '">';
                }
                html += group_list[i][1] + '</option>';
            }
            html += "</select>";
        } // fim campo grupo
        else if(this.type == "CG") { // Campo grupo de contatos
            html += ' <select class="campos" onchange="' + objReference + '.value = this.value;">';
            html += '<option> - - </option>';
            for(i=0; i < contacts_group_list.length; i++) {
                if(contacts_group_list[i][0] == this.value) {
                    html += '<option selected="selected" value="' + contacts_group_list[i][0] + '">';
                }
                else {
                    html += '<option value="' + contacts_group_list[i][0] + '">';
                }
                html += contacts_group_list[i][1] + '</option>';
            }
            html += "</select>";
        } // fim campo grupo de contatos
        else if(showfield) {
            html += ' <input class="campos box required" onchange="' + objReference + '.value = this.value;" value="' + this.value + '" type="text" />';
        }

        html += "</span>";
        return html;
    }

    this.getValue = function() {
        return this.type + (this.value != "" ? ":" + this.value : "");
    }
}

/**
 * Prototipo de Campo de origem.
 */
function SrcField(id) {
    this.Field(id);

    this.typeList[this.typeList.push()]  = new Array('T',str_trunk, true);
    this.typeList[this.typeList.push()]  = new Array('G',str_group, true);
    this.typeList[this.typeList.push()]  = new Array('CG',str_contacts_group, true);
    this.typeList[this.typeList.push()]  = new Array('R',str_ramal, true);
}
// Definindo herança entre SrcField e Field;
copyPrototype(SrcField, Field);

/**
 * Prototipo de Campo de destino.
 */
function DstField(id) {
    this.Field(id);

    this.typeList[this.typeList.push()]  = new Array('S',str_s, false);
    this.typeList[this.typeList.push()]  = new Array('G',str_group, true);
}
// Definindo herança entre SrcField e Field;
copyPrototype(DstField, Field);

/**
 * Protótipo de campo de Tempos
 */
function TimeField(id) {
    this.id        = id;
    this.value     = "";
    this.startTime = '00:00';
    this.endTime   = '23:59';
    this.lastReference = null;

    this.render = function() {
        $(this.id).innerHTML = this.getHtml(this.lastReference);
    }

    this.getHtml = function(objReference) {
        this.lastReference = objReference;
        var html = '<span id="' + this.id + '">';
        html += 'De: <input type="text" onchange="' + objReference + '.startTime = this.value;" value="'+this.startTime+'" class="campos box required" maxlength="5" size="5" onblur="valid_valida(this)"  /> hs\
                 Até: <input type="text" onchange="' + objReference + '.endTime = this.value;" value="'+this.endTime+'" class=" campos box required" maxlength="5" size="5" onblur="valid_valida(this)"  />';
        html += "</span>";
        return html;
    }

    this.getValue = function() {
        return this.startTime + "-" + this.endTime;
    }
}

indexes = new Array();

/**
 * Classe que define as ações para as regras de negocio.
 * $author: Rafael Bozzetti <rafael@opens.com.br>
 */
var Acoes = Class.create({
    tipo : null,

    /* Função inicializa a classe e coleta dados iniciais (ccustos, troncos, filas etc..) */
    initialize : function(tipo) {

        $('indice').value = 1;
                    
    },
                
    /* Função responsável por criar uma nova ação de tronco. */
    newtrunk : function(valor,cc,to,tl,omo,fg,em) {
        cc = remontaCc(cc);
        tk = remontaTrunk(valor);
                        
        var num = $('indice').value;
        var sn = 't'+num;
        var okgsm;
                                                
        if(omo == 1){
            okgsm = 'checked';
        }else{
            okgsm = '';
        }
        if(!fg) {
            fg = "TWK";
        }

        var html = "<li style=\'height: 80px;\' name='"+sn+"' id='"+sn+"'>  <img style=\"float:right;\" title=\"Apagar ação\"  src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\" /> " +
        "<strong>Direcionar para Tronco</strong><br /> " +
        "Centro de Custo: <select id="+sn+"cc name="+sn+"cc class=\"minisel\"> "+ cc +"   </select> " +
        "Tronco: <select id="+sn+"tnk name="+sn+"tnk class=\"minisel\"> "+ tk +" </select>  " +
        "<input type=\"checkbox\" "+okgsm+" name="+sn+"omo > Omitir Origem (Somente KGSM)<br />\n" +
        "Timeout Completamento: <input style='width:30px;' class=\"minibox required validate-number\" name="+sn+"to type=\"text\" value="+to+"> " +
        "Limitar Tempo Ligação: <input class=\"minibox required validate-number\"  name="+sn+"tl type=\"text\" value="+tl+" > " +
        "Parametros: <input class=\"minibox\" required name="+sn+"fg type=\"text\" value="+fg+" ><br />" +
        "Emails para alerta: <input class=\"miniemail\" type='text' size='30' name='"+sn+"em' value='"+em+"' /> </li>";

        $('myList').insert(html);
        $('indice').value = ++num;
                        
        var ids = $('ids').value;
        $('ids').value = ids + sn + ',' ;
    },

    /* Função responsável por criar uma nova ação de alteração de origem/destino. */
    newalterar : function(valor,cc,to,tl,omo,fg) {
        var num = $('indice').value;
        var sn = 'a'+num;
        var ccsrc = cc == 'src' ? 'checked' : '';
        var ccdst = cc == 'dst' ? 'checked' : '';

        var ctnocut   = valor == 'nocut' ? 'checked' : '';
        var ctpipecut = valor == 'pipecut' ? 'checked' : '';

        var html = "<li style=\'height: 60px;\' name='"+sn+"' id='"+sn+"'>  <img style=\"float:right;\" title=\"Apagar ação\" src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\" /> " +
        "<strong>Editar Origem / Destino</strong>" +
        '<input type="radio" value="src" id="'+sn+'src" '+ccsrc+' name="'+sn+'ct" /> <label for="'+sn+'src">Origem</label> <input type="radio" '+ccdst+' value="dst" name="'+sn+'ct" id="'+sn+'dst" /> <label for="'+sn+'dst">Destino</label>' +
        "<br /><strong>Cortar</strong>" +
        '<input type="radio" value="nocut" id="'+sn+'nocut" '+ctnocut+' name="'+sn+'cc" /> <label for="'+sn+'nocut">Não cortar</label> <input type="radio" '+ctpipecut+' value="pipecut" name="'+sn+'cc" id="'+sn+'pipecut" /> <label for="'+sn+'pipecut">Cortar no pipe "|"</label>' +
        "<strong style=\"margin-left: 20px;\">Anexar</strong> " +
        " Prefixo: <input class=\"minibox\" name="+sn+"to type=\"text\" value="+to+"> " +
        "Sufixo: <input class=\"minibox \"  name="+sn+"tl type=\"text\" value="+tl+" ></td></tr></table> </div>";
        $('myList').insert(html);
        $('indice').value = ++num;
        var ids = $('ids').value;
        $('ids').value = ids + sn + ',';
    },

    /* Função responsável por criar uma nova ação de definição de origem/destino. */
    newdefine : function(valor,cc) {
        var num = $('indice').value;
        var sn = 'd'+num;

        var html = "<li style=\'height: 60px;\' name='"+sn+"' id='"+sn+"'>  <img style=\"float:right;\" title=\"Apagar ação\" src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\" /> " +
        "<strong>Definir Origem/Destino</strong><br /> " +
        "Origem: <input type='text' name="+sn+"cc class=\"miniemail\" value='"+cc+"' /> " +
        "Destino: <input type='text' name="+sn+"ct class=\"miniemail\" value='"+ valor +"' /> </li> ";
        $('myList').insert(html);
        $('indice').value = ++num;
        var ids = $('ids').value;
        $('ids').value = ids + sn + ',' ;
    },

    /* Função responsável por criar uma nova ação de restauração. */
    newrestore : function(valor,cc) {
        var num = $('indice').value;
        var sn = 'r'+num;

        valor = valor == 1 ? 'checked="checked"' : '';
        cc = cc == 1 ? 'checked="checked"' : '';

        var html = "<li style=\'height: 60px;\' name='"+sn+"' id='"+sn+"'>  <img style=\"float:right;\" title=\"Apagar ação\" src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\" /> " +
        "<strong>Restaurar Origem/Destino</strong><br /> " +
        "<input type='checkbox' name="+sn+"cc class=\"campos\" "+ cc +" id='"+sn+"cc' /> <label for='"+sn+"cc'>Origem</label> " +
        "<input type='checkbox' name="+sn+"ct class=\"campos\" "+ valor +" id='"+sn+"ct' /> <label for='"+sn+"ct'>Destino</label></li> ";
        $('myList').insert(html);
        $('indice').value = ++num;
        var ids = $('ids').value;
        $('ids').value = ids + sn + ',' ;
    },

    /* Função responsável pro criar uma nova ação de fila. */
    newqueue : function(valor,cc,to) {
        cc = remontaCc(cc);
        fl = remontaFilas(valor);

        var num = $('indice').value;
        var sn = 'q'+num;
        var html = "<li style=\'height: 60px;\' name='"+sn+"' id='"+sn+"'>     <img style=\"float:right;\" title=\"Apagar ação\" src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\"  /> " +
        "<strong>Direcionar para Fila</strong><br /> " +
        "Centro de Custo: <select id="+sn+"cc name="+sn+"cc class=\"campos\"> "+ cc +" </select> " +
        "Fila: <select id="+sn+"fl name="+sn+"fl class=\"campos\"> "+ fl +"</select>  " +
        "TimeOut: <input class=\"minibox required validate-number\" name="+sn+"to type=\"text\" value="+to+"> ";
        $('myList').insert(html);
        $('indice').value = ++num;
                        
        var ids = $('ids').value;
        $('ids').value = ids + sn + ',';
    },

    /* Função responsável pro criar uma nova ação de loop. */
    newloop : function(valor,cc) {
        var num = $('indice').value;
        var sn = 'l'+num;

        var html = "<li style=\'height: 60px;\' name='"+sn+"' id='"+sn+"'><img style=\"float:right;\" title=\"Apagar ação\" src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\"  /> " +
        "<strong>Loop em Ação</strong><br /> " +
        "Repetir: <input type=\"text\" name="+sn+"ct class=\"minibox required validate-number\" value=\"" + valor +"\"/> vezes " +
        "<span style=\'margin-left: 20px;\'>Indice da Ação: <input type=\"text\" name="+sn+"cc class=\"minibox required validate-number\" value='"+cc+"' /></span>";
        $('myList').insert(html);
        $('indice').value = ++num;
        var ids = $('ids').value;
        $('ids').value = ids + sn + ',';
    },

    /* Função responsável pro criar uma nova ação de contexto. */
    newcontext : function(valor,cc) {
                        
        var num = $('indice').value;
        var sn = 'c'+num;
        var html = "<li style=\'height: 60px;\' name='"+sn+"' id='"+sn+"'>    <img style=\"float:right;\" title=\"Apagar ação\" src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\"  /> " +
        "<strong>Direcionar para Contexto</strong><br /> " +
        "Contexto: <input class=\"miniboxct required\" name="+sn+"ct type=\"text\" value="+valor+"> " ;
        $('myList').insert(html);
        $('indice').value = ++num;
        var ids = $('ids').value;
        $('ids').value = ids + sn + ',';
    },

    /* Função responsável pro criar uma nova ação de cadeado. */
    newpadlock : function(valor,cc) {
        var num = $('indice').value;
        var sn = 'p'+num;
        
        if(cc == "true") {
            cc = "checked";
        }

        var html = "<li style=\'height: 60px;\' name='"+sn+"' id='"+sn+"'><img style=\"float:right;\" title=\"Apagar ação\" src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\"  /> " +
        "<strong>Cadeado</strong><br /> " +
        "Senha: <input class=\"miniboxct required\" name="+sn+"ct type=\"text\" value="+valor+"> <br />"+
        '<input type="checkbox" name="'+sn+'cc" '+cc+' /> Requisitar e substituir ramal de origem';
        $('myList').insert(html);
        $('indice').value = ++num;
        var ids = $('ids').value;
        $('ids').value = ids + sn + ',';
    },

    /* Função responsável pro criar uma nova ação de Ramal. */
    newexten : function(valor,cc,to,tl,omo, fg, em) {
        cc = remontaCc(cc);
        var num = $('indice').value;
        var sn = 'e'+num;
                        
        if(!tl) {
            tl = "twk";
        }

        if(omo == "true") {
            omo = "checked";
        }

        if(fg == "true") {
            fg = "checked";
        }

        if(em == "true") {
            em = "checked";
        }

        var html = "<li style=\'height: 60px;\' name='"+sn+"' id='"+sn+"'><img style=\"float:right;\" title=\"Apagar ação\" src=\"../imagens/delete.png\" onclick=\"removenode('"+sn+"'); return false;\"  />" +
        "<strong>Direcionar para Ramal</strong><br /> " +
        "Centro de Custo: <select id="+sn+"cc name="+sn+"cc class=\"campos\"> "+cc+" </select> " +
        "Ramal: <input class=\"minibox validate-number\" name="+sn+"rm  id="+sn+"rm type=\"text\" onblur=\"verificaRamal('"+sn+"rm'); return false;\" value="+valor+"  > " +
        "Timeout Completamento: <input class=\"minibox required validate-number\" name="+sn+"to type=\"text\" value="+to+"><br />" +
        "Parametros: <input class=\"minibox\" required name="+sn+"tl type=\"text\" value="+tl+" >" +
        '<input type="checkbox" name="'+sn+'omo" '+omo+' /> Não Transbordar &nbsp;' +
        '<input type="checkbox" name="'+sn+'fg" '+fg+' /> Diferenciar toque' +
        '<input type="checkbox" name="'+sn+'em" '+em+' /> Permitir Voicemail';
        $('myList').insert(html);
        $('indice').value = ++num

        var ids = $('ids').value;
        $('ids').value = ids + sn + ',';
    },

    /* Função de criacao de cada node. receber o tipo de acao como parametro e contabiliza os itens. */
    newnode : function(tipo,cc,valor,to,tl,omo,fg, em) {
        $('semacao').hide();

        if(conta_nodes()) {
            var t;
            if(tipo == 'trunk') {
                t = 't';
                x.newtrunk(valor,cc,to,tl,omo,fg,em);
            }
            if(tipo == 'context') {
                t = 'c';
                x.newcontext(valor,cc);
            }
            if(tipo == 'queue') {
                t = 'q';
                x.newqueue(valor,cc,to);
            }
            if(tipo == 'exten') {
                t = 'e';
                x.newexten(valor,cc,to,tl,omo, fg, em);
            }
            if(tipo == 'define') {
                t = 'd';
                x.newdefine(valor,cc);
            }
            if(tipo == 'restore') {
                t = 'r';
                x.newrestore(valor,cc);
            }
            if(tipo == 'alterar') {
                t = 'a';
                x.newalterar(valor, cc, to, tl, omo, fg);
            }
            if(tipo == 'padlock') {
                t = 'p';
                x.newpadlock(valor,cc);
            }
            if(tipo == 'loop') {
                t = 'l';
                x.newloop(valor,cc);
            }
            // Mantendo controle dinamico dos indices.
            indexes[indexes.push()] = t + ($('indice').value-1) + '';
        }else{
            alert("Existe o limite de 9 acoes para uma regra.");
        }
    }
});


/* Cria objeto */
x = new Acoes();

/* Função à parte para remover objetos da lista */
function removenode(id) {
    $(id).remove();
    indexes.splice(indexes.indexOf(id), 1); // Removendo do Array de controle

    var empty = $('myList').childElements();
    if(empty == 0 ) {
        $('semacao').show();
    }

    var str = $('ids').value;
    var inicio = str.indexOf(id);
    var fim = inicio + 3;
    $('ids').value = str.substring(0,inicio) + str.substring(fim);
}

/* Função que conta */
function conta_nodes() {
    var count = $('myList').childElements();
    if(count.length >= 9) {
        return false;
    }
    else {
        return true;
    }
}

/* Função para validação de horario de inicio e fim da regra */
function valida_hora(item,hora) {
    if(hora.match('^([0-1][0-9]|[2][0-3])(:([0-5][0-9])){1,2}$')) {
        $(item).removeClassName('validation-failed');
    }else{
        $(item).addClassName('validation-failed');
    }
}
    
// Remonta lista de troncos e marca 1 como selecionado, e grava a nova lista no cookie.
    
function remontaTrunk(select) {
    var tnk = select;
        
    /* Ajax.Request pegando todos as Filas */
    var html = '';
    html += '<option> - - </option>';
    for(i=0; i < trunk_list.length; i++) {
        if(trunk_list[i][0] == tnk) {
            html += '<option selected="selected" value="' + trunk_list[i][0] + '">';
        }
        else {
            html += '<option value="' + trunk_list[i][0] + '">';
        }
        html += trunk_list[i][1] + '</option>';
    }
    return html;
}
    
// Remonta lista de Filas e marca 1 como selecionado, e grava a nova lista no cookie.    
function remontaFilas(select) {
    var fil = select;
    /* Ajax.Request pegando todos as Filas */
    var html = '';
    html += '<option> - - </option>';
    for(i=0; i < filas_list.length; i++) {
        if(filas_list[i][1] == fil) {
            html += '<option selected="selected" value="' + filas_list[i][1] + '">';
        }
        else {
            html += '<option value="' + filas_list[i][1] + '">';
        }
        html += filas_list[i][1] + '</option>';
    }
    return html;
}
    
// Remonta lista de Centro de Custos e marca 1 como selecionado, e grava a nova lista no cookie.
function remontaCc(select) {
    var cc = select;
    var html = '';
    html += '<option> - - </option>';
    for(i=0; i < ccusto_list.length; i++) {
        if(ccusto_list[i][0] == cc) {
            html += '<option selected="selected" value="' + ccusto_list[i][0] + '">';
        }
        else {
            html += '<option value="' + ccusto_list[i][0] + '">';
        }
        html += ccusto_list[i][1] + '</option>';
    }
    return html;
}
    
/* Função que testa a existencia do ramal no banco. */
function verificaRamal(item) {
    var x = $F(item);
    if(x != '') {
        new Ajax.Request('../includes/ramais.php', {
            method: 'post',
            parameters: 'ramal='+x,
            onSuccess: function(h) {
                if(h.responseText == "0") {
                    alert('Este ramal nao esta cadastrado.');
                    $(item).addClassName('validation-failed');
                }else{
                    $(item).removeClassName('validation-failed');
                }

            }
        });
    }
}