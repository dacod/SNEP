
 var Phone = Class.create({

  novo: function(id,phone) {
        elem = '<li id=\"phone_'+id+'\">';
        elem += '<input type=\"text\" name=\"phones[]\" value=\"'+phone+'\" />';
        elem += '<img src="/snep/imagens/delete.png" onclick=\"remover('+id+')\" />';
        elem += '</li>';
        $('phones').insert( elem );
        resortable();
  }
});

function remover(id) {
    $('phone_'+id).remove();
}

function newphone(value) {

    if(typeof value == "undefined") {
        value = "";
    }

    phones = $('phones').childElements();
    last = 0
    for (i = 0; i < phones.length ;i++) {        
        lstid = phones[i].id.substr(6);
        if(lstid > last) {
            last = lstid
        }
    }
    last++    
    obj = new Phone();
    obj.novo(last, value)
}

function resortable() {
   Sortable.create('phones',{tag:'li'});
}
