{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_tarifas.tpl - Relacao das Tarifas Cadastradas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="ambiente"}
{config_load file="../includes/setup.conf" section="cores"}
{math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
<table cellspacing="0" cellpadding="0" border="0" align="center"  >
   <thead>
      <tr>
         <td class="cen">{$LANG.operadora}</td>
         <td class="cen" width="10%">{$LANG.country}</td>
         <td class="cen" width="20%">{$LANG.city}</td>
         <td class="cen" width="5%">{$LANG.state}</td>
         <td class="cen">{$LANG.prefix}</td>
         <td class="cen" width="5%">{$LANG.ddd}</td>
         <td class="cen" width="10%">{$LANG.vlrbase_fix}</td>
         <td class="cen" width="10%">{$LANG.vlrpartida_fix}</td>
         <td class="cen" width="10%">{$LANG.vlrbase_cel}</td>
         <td class="cen" width="10%">{$LANG.vlrpartida_cel}</td>
         <td class="cen" colspan="2" width="100px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=tarifas loop=$DADOS max=#linelimit# start=$INI}
      <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         {assign var="operadora" value=$DADOS[tarifas].operadora}
         <td class="cen">{$OPERADORAS.$operadora}</td>
         <td class="esq">{$DADOS[tarifas].pais}</td>
         <td class="esq">{$DADOS[tarifas].cidade}</td>
         <td class="cen">{$DADOS[tarifas].estado}</td>
         <td class="cen">{$DADOS[tarifas].prefixo}</td>
         <td class="cen">{$DADOS[tarifas].ddd}</td>
         <td class="dir">{$DADOS[tarifas].vfix|string_format:"%.2f"}</td>
         <td class="cen" width="10%">{$DADOS[tarifas].vpf|string_format:"%.2f"}</td>
         <td class="dir">{$DADOS[tarifas].vcel|string_format:"%.2f"}</td>       
         <td class="cen" width="10%">{$DADOS[tarifas].vpc|string_format:"%.2f"}</td>
         <form name="acao" method="post" action="../tarifas/tarifas.php">
            <td align="center" valign="middle" width="30px;">
               <acronym title="{$LANG.change}">
                    <a href="../tarifas/tarifas.php?acao=alterar&amp;codigo={$DADOS[tarifas].codigo}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
               </acronym>
            </td>
            <td valign="middle" align="center" width="30px;">
               <acronym title="{$LANG.exclude}">
                  <a href="../tarifas/tarifas.php?acao=excluir&amp;codigo={$DADOS[tarifas].codigo}"><img src="../imagens/delete.png" alt="{$LANG.exclude}" /></a>
               </acronym>
            </td>
            <input type="hidden" name="codigo" value="{$DADOS[tarifas].codigo}" />
         </form>
      </tr>
   {/section}
   <tr class="dir">
      <td colspan="12" class="links" >
         {include file="paginacao.tpl"}
      </td>
   </tr>
</table>
{ include file="rodape.tpl }
