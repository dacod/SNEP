{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_grupos.tpl - Relacao dos grupos do sistema      
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<table cellspacing="0" cellpadding="0" border="0" align="center"  >   
   <thead>
      <tr>
         <td class="cen" width="15%">{$LANG.name}</td>
         <td class="esq">{$LANG.type}</td>
         <td class="cen" colspan="3" width="20%">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=grupos loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[grupos].name}</td>
         <td>{$DADOS[grupos].inherit}</td>
         
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/groups.php?acao=alterar&amp;cod_grupo={$DADOS[grupos].name}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>

         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
               <a href="../src/groups.php?acao=excluir&amp;cod_grupo={$DADOS[grupos].name}"><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
            </acronym>
         </td>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }