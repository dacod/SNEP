function remove_item(url,id,message) {
    if(confirm(message)) {
        var toUrl = url + '/remove/id/'+id
        if (!message) {
            window.location= url + '/remove/id/'+id
        }        
        var retorno = new Ajax.Request (
            toUrl, {
                method: 'post',        
                onComplete: function() { window.location = url }
            }
            );
    }
}