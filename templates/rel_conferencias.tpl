{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_conferencias.tpl - Lista de Salas de Conferencia
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<table align="center" >   
   <thead>
      <tr>
         <td class="cen" width="10px">{$LANG.id}</td>
         <td class="cen">{$LANG.secretroom}</td>
         <td class="cen">{$LANG.secretadmin}</td>
         <td class="cen" colspan="1" width="100px">{$LANG.actions}</td>
      </tr>
   </thead>
   <form name="formulario" method="post"  action="" enctype="multipart/form-data">
   {foreach from=$DADOS key=key item=item}
      {assign var="status" value=$item.ativa}
      <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$key}</td>
         <input type="hidden" name="status[{$key}]" value="{$status}"  />
         <td class="cen">
             <input type="text" name="secret_room[{$key}]"  value="{$item.sr}" {if $status == "N"} disabled class="campos_disable" {else} class="campos" {/if} />
         </td>
         <td class="cen">
            <input type="text" name="secret_admin[{$key}]"  value="{$item.sa}" {if $status == "N"} disabled class="campos_disable" {else} class="campos" {/if}  />
         </td>         
         <td class="cen">
             <input class="campos" type="checkbox" name="acao[{$key}]" value="{$key}" onClick="altera_status({$key},'{$status}')" />
             {$STATUS.$status}
         </td>
         
      </tr>
   {/foreach}
    <tr class="cen">
       <td height="40" colspan="4" valign="middle">
          <input type="submit" class="button" name="meetme" value="{$LANG.save}" />
           <div class="buttonEnding"></div>
        </td>
    </tr>
   </form>
</table>
{ include file="rodape.tpl }
{literal}
<script language="javascript" type="text/javascript">
   function altera_status(sala,stat) {
     var senha_sala = document.forms[0].elements['secret_room['+sala+']'] ;
     var senha_adm = document.forms[0].elements['secret_admin['+sala+']'] ;
     var acao = document.forms[0].elements['acao['+sala+']'] ;
     if ( stat == "N") {
        if (acao.checked ) {
           var classe = 'campos' ;
           var flag = false ;
        } else {
           var classe = 'campos_disable' ;
           var flag = true ;
        }     
     } else if (stat ==  "S") {
        if (acao.checked ) {
           var classe = 'campos_disable' ;
           var flag = true ;
        } else {
           var classe = 'campos' ;
           var flag = false ;
        }
     }
     senha_sala.className =  classe ;
     senha_sala.disabled =  flag;
     senha_adm.className = classe ;
     senha_adm.disabled =  flag;
     senha_sala.focus();
   }
</script>
{/literal}