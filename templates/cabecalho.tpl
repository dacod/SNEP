{*
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
      <!--<script src="../includes/javascript/status_asterisk.js"></script> -->
      <meta name="copyright" content="Opens Tecnologia&reg;" />
      <link rel="icon" type="image/png" href="../imagens/favicon.ico">

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
            <img src="{$LOGO_CLIENTE.name}" width="{$LOGO_CLIENTE.width}" height="{$LOGO_CLIENTE.height}" border="0" alt="" align="top">
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
              <a href="../src/ajuda.php?script={$SCRIPT_NAME}" rel="popup console 500 600 noicon">{$LANG.help}</a>
       </div>
       
       <ul id="navmenu">
        
         <!-- Sair do Sistema -->
         <li><a href="../src/logout.php">{$LANG.menu_sair}</a></li>
         <!-- Menu Configuracoes -->
         {if $PERM_MENUCONF} 
         <li><a href="#">{$LANG.menu_config} +</a>
            <ul>
               <li onclick="javascript:window.location.href='../configs/parametros.php'"><a href="#">{$LANG.menu_params}</a></li>

               <li onclick="javascript:window.location.href='../src/rel_sounds.php'"><a href="#">{$LANG.menu_sounds}</a></li>
               <li onclick="javascript:window.location.href='../gestao/rel_musiconhold.php'"><a href="#">{$LANG.menu_musiconhold}</a></li>
            </ul>
         </li>
         {/if}
         {if $CONTACTS && $PERM_MENUCONTACTS}
         <!--� Menu Contatos / Tarifas -->
         <li><a href="#">{$LANG.menu_sms} +</a>
             <ul>
                 <li onclick="javascript:window.location.href='../contacts/rel_cont_groups.php'"><a href="#">{$LANG.menu_contacts_groups}</a></li>
                 <li onclick="javascript:window.location.href='../contacts/rel_cont_names.php'"><a href="#">{$LANG.menu_contacts}</a></li>
                 <li onclick="javascript:window.location.href='../contacts/rel_sms_campaigns.php'"><a href="#">{$LANG.menu_sms_campaigns}</a></li>
             </ul>
         </li>
         {/if}
         <!-- Menu de Regras de Negócios -->
         {if $PERM_MENURULES}
         <li><a href="../gestao/rel_agi_rules.php">{$LANG.menu_rules}</a>
             <ul>
                 <li><a href="../gestao/rel_agi_rules.php">{$LANG.routes}</a></li>
                 <li><a href="../gestao/default_actions_configs.php">{$LANG.default_configs}</a></li>
             </ul>
         </li>
         {/if}
         <!--  Menu Tarifas -->
         {if $PERM_MENUTARIF}
         <li><a href="#">{$LANG.menu_tarifas} +</a>
            <ul>
               <li onclick="javascript:window.location.href='../tarifas/rel_operadoras.php'"><a href="#">{$LANG.menu_operadoras}</a></li>
               <li onclick="javascript:window.location.href='../tarifas/rel_tarifas.php'"><a href="#">{$LANG.menu_tarifas}</a></li>
            </ul>
         </li>
         {/if}
         <!--  Menu Relatorios -->
         {if $PERM_MENUREL}
         <li><a href="#">{$LANG.menu_reports} +</a>
            <ul>
                <li onclick="javascript:window.location.href='../src/rel_chamadas.php'"><a href="#">{$LANG.menu_rel_callers}</a></li>
                <li onclick="javascript:window.location.href='../src/rel_services.php'"><a href="#">{$LANG.services_report}</a></li>
                <li onclick="javascript:window.location.href='../src/rel_ranking.php'"><a href="#">{$LANG.menu_callranking}</a></li>
            </ul>
         </li>
         {/if}
         <!--  Menu Cadastros -->
         {if $PERM_MENUCAD}
         <li><a href="#">{$LANG.menu_register} +</a>
            <ul>
               <li onclick="javascript:window.location.href='../src/rel_ccustos.php'"><a href="#">{$LANG.menu_ccustos}</a></li>
               <li onclick="javascript:window.location.href='../src/rel_cont_names.php'"><a href="#">{$LANG.menu_contacts}</a></li>               
               <li onclick="javascript:window.location.href='../src/rel_queues.php'"><a href="#">{$LANG.menu_queues}</a></li>
               <li onclick="javascript:window.location.href='../src/rel_groups.php'"><a href="#">{$LANG.menu_grupos_ramais}</a></li>
               <li onclick="javascript:window.location.href='../src/rel_grupos.php'"><a href="#">{$LANG.menu_grupos}</a></li>
               <li onclick="javascript:window.location.href='../src/rel_ramais.php'"><a href="#">{$LANG.menu_ramais}</a></li>
               <li onclick="javascript:window.location.href='../gestao/conferencias.php'"><a href="#">{$LANG.menu_conference}</a></li>
               <li onclick="javascript:window.location.href='../src/rel_troncos.php'"><a href="#">{$LANG.menu_troncos}</a></li> 
           </ul>
        </li>
        {/if}
        <li><a href="#">{$LANG.status} +</a>
             <ul>
               <li onclick="javascript:window.location.href='../src/sistema.php'"><a href="#">{$LANG.menu_system}</a></li>
               <!-- Links -->
               {if $KHOMP}
                  <li onclick="javascript:window.location.href='../gestao/links_load.php'"><a href="#">{$LANG.menu_links}</a></li>
               {/if}
               <li><a href="#"  type="text/html" onclick="javascript:window.location.href='../src/database_load.php'">{$LANG.menu_databaseshow}</a></li>
               <li><a href="#"  type="text/html" onclick="javascript:window.location.href='../gestao/links_errors_load.php'">{$LANG.menu_links_erros}</a></li>
               <li onclick="javascript:window.location.href='../gestao/sneplog.php'"><a href="#"> {$LANG.tit_logger}</a></li>
             </ul>
        </li>
      </ul>
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
