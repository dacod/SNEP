{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: paginacao.tpl - Monta Links de Paginacao
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
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
