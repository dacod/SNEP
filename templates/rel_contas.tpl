{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_contas.tpl - Relacao das Contas Cadastradas       
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<table align="center" >   
   <thead>
      <tr>
         <td class="cen" width="15%">{$LANG.id}</td>
         <td class="cen">{$LANG.desc}</td>
         <td class="cen">{$LANG.type}</td>
         <td class="cen" colspan="2" width="20%">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=contas loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[contas].codigo}</td>
         <td>{$DADOS[contas].nome}</td>
         {assign var="tipo_conta" value=$DADOS[contas].tipo}
         <td>{$tipos_contas.$tipo_conta}</td>
         <form name="acao" method="post" action="../src/contas.php">
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <input type="image" src="../imagens/edit.png" border="0" alt="{$LANG.change}" name="acao" value="alterar"  />
            </acronym>
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
               <input type="image" src="../imagens/delete.png" border="0" alt="{$LANG.exclude}"  name="acao" value="excluir" />
            </acronym>          
         </td>       
         <input type="hidden" name="id" value="{$DADOS[contas].codigo}" />
         </form>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }