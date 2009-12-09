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
 * Ajuda em heranças, obrigado a http://www.sitepoint.com/blogs/2006/01/17/javascript-inheritance/
 */
function copyPrototype(descendant, parent) {
    var sConstructor = parent.toString();
    var aMatch = sConstructor.match( /\s*function (.*)\(/ );
    if ( aMatch != null ) { descendant.prototype[aMatch[1]] = parent; }
    for (var m in parent.prototype) {
        descendant.prototype[m] = parent.prototype[m];
    }
}

/**
 * Gerencia multiplos widgets e seus conteúdos.
 *
 * @param id          - id do elemento onde será renderizado o widget.
 * @param elementType - Tipo de elemento ao qual será gerenciado
 */
function MultiWx(id, elementType) {
    this.id = id;
    this.childNextId = 0;
    this.elementType = elementType;
    this.widgets = new Array();

    this.addItem = function(amount) {
        if(amount == undefined) {
            var position = this.widgets.push()
            this.widgets[position] = new this.elementType("" + this.id + "Widget" + this.childNextId);
            this.childNextId++;
            this.render();
        }
        else {
            for(var i=0; i < amount; i++) {
                var position = this.widgets.push()
                this.widgets[position] = new this.elementType("" + this.id + "Widget" + this.childNextId);
                this.childNextId++;
            }
        }
    }

    this.rmItem = function(index) {
        this.widgets.splice(index, 1);
        this.render();
    }

    /**
     * Renderiza novamente o widget.
     *
     * TODA INFORMAÇÃO NÃO SALVA SERÁ PERDIDA
     */
    this.render = function() {
        $(this.id).innerHTML = this.getHtml();
    }

    this.getHtml = function() {
        var html = "";
        for(var i=0; i < this.widgets.length; i++) {
            html += '<li id="' + this.id + i + '">';
            html += this.widgets[i].getHtml(this.id + 'Obj.widgets[' + i + ']');

            if(this.widgets.length > 1)
                   html += '<input class="boxremove help" title="Remover Regra" type="button" onclick="' + this.id + 'Obj.rmItem(' + i + ')" />';

            if(i == this.widgets.length - 1)
                html += '<input class="boxadd help" title="Adicionar Regra"  style="height:18px;" type="button" onclick="' + this.id + 'Obj.addItem()" />';

            html += '</li>';
        }
        return html;
    }

    this.getValue = function() {
        var value = "";
        for(var i=0; i < this.widgets.length; i++) {
            if(i > 0)
                value += ",";

            value += this.widgets[i].getValue();
        }
        return value;
    }
}

function StringField(id) {
    this.id        = id;
    this.value     = "";
    this.lastReference = null;

    this.render = function() {
        $(this.id).innerHTML = this.getHtml(this.lastReference);
    }

    this.getHtml = function(objReference) {
        this.lastReference = objReference;
        return '<input id="' + this.id + '" type="text" onchange="' + objReference + '.value = this.value;" value="'+this.value+'" class="box required" class="required box" style="width: 150px;height: 18px;"  />';
    }

    this.getValue = function() {
        return this.value;
    }
}