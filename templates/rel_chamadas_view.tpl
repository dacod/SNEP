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
{include file="cabecalho.tpl"}
{if $TPREL == "grafico"}
   <div>
      <img src="../gestao/graf_chamadas.php" />
   </div>
   { include file="rodape.tpl }
{elseif $TPREL == "csv"}
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
   <br /><br /><br /><br /><br />
{else}
   {config_load file="../includes/setup.conf" section="ambiente"}
   {config_load file="../includes/setup.conf" section="cores"}
   {math equation="(x-y)*z" x=$smarty.get.pag|default:1 y=1 z=#linelimit#  assign="INI"}
   {if $rel_type == "sintetico"}
      <table>
         <tr style="background-color: #f1f1f1;">
             <td colspan="2">
                <b>{$LANG.filters}:</b>
             </td>
          </tr>
         <tr>
            <td class="esq" width="15%">
            <strong>{$LANG.origin}</strong>
            </td>
            <td class="esq">
                {$sinteticsrc}
            </td>
         </tr>
         <tr>
            <td class="esq" width="15%">
            <strong> {$LANG.destination} </strong>
            </td>
            <td class="esq">
                {$sinteticdst}
            </td>
         </tr>
         <tr>
            <td class="esq" width="15%"><strong>  {$LANG.menu_ccustos}</strong> </td>
            <td class="esq">
            {foreach from=$sintetic_cc key=k item=v}
              {$v}
            {/foreach}
            </td>
         </tr>
         <tr>
            <td class="esq" width="15%">
            <strong>{$LANG.callstatus}</strong>
            </td>
            <td class="esq">
                {$sintetic_status}
            </td>
          </tr>
          <tr style="background-color: #f1f1f1;">
             <td colspan="2">
                <b>{$LANG.totais}:
             </td>
          </tr>
          <tr>
             <td>
                <b>{$LANG.calls}:</b>
             </td>
             <td>
                {$LANG.answered}:&nbsp;<b>
                {$TOTAIS.answered}</b>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {$LANG.notanswereds}:&nbsp;
                <b>{$TOTAIS.notanswer}</b>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {$LANG.busys}:&nbsp;
                <b>{$TOTAIS.busy}</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {$LANG.duration}:&nbsp;
                <b>{$TOTAIS.fail}</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {$LANG.others}:&nbsp;<b>{$TOTAIS.oth}</b>

             </td>
          </tr>
          <tr>
             <td>
                <strong>{$LANG.times}: (H:m:s)</strong>
             </td>
             <td>
                {$LANG.calls}:&nbsp;
                {if #typetime# == "S"}
                    <b>{$TOTAIS.duration}</b>
                {else}
                    <b>{formata->fmt_segundos a=$TOTAIS.duration b='hms'}</b>
                {/if}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {$LANG.tarifation}:&nbsp;
                {if #typetime# == "S"}
                    <b>{$TOTAIS.billsec}</b>
                {else}
                    <b>{formata->fmt_segundos a=$TOTAIS.billsec b='hms'}</b>
                {/if}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                {$LANG.wait}:&nbsp;
                {if #typetime# == "S"}
                    <b>{$TOTAIS.espera}</b>
                {else}
                    <b>{formata->fmt_segundos a=$TOTAIS.espera b='hms'}</b>
                {/if}
             </td>
          </tr>
          {if $VIEW_TARIF == "yes"}
             <tr>
                <td>
                   <strong>{$LANG.tottariff}:</strong>
                </td>
                <td>
                   {$TOTAIS.tot_tarifado}
                </td>
             </tr>
          {/if}
          <tr class="cen">
              <td colspan="12" class="links">
                 <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_chamadas.php'" />
                 <div class="buttonEnding"></div></td>
          </tr>
      </table>
   {else}
      <form name="tabela" id="tabela">
          <input type="hidden" value="" id="selected" />
         <table align="center">
            <thead>
               <tr>

                  <td class="cen">{$LANG.seq}</td>
                  <td class="cen">{$LANG.calldate}</td>
                  <td class="esq">{$LANG.origin}</td>
                  <td class="esq">{$LANG.destination}</td>
                  <td class="esq">{$LANG.callstatus}</td>
                  <td class="esq">{$LANG.duration}</td>
                  <td class="esq">Conversação</td>
                  <td class="esq">{$LANG.menu_ccustos}</td>
                  <!--<td class="esq">{$LANG.context}</td>-->
                  <td class="esq">{$LANG.city} - {$LANG.state}</td>
                  {if $VIEW_TARIF == "yes"}
                  <td class="dir">{$LANG.value}</td>
                  {/if}
                  {if $VIEW_FILES == "yes"}
                  <td class="cen" colspan="3">{$LANG.record}</td>
                  {/if}
               </tr>
            </thead>
            {section name=chamadas loop=$DADOS max=#linelimit# start=$INI}
               {if $cor_bg == #COR_GRID_A#}
                  {assign var="cor_bg" value=#COR_GRID_B#}
               {else}
                  {assign var="cor_bg" value=#COR_GRID_A#}
               {/if}
               <tr bgcolor="{$cor_bg}">
                  {math equation="x+1" x=$smarty.section.chamadas.index assign="prox"}
                  <td class="cen">{$smarty.section.chamadas.index+1}</td>
                  <td class="{$classe}">{$DADOS[chamadas].dia}</td>
                  <td class="{$classe}">{formata->fmt_telefone a=$DADOS[chamadas].src}</td>
                  <td class="{$classe}">{formata->fmt_telefone a=$DADOS[chamadas].dst}</td>
                  {assign var="disposition" value=$DADOS[chamadas].disposition}
                  {if $DADOS[chamadas].qtdade > 1 && $DADOS[chamadas].userfield != ""}
                       <td class="{$classe}" colspan="4">
                          {if $DADOS[chamadas].qtdade > 1}
                             {if $DADOS[chamadas].userfield != ""}
                                 <a href="#" class="mais"  id="more{$smarty.section.chamadas.index+1}"  onclick="moreinfo('{$DADOS[chamadas].userfield}', '{$smarty.section.chamadas.index+1}','{$VIEW_TARIF}');">
                                   <span  >{$LANG.detail}</span>
                                    </a>
                             {/if}
                          {else}
                             N.A.
                          {/if}
                       </td>
                  {else}
                     <td class="{$classe}">{$TIPOS_DISP.$disposition}</td>
                     <td class="{$classe}" align="center">
                        {if #typetime# == "S"}
                           $DADOS[chamadas].duration
                        {else}
                           {formata->fmt_segundos a=$DADOS[chamadas].duration b='hms'}
                        {/if}
                     </td>
                     <td class="{$classe}" align="center">
                         {formata->fmt_segundos a=$DADOS[chamadas].billsec b='hms'}
                     </td>
                     <td class="{$classe}">
                         {assign var="cc" value=$DADOS[chamadas].accountcode}
                         {$CCUSTOS.$cc}

                     </td>
<!--                   <td class="{$classe}">{$DADOS[chamadas].dcontext}</td> -->
                     <td class="{$classe}">
                         {if strlen($DADOS[chamadas].src) > 7 && strlen($DADOS[chamadas].dst) < 5 }
                            {formata->fmt_cidade a=$DADOS[chamadas].src}
                         {else}
                            {formata->fmt_cidade a=$DADOS[chamadas].dst}
                         {/if}
                     </td>
                     {if $VIEW_TARIF == "yes"}
                     <td class="{$classe}" style="text-align:right">
                        {if $DADOS[chamadas].disposition == "ANSWERED"}
                           {formata->fmt_tarifa a=$DADOS[chamadas].dst b=$DADOS[chamadas].billsec c=$DADOS[chamadas].accountcode d=$DADOS[chamadas].calldate e=$DADOS[chamadas].tipo}
                        {else}
                           0,0
                        {/if}
                     </td>
                  {/if}
                  {/if}

                  {if $VIEW_FILES == "yes"}
                  <td class="{$classe}" style="text-align:center">
                     {if $DADOS[chamadas].userfield != "" && $classe == ""}
                        {formata->fmt_gravacao a=$DADOS[chamadas].calldate b=$DADOS[chamadas].userfield}
                        {if $voz != "N.D."}
                           {if $VIEW_FILES == "yes"}
                              <input type="checkbox"  value="{$voz}">
                              {if $quebra == "True"}                                 
                                 {assign var="classe" value=""}
                              {/if}
                           {/if}
                           <a href="{$voz}" class="link_esp_1"><img src="../imagens/ouvir.png" title="{$LANG.ouvirarquivo}" ></a>
                        {else}
                           <img src="../imagens/semaudio.png" title="{$LANG.nodeletedisable}">
                        {/if}
                     {else}
                        ---
                     {/if}
                  </td>

                  {if $EXCLUIR_ICON != ""}
                     <td>
                     {if $DADOS[chamadas].userfield != "" && $classe == ""}
                        {if $voz != "N.D."}
                           <a onclick="return rem_arq('{$voz}','{$LANG.msg_removefile}');" ><img style="cursor:pointer;" src="../imagens/delete.png" title="{$LANG.nodelete}"></a>
                        {else}
                           <img src="../imagens/nodelete.png" title="{$LANG.nodeletedisable}" >
                        {/if}
                     {else}
                        ---
                     {/if}
                     </td>
                  {/if}
                  {/if}
               </tr>
               <tr id="eg{$smarty.section.chamadas.index+1}" style="display:none;">
                   <td id="reg{$smarty.section.chamadas.index+1}" colspan="12" style="background-color: #d2d2d2;">
                      <img src="../imagens/cdr-loader.gif" width="16" height="16" />
                      <span style="font-weight: bold; text-align: center; font-size:1.2em; color: #000; ">{$LANG.processing}</span>
                   </td>
               </tr>
            {/section}
            <tr class="item_rel_impar">
               <td colspan="12">
                  <b>{$LANG.subtotals}:</b>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  {$LANG.answered}:&nbsp;<b>{$TOTAIS.answered}</b>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  {$LANG.notanswereds}:&nbsp;<b>{$TOTAIS.notanswer}</b>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  {$LANG.busys}:&nbsp;<b>{$TOTAIS.busy}</b>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  {$LANG.fail}:&nbsp;<b>{$TOTAIS.fail}</b>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  {$LANG.others}:&nbsp;<b>{$TOTAIS.oth}</b>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  {if $VIEW_TARIF == "yes"}
                     {$LANG.tottariff}:&nbsp;<b>{$TOTAIS.tot_tarifado|string_format:"%.2f"}</b>
                  {/if}
                  {if $VIEW_FILES == "yes"}
                     <span class="botaospan">
                        <span  class="button"  style="float:left" OnClick="compactCheckeds();">{$LANG.compress} .</span>
                           <div class="buttonEnding"></div>
                     </span>
                  {/if}
               </td>
            </tr>
            <tr class="dir">
               <td colspan="12" class="links" >
                  {include file="paginacao.tpl"}
               </td>
            </tr>
         </table>
      </form>
   {/if}
   { include file="rodape.tpl }
{/if}
