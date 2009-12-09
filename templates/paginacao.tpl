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
{* ---------------------------------------------------------------------------- 
 * Monta Links de Paginacao
 * Uso: 
 * a) Defina no arquivo .php as seguintes variaveis:
 *  $smarty->assign ('PAGINAS',$paginas); -> array de todas as paginas
 *  $smarty->assign ('INI',1);            -> Primeiro Registro a ser exibido
 *  $smarty->assign ('TOT',$tot_pages);   -> Total de Paginas
 *  $smarty->assign ('TPCLICK', X);       -> X : A=Array, T=Tabela (default)
 *
 * b) Inclua no incio do template a linha:
 *  {math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=$MAX assign="INI"}
 * ---------------------------------------------------------------------------- *}
{if $TPCLICK == "A"}
   {assign var="s_pag" value=$smarty.post.pag|default:1}
{else}
   {assign var="s_pag" value=$smarty.get.pag|default:1}
{/if}
{assign var="menos" value=$s_pag-1}
{assign var="mais" value=$s_pag+1}
{assign var="ate" value=$TOT}

{if $TOT > 1}
    {if $JUMP != 0}
        {$LANG.gotopage}:
           <select class="campos mr10" name="jump" id="jump" onchange="regpaginacao(this.value);">
           {html_options options=$JUMP values=$JUMP}
           </select>
    {/if}
{/if}

{if $pag < $TOT-5}
   {assign var="ate" value=$s_pag+5}
{/if}
{assign var="desde" value=$s_pag}
{if $pag > 1 }
   {assign var="desde" value=$s_pag-1}
{/if}
{if $desde <= 5 }
    {assign var="desde" value=1}
{else}
    {assign var="desde" value=$desde-4}
{/if}           
{if $TOT > 1}
   {if $menos > 0}
      {if $TPCLICK == "A"}
         <a href="#"  onClick="save_array(1)">
      {else}
         <a href="?pag=1&acao=imp">
      {/if}
      {$LANG.startpage}</a>
   {/if}
   {foreach from=$PAGINAS item=item}
      {if ($item >= $desde) and ($item <= $ate) }
         {if $item == $s_pag}
            |<span class="links_selected">&nbsp;{$item}</span>
         {else}
            {if $TPCLICK == "A"}
               |<a href="#" onClick="save_array({$item})">
            {else}
               |<a href="?pag={$item}&acao=imp" >
            {/if}
            &nbsp;&nbsp;{$item}&nbsp;</a>
         {/if}
      {/if}
   {/foreach}      
   {if $mais <= $TOT}
       <span class="links">
       {if $TPCLICK == "A"}
          |<a href="#" onClick="save_array({$TOT})">
       {else}
          |<a href="?pag={$TOT}&acao=imp">          
       {/if}
       &nbsp;{$LANG.endpage}</a>
       </span>
   {/if}
{/if}
