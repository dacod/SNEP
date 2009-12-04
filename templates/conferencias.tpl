{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: conferencias.tpl - Lista de Salas de Conferencia
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<table cellspacing="0" cellpadding="0" border="0" align="center"  >   
   <thead>
      <tr>
         <td class="cen">{$LANG.id}</td>
         <td class="cen">{$LANG.useauthenticate}</td>
         <td class="cen">{$LANG.menu_ccustos}</td>
         <td class="cen"  width="10%">{$LANG.actions}</td>
      </tr>
   </thead>
   <form name="formulario" method="post"  action="" enctype="multipart/form-data">
   {foreach from=$DADOS key=key item=item}
      {assign var="status" value=$item.status}
      <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$key}</td>
         <input type="hidden" name="status[{$key}]" value="{$status}"  />
         <td class="cen">
            {assign var="auth_yes" value=""}
            {assign var="auth_no" value=""}
            {if $item.usa_auth}
                {assign var="auth_yes" value="checked"}
            {else}
                {assign var="auth_no" value="checked"}
            {/if}
            <input type="radio" name="usa_auth[{$key}]" value="yes" {$auth_yes} onClick="senha({$key},'yes')" /> {$LANG.yes}
            <input type="radio" name="usa_auth[{$key}]" value="no" {$auth_no} onClick="senha({$key},'no')" /> {$LANG.no}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            {if $item.usa_auth }
               {assign var="auth_class" value="campos"}
               {assign var="auth_disable" value=""}
            {else}
               {assign var="auth_class" value="campos_disable"}
               {assign var="auth_disable" value="disable"}
            {/if}
            &nbsp;&nbsp;&nbsp;
            {$LANG.secret}
            <input size="7" type="password" name="authenticate[{$key}]" value="{$item.authenticate}" class={$auth_class} {$auth_disable} />
            &nbsp;&nbsp;
            {$LANG.onlynumbers}
         </td>
         <td class="cen">
             <SELECT name="ccustos[{$key}]" class="campos">
                  {html_options options=$CCUSTOS selected=$item.ccustos}
             </SELECT>
         </td>
         <td class="cen">
             <input class="campos" id="action" type="checkbox" name="acao[{$key}]" value="{$key}" />
             {$STATUS.$status}
         </td>
         
      </tr>
   {/foreach}
    <tr class="cen">
       <td height="40" colspan="4" valign="middle">
          <input type="submit" class="button" name="conference" value="{$LANG.save}" />
           <div class="buttonEnding"></div>
        </td>
    </tr>
   </form>
</table>
{ include file="rodape.tpl }
{literal}
<script language="javascript" type="text/javascript">
   function senha(sala,auth) {
      var senha = document.forms[0].elements['authenticate['+sala+']'] ;
      var acao  = document.forms[0].elements['acao['+sala+']'] ;
      var stat  = document.forms[0].elements['status['+sala+']'] ;      
      if (auth == 'yes') {
         var classe = 'campos' ;         
         if (!acao.checked && stat.value == "N" )
            acao.checked = true ;
         senha.focus() ;
      } else {
        if (acao.checked)
           acao.checked = false ;
        var classe = 'campos_disable' ;
      }
      senha.className = classe ;      
   }
</script>
{/literal}