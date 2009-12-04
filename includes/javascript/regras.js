
/*-----------------------------------------------------------------------------
 * Programa: regras.js - Script Prototype para habilitar/desabilitar regras de saida.
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Rafael Bozzetti <rafael@opens.com.br>
 * ----------------------------------------------------------------------------*/
    function regras(par) {
        
        if($('id' + par).hasClassName('regra0')) {

            troca_status(par)
            $('id' + par).removeClassName('regra0');
            $('id' + par).addClassName('regra1');

        }else{

            troca_status(par)
            $('id' + par).removeClassName('regra1');
            $('id' + par).addClassName('regra0');

        }
    }

    function troca_status(id) {
        var url = '../includes/troca_status.php';
        var params = 'id='+id;

        var retorno = new Ajax.Request (
            url, {
                  method: 'post',
                  parameters: params

                 }
        );
    }
