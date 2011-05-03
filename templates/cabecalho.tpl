{*
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
 *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
   <head>
      <title>{$LANG.tit_sistema}</title>
      <link rel="icon" href="favicon.ico" type="images/x-icon" />
      <link rel="shortcut icon" href="favicon.ico" />
      <link rel="stylesheet" href="../css/{$CSS_TEMPL}.css" type="text/css" />
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <meta http-equiv="Content-Script-Type" content="text/javascript" />
      <meta http-equiv="imagetoolbar" content="false" />
      <meta http-equiv="Expires" content="Fri, 25 Dec 1980 00:00:00 GMT" />
      <meta http-equiv="Last-Modified" content="{php}gmdate('D, d M Y H:i:s'){/php} GMT" />
      <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
      <meta http-equiv="Cache-Control" content="post-check=0, pre-check=0" />
      <meta http-equiv="Pragma" content="no-cache" />
      <meta name="copyright" content="Opens Tecnologia&reg;" />
      <link rel="icon" type="image/png" href="../imagens/favicon.ico" />
      {$EXTRA_HEADERS}

      <script src="../includes/javascript/popup.js"></script>
      {if $debugger}
            <link rel="stylesheet" href="../css/debugger.css" type="text/css" />
      {/if}
      {if $PROTOTYPE}
            <script src="../includes/javascript/prototype.js"></script>
      {/if}
      {if $TARIFAS}
            <script src="../includes/javascript/prototype.js"></script>
            <script>
               function cidades(uf) {ldelim}
                  new Ajax.Updater('cidade', 'rel_cidades.php?uf=' + uf, {ldelim} method: 'get' {rdelim});
               {rdelim}
            </script>
      {/if}
      {if $REFRESH.mostrar}
         <script src="../includes/javascript/prototype.js"></script>
         <script>
             window.onload = function() {ldelim}
               var MyAjax = new Ajax.PeriodicalUpdater('mostrar','{$REFRESH.url}',
                   {ldelim}method: 'get',
                           asynchronous:true,
                           frequency:{$REFRESH.tempo}
                   {rdelim}
               );
            {rdelim}
            function agent_action(op,id) {ldelim}
               new Ajax.Request('agents.php?op=' + op +'&agent='+id,
                  {ldelim}
                    method:'get',
                    onSuccess: function(transport){ldelim}
                      var response = transport.responseText || "no response text";
                    {rdelim},
                    onFailure: function(){ldelim} alert('Falha ao fazer a requisição, verifique a sua conexão...') {rdelim}
                  {rdelim});
            {rdelim}
         </script>
      {/if}
      <script src="../includes/javascript/functions.js"></script>
      {if $MOSTRA_MENU}
            <!-- Ajusta Estilos se Browser for I.E. - problemas com Menus drop-down -->
            <script language="javascript" src="../includes/javascript/menus.js" type="text/javascript"> </script>
      {/if}
   </head>

   <body>
   <div id="container"> 
   <table style="border: none; padding: 0 ; margin: 0; width: 100%;" cellpadding="0" cellspacing="0">
      <tr>
         <td style="border: none; padding: 0 ; margin: 0">
             <a href="{$PATH_WEB}/src/sistema.php" alt="inicio" title="inicio">
                 <img src="{$LOGO_CLIENTE.name}" width="{$LOGO_CLIENTE.width}" height="{$LOGO_CLIENTE.height}" border="0" alt="" align="top">
             </a>
         </td>
         <td style="border: none; padding: 0 ; margin: 0">
            <img src="{$LOGO_SNEP.name}" width="{$LOGO_SNEP.width}" height="{$LOGO_SNEP.height}" border="0" alt="" align="top">
         </td>
      </tr>
   </table>
   {if $MOSTRA_MENU}
    <!-- Ajuda Contextual -->
   <div id="menu">
       <div id="ajuda">
           <a href="../src/ajuda.php?script={$SCRIPT_NAME}" rel="popup console 500 600 noicon">{$i18n->translate("Ajuda")}</a>
       </div>
       {$MENU}
   </div>
   <div id="titulo">
      {$TITULO}
   </div>
   {/if}
   {if $REFRESH.mostrar}
      <div id="mostrar">
         <center>
            <img style="margin-top:150px;" src="../imagens/ajax-loader.gif" width="32" height="32" />
         </center>
      </div>
   {/if}
