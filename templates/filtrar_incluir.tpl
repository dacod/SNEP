{* Smarty *}
{* ---------------------------------------------------------------------------- 
 * Template: filtrar_incluir.tpl - Exibe  opcoes de Filtro e/ou    
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP            
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt 
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>            
 * ---------------------------------------------------------------------------- *}   
<table align="center" class="bgfiltro">
   <tr>
      {if $view_filter}
         <form name="filtro" method="post" action="{$smarty.server.SCRIPT_NAME}">
         <td class="subtable" width="60%" height="35">
            {$LANG.fieldtofilter}
            <select name="field_filter" class="campos">
               {html_options options=$OPCOES}
            </select>
            &nbsp;&nbsp;&nbsp;
            {$LANG.filter}: <input type="text" name="text_filter" class="campos">
            &nbsp;&nbsp;&nbsp;
            <input type="submit" name="filtrar" value="{$LANG.apply}" class="button"/>
            <div class="buttonEnding"></div>
            &nbsp;&nbsp;&nbsp;
            <input type="submit" name="limpar" value="{$LANG.cancel}"  class="button"/>
            <div class="buttonEnding"></div>
         </td>
         </form>
      {/if}
      {if $debugger_btn}
        <td class="subtable"  height="35">
           <a href="./debugger.php" class="links_debug" >
              {$LANG.debugger}
           </a>
         </td>
      {/if}
      {if $view_include_buttom}
         <td class="subtable"  height="35">
           <a href="#" class="links_include" onclick="location.href='{$array_include_buttom.url}'"  >
              {$array_include_buttom.display}
           </a>
         </td>         
      {/if}
      {if $view_include_buttom2}
         <td class="subtable" width="15%" height="35">
           <a href="#" class="links_include_various" onclick="location.href='{$array_include_buttom2.url}'"  >
              {$array_include_buttom2.display}
           </a>
         </td>
      {/if}
   </tr>
</table>