/*-----------------------------------------------------------------------------
 * Programa: functions_smarty.js - Funcoes em JS com sintaxe Smarty
 * Copyright (c) 2008 - Opens Tecnologia - Projeto SNEP
 * Licenciado sob Creative Commons. Veja arquivo ./doc/licenca.txt
 * Autor: Flavio Henrique Somensi <flavio@opens.com.br>
 * ----------------------------------------------------------------------------
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
 function verifica_peers(valor,tipo,strtipo) {ldelim}
      var opt = {ldelim} method: 'get',       
                asynchronous:true, 
                evalScripts:true, 
                onSuccess: function(transport) {ldelim}
                   var response = transport.responseText ;
                   response = response.replace(/^\s+|\s+$/g,"") ;
                   if (response.length > 0) {ldelim}
                     alert('{$LANG.msg_channelusedby} '+strtipo+': '+response) ;
                     return false ;
                   {rdelim} else {ldelim}
                     return true ;
                   {rdelim}
               {rdelim}
             {rdelim};
      opt.parameters="c="+valor+"&t="+tipo;
      new Ajax.Request('pesq_peers.php', opt);  
 {rdelim} 
/*-----------------------------------------------------------------------------
 * Funcao valida_formulario  - Valida DAdos de um formulario
 * Recebe : dados =  array com dados separados por ";" na seguinte ordem:
 *              - campo = nome/descricao do campo (usado na msg de erro)
 *              - valor = conteudo do campo
 *              - tipo  = Tipo do Dado a ser validado (Ex: NUM=Numerico)
 *              - critica = Critica especifica
 * Retorna: Verdadeiro ou Falso
 * -----------------------------------------------------------------------------*/
 function valida_formulario(dados) {ldelim}
   var msg = new String("");
   for (i=0;i<=dados.length-1;i++) {ldelim}
       dtmp = dados[i].split(";") ;      
       campo   = dtmp[0] ;
       valor   = dtmp[1] ;
       tipo    = dtmp[2] ;
       critica = dtmp[3] ;
      // ----- Dados Numerios somente ----- //
      if (tipo == "NUM") {ldelim}
         if (!valor.match(/^\d+$/) || valor.length == 0) {ldelim}
            msg += "{$LANG.thefield} "+campo+" {$LANG.msg_onlynumeric}\n";
         {rdelim}
      {rdelim}
      // ----- Dados Numerios somente e > que ZERO ----- //
       if (tipo == "NUM_NOZERO") {ldelim}
         if (!valor.match(/^\d+$/) || valor < 1) {ldelim}
            msg += "{$LANG.thefield} "+campo+" {$LANG.msg_notblank}\n";
         {rdelim}
      {rdelim}
      // ----- Nao pode estar em Branco ----- //
      if (tipo == "NOT_NULL") {ldelim}
         if (valor.length == 0 || (valor.replace(/^\s\s*/, '').replace(/\s\s*$/, '')) == "" ) {ldelim}
            msg += "{$LANG.thefield} "+campo+" {$LANG.msg_notblank}\n";
         {rdelim}
      {rdelim}
      // ----- Deve ser Ponto Flutuante -- //
      if (tipo == "FLOAT") {ldelim}
         if (!valor.match(/^((\d+(\.\d*)?)|((\d*\.)?\d+))$/)) {ldelim}
            msg += "{$LANG.thefield} "+campo+" {$LANG.msg_floatvalid}\n";
         {rdelim}
      {rdelim}
      // ----- Deve iniciar com _ (underline) ----- //
      if (tipo == "UNDER") {ldelim}
         if (!valor.match(/^[_]/)) {ldelim}
            msg += "{$LANG.thefield} "+campo+" {$LANG.msg_under_requir}\n";
         {rdelim}
      {rdelim}
      // ----- Alfanuméricos ----- //
      if (tipo == "ALPHANUM") {ldelim}
         if (valor.match(/[\W]/)) {ldelim}
            msg += "{$LANG.thefield} "+campo+" {$LANG.msg_inv_aphanum}\n";
         {rdelim}
      {rdelim}
      // ----- Campo Nome do Ramal com Numero entre <> ----- //
      if (tipo == "NAME_PEER") {ldelim}
         if (!valor.match(/<+[0-9]+>+/)) {ldelim}
            msg += "{$LANG.thefield} "+campo+" {$LANG.msg_name_peer}\n";
         {rdelim}
      {rdelim}
   {rdelim}
   if (msg.length > 0) {ldelim}
      alert("{$LANG.msg_errors}\n"+msg) ;
      return false ;
   {rdelim} else
      return true ;     
 {rdelim}

