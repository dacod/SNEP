{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_musiconhold.tpl - Template da rotina rel_musionhold.php
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<table cellspacing="0" cellpadding="0" border="0" align="center" >   
   <thead>
      <tr>
         <td class="cen" width="10%">{$LANG.section}</td>
         <td class="cen">{$LANG.desc}</td>
         <td class="cen">{$LANG.mode}</td>
         <td class="cen">{$LANG.directory}</td>
         <td class="cen" colspan="3" width="15%">{$LANG.actions}</td>
      </tr>
   </thead>
   {foreach name=outer from=$DADOS item=item}
      <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="esq">{$item.name}</td>
         <td class="esq">{$item.desc}</td>
         {assign var="modo" value=$item.mode}
         <td class="esq">{$MUSIC_MODES.$modo}</td>
         <td class="esq">{$item.directory}</td>
         <form name="acao" method="post" action="../gestao/musiconhold.php">
         <input type="hidden" name="name" value="{$item.name}" />
         <input type="hidden" name="directory" value="{$item.directory}" />
         <input type="hidden" name="mode" value="{$MUSIC_MODES.$modo}" />
         <input type="hidden" name="application" value="{$item.application}" />
         <input type="hidden" name="acao" value="listar" />
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../gestao/musiconhold.php?acao=alterar&amp;name={$item.name}&amp;directory={$item.directory}&amp;mode={$MUSIC_MODES.$modo}&amp;application={$item.application}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center">
            {if $item.name != "default"} 
               <acronym title="{$LANG.exclude}">
                  <a href="../gestao/musiconhold.php?acao=excluir&amp;name={$item.name}&amp;directory={$item.directory}&amp;mode={$MUSIC_MODES.$modo}&amp;application={$item.application}"><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
               </acronym>
            {/if}
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.listmusic}">
               <input type="image" src="../imagens/listsounds.png" border="0" alt="{$LANG.listmusic}" />
            </acronym>
         </td>         
         </form>
      </tr>
   {/foreach}
</table>
{ include file="rodape.tpl }