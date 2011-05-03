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
{include file="cabecalho.tpl"}
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
{config_load file="../includes/setup.conf" section="ambiente"}
{math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
<table cellspacing="0" cellpadding="0" border="0" align="center"  >
   <thead>
      <tr>
         <td rowspan="2" class="esq" width="15%">{$LANG.filename}</td>
         <td rowspan="2" class="esq">{$LANG.desc}</td>
         <td rowspan="2" class="cen">{$LANG.updated}</td>
         <td colspan="2" class="cen">{$LANG.listen}</td>
         <td rowspan="2" class="cen" colspan="3" width="20%">{$LANG.actions}</td>
      </tr>
      <tr>
         <td class="cen">{$LANG.backup}</td>
         <td class="cen">{$LANG.actual}</td>
      </tr>
   </thead>   
   {section name=sounds loop=$DADOS max=#linelimit# start=$INI}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
      <td class="esq">{$DADOS[sounds].arquivo}</td>
      <td class="esq">{$DADOS[sounds].descricao}</td>
      <td class="cen">{$DADOS[sounds].data}</td>
      <td class="cen">
         {if $DADOS[sounds].backup}         
            <a href="{$DADOS[sounds].arq_backup}" type="audio/mpeg" >
              <img src="../imagens/ouvir.png" alt="Ouvir" width="24" height="24" hspace="0" vspace="0" style="border: none" />
            </a>
         {else}
            N.D.
         {/if}
      </td>
      <td class="cen">
         {if $DADOS[sounds].atual}
            <a href="{$DADOS[sounds].arq_atual}" type="audio/mpeg" >
              <img src="../imagens/ouvir.png" alt="Ouvir" width="24" height="24" hspace="0" vspace="0" style="border: none" />
            </a>
         {else}
            N.D.
         {/if}
      </td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="../src/sounds.php?acao=alterar&amp;arquivo={$DADOS[sounds].arquivo}&amp;backup={$DADOS[sounds].arq_backup}&amp;atual={$DADOS[sounds].arq_atual}&amp;secao={$SECAO}&amp;tipo=MOH">
                 <img src="../imagens/edit.png" alt="{$LANG.change}" />
               </a>
            </acronym>
         </td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.backbackup}">
               <a href="../src/sounds.php?acao=voltar&amp;arquivo={$DADOS[sounds].arquivo}&amp;backup={$DADOS[sounds].arq_backup}&amp;atual={$DADOS[sounds].arq_atual}&amp;secao={$SECAO}&amp;tipo=MOH">
                 <img src="../imagens/refresh.png" alt="{$LANG.backbackup}" />
               </a>
            </acronym>
         </td>
         <td valign="middle" align="center">
            <acronym title="{$LANG.exclude}">
               <a href="../src/sounds.php?acao=excluir&amp;arquivo={$DADOS[sounds].arquivo}&amp;backup={$DADOS[sounds].arq_backup}&amp;atual={$DADOS[sounds].arq_atual}&amp;secao={$SECAO}&amp;tipo=MOH">
                 <img src="../imagens/delete.png" alt="{$LANG.exclude}" />
               </a>
            </acronym>          
         </td>
      </tr>
   {/section}
   <tr>
      <td colspan="9" align="center" height="40">
         <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../gestao/rel_musiconhold.php'" />
        <div class="buttonEnding"></div>
      </td>
   </tr>
   <tr class="dir">
      <td colspan="9" class="links" >
         {include file="paginacao.tpl"}
      </td>
   </tr>
   
</table>
{ include file="rodape.tpl }
