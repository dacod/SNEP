
/*-----------------------------------------------------------------------------
 * Programa: cadeado.js - Script Prototype para consulta do numero cadeado no banco.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Rafael Bozzetti <rafael@opens.com.br>
 * ----------------------------------------------------------------------------*/
    
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
        }else{
            
        }
    }