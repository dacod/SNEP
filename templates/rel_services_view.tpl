{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_filas.tpl - Relacao das filas Efetuadas
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Rafael Bozzetti <rafael@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
 {include file="cabecalho.tpl"}
     {if $TPREL == "csv"}
             
            <table align="center" class="mensagem" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <td class="mensagem">{$LANG.msg_down_csv}</td>
                </tr>
                </thead>
    
                <tr>
                   <td height="50" class="mensagem">
                    <a href="{$ARQCVS}"><img src="../imagens/csv.png">{$LANG.msg_download}</a>
                      
                   </td>
                </tr>
                <tr>

                   <td class="subtable" align="right" style="padding: 5px;">
                      <form name="ok" id="ok">
                                   <input type="button" class="button" value="Voltar" onClick="history.go(-1);"  />
                                <div class="buttonEnding"></div>
                      </form>
                   </td>
                </tr>
            </table>
            <br /><br /><br /><br />
    
<br />
{elseif $TPREL != "csv"}

    {config_load file="../includes/setup.conf" section="ambiente"}
    {config_load file="../includes/setup.conf" section="cores"}
    {math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
    <table align="center">
       <thead>
         <tr>
            <td class="cen">{$LANG.ramal}</td>
            <td class="cen">{$LANG.data}</td>
            <td class="esq">{$LANG.service_enable}</td>
            <td class="esq">{$LANG.service_status}</td>
         </tr>
       </thead>
       {assign var="tree" value=""}
       {section name=filas loop=$DADOS max=#linelimit# start=$INI}
         
         <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         {math equation="x+1" x=$smarty.section.filas.index assign="prox"}
            <td class="{$classe} cen"> {$DADOS[filas].peer} </td>
            <td class="{$classe} cen"> {$DADOS[filas].date} </td>            
            <td class="{$classe}"> {$DADOS[filas].service} - {if $DADOS[filas].state == "1"}{$LANG.enabled} {else if} {$LANG.notenabled} {/if} </td>
            <td class="{$classe}"> {$DADOS[filas].status}</td>
         </tr>

       {/section}

{if $smarty.session.nome != ''}
{$smarty.session.nome}
{else}

{/if}

       <tr class="dir">
          <td colspan="11" class="links" >
             {include file="paginacao.tpl"} 
          </td>
       </tr>
       <!--
       <tr class="item_rel_impar">
          <td colspan="11">
          <b>{$LANG.subtotals}:</b>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          {$LANG.answered}:&nbsp;<b>{$TOTAIS.answered}</b>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          {$LANG.notanswereds}:&nbsp;<b>{$TOTAIS.notanswer}</b>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          {$LANG.abandon}:&nbsp;<b>{$TOTAIS.abandon}</b>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  
          {$LANG.endbyagent}:&nbsp;<b>{$TOTAIS.endbyagent}</b>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
          {$LANG.endbycaller}:&nbsp;<b>{$TOTAIS.endbycaller}</b>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

       </tr>
       -->
   </table>

{ include file="rodape.tpl }
{/if}