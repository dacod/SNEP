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
 {config_load file="../includes/setup.conf" section="cores"}
 <table align="center">
    <tr>
       <td class="usuario">{$dt_usuario}</td>
    </tr>
</table>
 <table>
    <thead>
       <tr>
          <td width="10%">{$LANG.id}</td>
          <td width="60%" class="esq">{$LANG.desc}</td>
          <td>{$LANG.autorized}</td>
       </tr>  
    </thead>
    <form name="formulario" action="{$smarty.server.SCRIPT_NAME}"  method="POST" enctype="multipart/form-data" >
    <input type="hidden" name="id" value="{$dt_id}">
    {section name=perms loop=$dt_permissoes}
    <tr bgcolor='{cycle values="`$smarty.config.COR_GRID_A`,`$smarty.config.COR_GRID_B`"}'>
       <td class="cen">{$dt_permissoes[perms].cod_rotina}</td>
       <td class="esq">{$dt_permissoes[perms].desc_rotina}</td>       
       <td class="cen">      
          {html_radios name=$dt_permissoes[perms].cod_rotina checked=$dt_permissoes[perms].permissao options=$TIPOS_PERMS  separator="&nbsp;&nbsp;&nbsp;"}
       </td>      
    </tr>   
    {/section}
    <tr>
       <td colspan="3" class="cen" height="40" >
          <input type="submit" value="{$LANG.save}" name="permissao" class="button">
          <div class="buttonEnding"></div>     
          &nbsp;&nbsp;&nbsp;
          <input class="button" type="button" id="voltar" value="{$LANG.back}" onClick="location.href='../src/rel_ramais.php'" />
         <div class="buttonEnding"></div>     
      </td>
    </tr>
    </form>
 </table>
 { include file="rodape.tpl }
