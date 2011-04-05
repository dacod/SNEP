/**
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
 */

/* ----------------------------------------------------------------------------
 * Funcao verifica_peers - cham script que verifica se canal ja nao foi usado
 * Recebe : valor = numero do tronco ou ramal (name)
 *          tipo = Tipo (T=tronco, R=ramal)
 *          strtipo  = String com descricao do tipo (Tronco ou Ramal)
 * Retorna: Verdadeiro ou Falso
 * Exemplos de uso: 
 * <input ... onChange="verifica_peers(this.value, 'T','{$LANG.tronco}')" />
 * ou
 * <input ... onChange="verifica_peers(this.value, 'R','{$LANG.ramal}')" />
 * -----------------------------------------------------------------------------*/
 function verifica_peers(valor,tipo,strtipo) {
      var opt = { method: 'get',
                asynchronous:true, 
                evalScripts:true, 
                onSuccess: function(transport) {
                   var response = transport.responseText ;
                   response = response.replace(/^\s+|\s+$/g,"") ;
                   if (response.length > 0) {
                     alert('{$LANG.msg_channelusedby} '+strtipo+': '+response) ;
                     return false ;
                   } else {
                     return true ;
                   }
               }
             };
      opt.parameters="c="+valor+"&t="+tipo;
      new Ajax.Request('pesq_peers.php', opt);  
 }
/*-----------------------------------------------------------------------------
 * Funcao valida_formulario  - Valida DAdos de um formulario
 * Recebe : dados =  array com dados separados por ";" na seguinte ordem:
 *              - campo = nome/descricao do campo (usado na msg de erro)
 *              - valor = conteudo do campo
 *              - tipo  = Tipo do Dado a ser validado (Ex: NUM=Numerico)
 *              - critica = Critica especifica
 * Retorna: Verdadeiro ou Falso
 * -----------------------------------------------------------------------------*/
 function valida_formulario(dados) {
   var msg = new String("");
   for (i=0;i<=dados.length-1;i++) {
       dtmp = dados[i].split(";") ;      
       campo   = dtmp[0] ;
       valor   = dtmp[1] ;
       tipo    = dtmp[2] ;
       critica = dtmp[3] ;
      // ----- Dados Numerios somente ----- //
      if (tipo == "NUM") {
         if (!valor.match(/^\d+$/) || valor.length == 0) {
            msg += campo+" not numeric.\n";
         }
      }
      // ----- Dados Numerios somente e > que ZERO ----- //
       if (tipo == "NUM_NOZERO") {
         if (!valor.match(/^\d+$/) || valor < 1) {
            msg += campo+" not null.\n";
         }
      }
      // ----- Nao pode estar em Branco ----- //
      if (tipo == "NOT_NULL") {
         if (valor.length == 0 || (valor.replace(/^\s\s*/, '').replace(/\s\s*$/, '')) == "" ) {
            msg += campo+" not blank.\n";
         }
      }
      // ----- Deve ser Ponto Flutuante -- //
      if (tipo == "FLOAT") {
         if (!valor.match(/^((\d+(\.\d*)?)|((\d*\.)?\d+))$/)) {
            msg += campo+" not valid float.\n";
         }
      }
      // ----- Deve iniciar com _ (underline) ----- //
      if (tipo == "UNDER") {
         if (!valor.match(/^[_]/)) {
            msg += campo+" invalid.\n";
         }
      }
      // ----- Alfanuméricos ----- //
      if (tipo == "ALPHANUM") {
         if (valor.match(/[\W]/)) {
            msg += campo+" not alphabet.\n";
         }
      }
      // ----- Campo Nome do Ramal com Numero entre <> ----- //
      if (tipo == "NAME_PEER") {
         if (!valor.match(/<+[0-9]+>+/)) {
            msg += campo+"\n";
         }
      }
   }

   alert(msg);
   if (msg.length > 0) {
      return false ;

   } else
      return true ;
  
 }

