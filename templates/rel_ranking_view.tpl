{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: rel_ranking_view.tpl - Ranking das Ligacoes
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}  
{include file="cabecalho.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
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
 <table>
    <tr>
       <td class="tb_tit1" rowspan="2">{$LANG.seq}</td>
       <td class="tb_tit1" rowspan="2">{$LANG.destination}</td>
       {if $RANKTYPE == "qtdade"}
          <td class="tb_tit1" colspan="3">{$LANG.rank_qtdade}</td>
          <td class="col_vazia"></td>
          <td class="tb_tit1" colspan="3">{$LANG.rank_time}</td>
       {else}
          <td class="tb_tit1" colspan="3">{$LANG.rank_time}</td>
          <td class="col_vazia"></td>
          <td class="tb_tit1" colspan="3">{$LANG.rank_qtdade}</td>
       {/if}
    </tr>
    <tr>
       <td class="tb_tit2">{$LANG.answered}</td>
       <td class="tb_tit2">{$LANG.notanswered}</td>
       <td class="tb_tit2">{$LANG.total}</td>
       <td class="col_vazia"></td>
       <td class="tb_tit2">{$LANG.answered}</td>
       <td class="tb_tit2">{$LANG.notanswered}</td>
       <td class="tb_tit2">{$LANG.total}</td>
    </tr>
    {foreach name=rank_src from=$DADOS key=key_src item=item_src}
       <tr>
          <td colspan="13">
             <div id="titulo">
                {$smarty.foreach.rank_src.iteration}.&nbsp;
                {$LANG.origin}: {formata->fmt_telefone a=$key_src}
                &nbsp;&nbsp;&nbsp;&nbsp;
                {if $RANKTYPE == "qtdade"}
                   <span class="textocampos">({$TOTAIS.$key_src})</span>
                {else}
                   <span class="textocampos">({formata->fmt_segundos a=$TOTAIS.$key_src b='hms'})</span>
                {/if}
             </div>             
          </td>
       </tr>
       {counter start=0 print=false}
       {foreach name=rank from=$DADOS[$key_src] key=key_rank item=item_rank}         
         {foreach name=rank_data from=$DADOS[$key_src][$key_rank] key=key_dst item=item_dst}
            {if $cor_bg == #COR_GRID_A#}
                {assign var="cor_bg" value=#COR_GRID_B#}
            {else}
                {assign var="cor_bg" value=#COR_GRID_A#}
            {/if}
            <tr  bgcolor="{$cor_bg}">
                <td class="cen">{counter}</td>
                <td class="cen">{formata->fmt_telefone a=$key_dst}</td>
                {if $RANKTYPE == "qtdade"}
                   <td class="cen">{$item_dst.QA|default:0}</td>
                   <td class="cen">{$item_dst.QN|default:0}</td>
                   <td class="cen"><strong>{$item_dst.QT|default:0}</strong></td>
                   <td class="col_vazia"></td>
                   <td class="cen">{formata->fmt_segundos a=$item_dst.TA b='hms'}</td>
                   <td class="cen">{formata->fmt_segundos a=$item_dst.TN b='hms'}</td>
                   <td class="cen">{formata->fmt_segundos a=$item_dst.TT b='hms'}</td>
                {else}
                   <td class="cen">{formata->fmt_segundos a=$item_dst.TA b='hms'}</td>
                   <td class="cen">{formata->fmt_segundos a=$item_dst.TN b='hms'}</td>
                   <td class="cen"><strong>{formata->fmt_segundos a=$item_dst.TT b='hms'}</strong></td>
                   <td class="col_vazia"></td>                   
                   <td class="cen">{$item_dst.QA|default:0}</td>
                   <td class="cen">{$item_dst.QN|default:0}</td>
                   <td class="cen">{$item_dst.QT|default:0}</td>
                {/if}
               
             </tr>
         {/foreach}
         
       {/foreach}       
   {/foreach}
 </table>
 { include file="rodape.tpl }
 {/if}