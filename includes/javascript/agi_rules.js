/**
 *  This file is part of SNEP.
 *  Para território Brasileiro leia LICENCA_BR.txt
 *  All other countries read the following disclaimer
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
    this.typeList = [];
    this.lastReference = null;

    this.typeList[this.typeList.push()]  = ['RX',str_regex ,true];
    this.typeList[this.typeList.push()]  = ['X',str_any, false];
    this.typeList[this.typeList.push()] = ['G',str_group, true];
    this.typeList[this.typeList.push()] = ['CG',str_contacts_group, true];
    this.typeList[this.typeList.push()] = ['R',str_ramal, true];
    this.typeList[this.typeList.push()] = ['AL',"Alias de Expressão", true];

    this.render = function() {
        $(this.id).innerHTML = this.getHtml(this.lastReference);
    };

    this.setType = function(type) {
        this.type = type;
        this.value = "";
        this.render();
    }

    this.getHtml = function(objReference) {
        this.lastReference = objReference;
        var html = '<span id="' + this.id + '">';
        html += '<select class="campos" onchange="' + objReference + '.setType(this.value);">';
        var showfield = true;
        var i;
        for(i=0;i<this.typeList.length;i++) {
            if(this.typeList[i][0] === this.type) {
                html += '<option selected="selected" value="' + this.typeList[i][0] + '">';
                showfield = (this.typeList[i][2])? true : false;
            }
            else {
                html += '<option value="' + this.typeList[i][0] + '">';
            }
            html += this.typeList[i][1] +'</option>';
        }
        html += "</select>";

        if(this.type === "T") { // Campo tronco
            html += ' <select class="campos" onchange="' + objReference + '.value = this.value;">';
            var selected = false;
            for(i=0; i < trunk_list.length; i++) {
                if(trunk_list[i][0] === this.value) {
                    html += '<option selected="selected" value="' + trunk_list[i][0] + '">';
                    selected = true;
                }
                else {
                    html += '<option value="' + trunk_list[i][0] + '">';
                }
                html += trunk_list[i][1] + '</option>';
            }
            if (selected === false) {
                this.value = trunk_list[0][0];
            }
            html += "</select>";
        } // fim campo tronco
        else if(this.type === "G") { // Campo grupo
            html += ' <select class="campos" onchange="' + objReference + '.value = this.value;">';
            var selected = false;
            for(i=0; i < group_list.length; i++) {
                if(group_list[i][0] === this.value) {
                    html += '<option selected="selected" value="' + group_list[i][0] + '">';
                    selected = true;
                }
                else {
                    html += '<option value="' + group_list[i][0] + '">';
                }
                html += group_list[i][1] + '</option>';
            }
            if (selected === false) {
                this.value = group_list[0][0];
            }
            html += "</select>";
        } // fim campo grupo
        else if(this.type === "CG") { // Campo grupo de contatos
            html += ' <select class="campos" onchange="' + objReference + '.value = this.value;">';
            var selected = false;
            for(i=0; i < contacts_group_list.length; i++) {
                if(contacts_group_list[i][0] === this.value) {
                    html += '<option selected="selected" value="' + contacts_group_list[i][0] + '">';
                    selected = true;
                }
                else {
                    html += '<option value="' + contacts_group_list[i][0] + '">';
                }
                html += contacts_group_list[i][1] + '</option>';
            }
            if (selected === false) {
                this.value = contacts_group_list[0][0];
            }
            html += "</select>";
        } // fim campo grupo de contatos
        else if(this.type === "AL") {
            html += ' <select class="campos" onchange="' + objReference + '.value = this.value;">';
            var selected = false;
            for(i=0; i < alias_list.length; i++) {
                if(alias_list[i][0] === this.value) {
                    html += '<option selected="selected" value="' + alias_list[i][0] + '">';
                    selected = true;
                }
                else {
                    html += '<option value="' + alias_list[i][0] + '">';
                }
                html += alias_list[i][1] + '</option>';
            }
            if ( selected === false ) {
                this.value = alias_list[0][0];
            }
            html += "</select>";
        }
        else if(showfield) {
            html += ' <input class="campos required" onchange="' + objReference + '.value = this.value;" value="' + this.value + '" type="text" />';
        }

        html += "</span>";
        return html;
    };

    this.getValue = function() {
        return this.type + (this.value !== "" ? ":" + this.value : "");
    };
}

/**
 * Prototipo de Campo de origem.
 */
function SrcField(id) {
    this.Field(id);

    if(trunk_list.length != 0){
        this.typeList[this.typeList.push()] = new Array('T',str_trunk, true);    
    }
    
}
// Definindo herança entre SrcField e Field;
copyPrototype(SrcField, Field);

/**
 * Prototipo de Campo de destino.
 */
function DstField(id) {
    this.Field(id);

    this.typeList[this.typeList.push()] = new Array('S',str_s, false);
}
// Definindo herança entre DstField e Field;
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
        html += 'De: <input type="text" onchange="' + objReference + '.startTime = this.value;" value="'+this.startTime+'" class="campos required" maxlength="5" size="5" onblur="valid_valida(this)"  /> hs\
                 Até: <input type="text" onchange="' + objReference + '.endTime = this.value;" value="'+this.endTime+'" class=" campos required" maxlength="5" size="5" onblur="valid_valida(this)"  />';
        html += "</span>";
        return html;
    }

    this.getValue = function() {
        return this.startTime + "-" + this.endTime;
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

active = null;
removed = null;

updateSortableActionsList = function() {
    Sortable.destroy("actions_list");

    Sortable.create("actions_list",{
        scroll:'actions-list-scrollable',
        onChange:function(element){
            $('actions-order').value = Sortable.serialize('actions_list')
        }
    });

    $('actions-order').value = Sortable.serialize('actions_list');
}

setActiveAction = function(element) {
    if (!$('actions_list').hasChildNodes()) {
        $('action-config-title').innerHTML = "";
        $('cleanActionsButton').disabled = true;
    }
    else if( element != removed) {
        if( element != null && element != active) {
            if(active != null) {
                Element.removeClassName(active, 'active');
                active.config_container.style.display = "none";
            }
            element.addClassName('active');
            active = element;
            active.config_container.style.display = "block";
            $('action-config-title').innerHTML = active.name;
        }
    }
    else {
        removed = null;
    }
}

cleanActions = function() {
    $('actions_list').innerHTML = "";
    $('actions-config').innerHTML = "";
    $('action-config-title').innerHTML = "";
    $('cleanActionsButton').disabled = true;
    updateSortableActionsList();
    setActiveAction(null);
}

removeAction = function(element) {
    removed = element;
    Element.remove(element.config_container);
    Element.remove(element);
    updateSortableActionsList();
    if( element == active ) {
        setActiveAction($('actions_list').firstChild);
    }
}

id = 0;
addNewAction = function(type) {
    $('addActionButton').disabled = true;
    $('loader_icon').style.display = "block";
    new Ajax.Request('/snep/gestao/actionform.php', {
        method: 'get',
        parameters: {
            mode:"new_action",
            id:'action_'+id,
            type:type,
            cachebuster: new Date().valueOf()
        },
        onSuccess: function(response) {
            var action = addAction(response.responseJSON);
            setActiveAction(action);
        },
        onFailure: function(response) {
            alert("Erro ao adicionar ação: " + response.responseJSON.message);
        },
        onComplete: function(response) {
            $('addActionButton').disabled = false;
            $('loader_icon').style.display = "none";
        }
    });
}

getRuleActions = function(ruleId) {
    var params = {
        mode:"get_rule_actions",
        rule_id: ruleId,
        cachebuster: new Date().valueOf()
    };

    new Ajax.Request('/snep/gestao/actionform.php', {
        method: 'get',
        parameters: params,
        onSuccess: function(response) {
            var act = 0;
            while(response.responseJSON["action_" + act] != null) {
                id = act;
                addAction(response.responseJSON["action_" + act]);
                act++;
            }
            setActiveAction($('actions_list').firstChild);
        },
        onFailure: function(response) {
            alert("Erro ao adicionar ação: " + response.responseJSON.message);
        }
    });
}

addAction = function(action_spec) {
    $('cleanActionsButton').disabled = false;
    var newAction = document.createElement('li');

    if(action_spec.status == "error") {
        Element.addClassName(newAction, "error");
    }
    
    newAction.setAttribute('id', action_spec.id);

    var caption           = $(action_spec.type).label;
    newAction.name        = caption;
    newAction.actionType  = action_spec.type;
    newAction.rawId       = action_spec.id;

    caption = "<a href='#' onclick=\"removeAction($('" + action_spec.id + "')); return false;\" style='float:right'>remover</a>" + caption;

    newAction.innerHTML = caption;

    Event.observe(newAction, 'click', function(){
        setActiveAction(newAction);
    });

    var config_container = document.createElement('div');
    config_container.setAttribute('id', 'action-config-' +id);
    newAction.config_container = config_container;
    config_container.innerHTML = action_spec.html(form);

    config_container.style.display = "none";
    $('actions-config').appendChild(config_container);
    $('actions_list').appendChild(newAction);
    updateSortableActionsList();
    id++;
    return newAction;
}

init = function() {
    Position.includeScrollOffsets = true;
    if(!$('actions_list').hasChildNodes()) {
        $('cleanActionsButton').disabled = true;
    }

    Event.observe($('addActionButton'), 'click', function(event) {
        addNewAction($('action-name').value);
    });

    Event.observe($('cleanActionsButton'), 'click', cleanActions);

    Event.observe($('routeForm'), 'submit', atualizaValues);
}

// Após o carregamento da janela, podemos começar a trabalhar com os elementos
Event.observe(window, 'load', init, false);