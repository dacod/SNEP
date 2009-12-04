/*-----------------------------------------------------------------------------
 * Programa: remove.js - Script Prototype para remocao de registros da tabela cdr.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Rafael Bozzetti <rafael@opens.com.br>
 * ----------------------------------------------------------------------------*/
    
    /* Observa abertura da pagina e chama a função init() */    
    Event.observe(window, 'load', init, false);
    

    /* Observa objeto o botao "rm_reg" e "compact" quando, onclick chama funcao recuperar ou compact */
    function init() {
        Event.observe('rmreg', 'click', recuperar, false);
        Event.observe('compact', 'click', compact, false);
    }
    
    /* Recupera valor do campo e submete ao remover.php */
    function recuperar() {
        var url = '../includes/remover.php';
        
        if ($F('rm_dia_ini') != '' && $F('rm_dia_fim') != '') {

            if(confirm("ATENCAO: Esta operacao removera arquivos e registros de gravacao. Esta certo disso?")) {

                  $('frescura').style.display='block';
                  var params = 'rm_dia_ini='+escape($F('rm_dia_ini'))+'&rm_dia_fim='+escape($F('rm_dia_fim'));
                  var retorno = new Ajax.Request (
                      url, {
                            method: 'post',
                            parameters: params,
                            onComplete: resposta
                           }
                  );
            }

        }else if($F('rm_dia_ini') == '' && $F('rm_dia_fim') == ''){
            alert("Sem parametros");
        }
    }

    
    /* Retorna resultado para usuário */
    function resposta(resp) {
        alert(resp.responseText);
        $('frescura').style.display='none';
        
    }


    /* Funcão de Compactacao do */
    function compact() {
        var data_ini = $F('dia_ini');
        var data_fim = $F('dia_fim');
        var action = $F('action');
        if(data_ini == '') {
            alert('Informe uma data de inicio.');            
            die;
        }
        if(data_fim == '') {
               alert('Informe uma data para final.');
        }
        else{
            janelaSecundaria('./compactar.php?di='+data_ini+'&df='+data_fim+'&type='+action,null,430,150);
        }
    }
