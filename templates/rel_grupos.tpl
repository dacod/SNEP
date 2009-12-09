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
<table cellspacing="0" cellpadding="0" border="0" align="center"  >   
   <thead>
      <tr>
         <td class="cen" width="10px">{$LANG.id}</td>
         <td class="esq">{$LANG.name}</td>
         <td class="cen" colspan="2" width="10px">{$LANG.actions}</td>
      </tr>
   </thead>
   {section name=grupos loop=$DADOS}
   <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
         <td class="cen">{$DADOS[grupos].cod_grupo}</td>
         <td>{$DADOS[grupos].nome}</td>
         <td align="center" valign="middle" width="30px" >
            <acronym title="{$LANG.change}">
               <a href="../src/grupos.php?acao=alterar&amp;cod_grupo={$DADOS[grupos].cod_grupo}"><img src="../imagens/edit.png" alt="{$LANG.change}" /></a>
            </acronym>
         </td>
         <td valign="middle" align="center" width="30px">
            <acronym title="{$LANG.exclude}">
               <img src="../imagens/delete.png" alt="{$LANG.exclude}" onclick="remove_grupo('{$DADOS[grupos].cod_grupo}')"/>
            </acronym>          
         </td>
      </tr>
   {/section}
</table>
{ include file="rodape.tpl }

<script type="text/javascript">
{literal}

    /* Confirmacao e remocao de regras de negocio */
    function remove_grupo(id) {
        var url = '../src/grupos.php';
        var params = 'acao=excluir&cod_grupo='+id;

        if(confirm("{/literal} {$LANG.confirm_remocao_grupo} {literal}")) {
            var retorno = new Ajax.Request (
                url, {
                      method: 'post',
                      parameters: params,
                      onComplete: resposta_grupo
                     }
            );
        }
    }

    function resposta_grupo(resp) {
         window.location.href="../src/rel_grupos.php";
    }

{/literal}
</script>