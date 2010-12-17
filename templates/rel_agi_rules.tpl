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
{include file="filtrar_incluir.tpl"}
{config_load file="../includes/setup.conf" section="cores"}
<script type="text/javascript" src="../includes/javascript/prototype.js"></script>

<table cellspacing="0" cellpadding="0" border="0" align="center" >   
   <thead>
      <tr>
         <td class="cen" width="5%">{$LANG.id}</td>
         <td class="cen" width="5%">{$LANG.onoff}</td>
         <td class="esq" width="15%">{$LANG.origin}</td>
         <td class="esq" width="15%">{$LANG.destination}</td>
         <td class="esq">{$LANG.desc}</td>
         <td class="cen" width="5%">{$LANG.exec_order}</td>
         <td class="cen" colspan="4" width="10%">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=agi_rules loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen"> {$DADOS[agi_rules].codigo}</td>
         <td class="cen"> <span style="cursor:pointer;" alt="{$LANG.onoff}" id="id{$DADOS[agi_rules].codigo}" class="regra{if $DADOS[agi_rules].ativa}1{else}0{/if}" onclick="regras({$DADOS[agi_rules].codigo})"></span></td>
         <td> {$DADOS[agi_rules].src}</td>
         <td> {$DADOS[agi_rules].dst}</td>
         <td> {$DADOS[agi_rules].descricao}</td>
         <td class="cen"> {$DADOS[agi_rules].ordem}</td>
         <td align="center" valign="middle">
            <acronym title="{$LANG.change}">
               <a href="agi_rules.php?acao=alterar&amp;id={$DADOS[agi_rules].codigo}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td align="center" valign="middle">
            <acronym title="Duplicar">
               <a href="agi_rules.php?acao=duplicar&amp;id={$DADOS[agi_rules].codigo}"><img src="../imagens/duplicate.gif" alt="Duplicar" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center">
             <acronym title="{$LANG.remove}">
                <img src="../imagens/delete.png" alt="{$LANG.exclude}" onclick="remove_regra({$DADOS[agi_rules].codigo})"/>
             </acronym>
         </td>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }
<script type="text/javascript" src="../includes/javascript/regras.js"></script>
<script type="text/javascript">
{literal}
    /* Confirmacao e remocao de regras de negocio */
    function remove_regra(id) {
        var url = '../gestao/agi_rules.php';
        var params = 'acao=excluir'+'&id='+id;

        if(confirm("{/literal} {$LANG.confirm_remocao_regras} {literal}")) {
            window.location.href= url + "?" + params;
        }
    }

{/literal}
</script>