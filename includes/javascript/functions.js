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

/*------------------------------------------------------------------------------
 * Funcao janela_otimizada - Identifica tamanho da tela para
 * exibir resolucao correta do Flash no op-panel
 -----------------------------------------------------------------------------*/
function janela_otimizada(URL) {
  width = screen.width-10;
  height = screen.height-100;
  if (width > 0 && height >0) {
     window.location.href = URL+"?L=" + width + "&A=" + height;
  } else
          exit();
}

/*------------------------------------------------------------------------------
 * Funcao janelaSecundaria - Abre uma Janela Pop-up centralizada fullscreen
 -----------------------------------------------------------------------------*/
 function janelaSecundaria (URL,NOME,LARG,ALT){
     if (LARG == 0) {
        LARG = window.screen.width ;
        LEFT = 0 ;
     } else {
        LEFT = ((window.screen.width - LARG ) /2);
     }
     if (ALT == 0 ) {
        ALT  = window.screen.height  ;
        TOP = 0 ;
     } else {
       TOP = ((window.screen.height - ALT ) /2);
     }
     if (URL.indexOf( "?" ) > 0 )  {
        URL  = URL+"&L=" + (LARG-(LARG*0.035)) + "&A=" + (ALT-(ALT*0.26)) ;
     } else {
        URL  = URL+"?L=" + (LARG-(LARG*0.035)) + "&A=" + (ALT-(ALT*0.26)) ;
     }
     /* Ajuste de Altura da Janela */
     ALT=(ALT-(ALT*0.19)) ;
     PARAM="width="+LARG+",height="+ALT+",top="+TOP+",left="+LEFT+",scrollbars=yes,directories=no,status=no,toolbar=no,menubar=no,location=no,resizable=yes";
     window.open(URL,NOME,PARAM)
}

/*------------------------------------------------------------------------------
 * Funcao masc_data - Mascara data para formato europeu
 -----------------------------------------------------------------------------*/
 function masc_data(objeto,data) {
    var mdata = '';
    mdata = mdata + data;
    if (mdata.length == 2) {
        mdata = mdata + '/';
		objeto.value = mdata;
    }
	if (mdata.length == 5) {
        mdata = mdata + '/';
		objeto.value = mdata;
    }
}
/*------------------------------------------------------------------------------
 * Funcao masc_hora - Mascara Hora
 -----------------------------------------------------------------------------*/
function masc_hora(objeto,hora){
    var mhora = '';
    mhora = mhora + hora;
    if (mhora.length == 2) {
        mhora = mhora + ':';
		objeto.value = mhora;
    }
}
/*------------------------------------------------------------------------------
 * Funcao masc_cep - Mascara CEP
 -----------------------------------------------------------------------------*/
function masc_cep(objeto,cep) {
    var mcep = '';
    mcep = mcep + cep; 
    if (mcep.length == 5) { 
        mcep = mcep + '-'; 
	objeto.value = mcep; 
    }
}
/*------------------------------------------------------------------------------
 * Funcao masc_cpf - Mascara CPF
 -----------------------------------------------------------------------------*/
function masc_cpf(objeto,cpf) { 
    var mcpf = ''; 
    mcpf = mcpf + cpf; 
    if (mcpf.length == 3) { 
        mcpf = mcpf + '.'; 
	objeto.value = mcpf; 
    }
    if (mcpf.length == 7) { 
        mcpf = mcpf + '.'; 
	objeto.value = mcpf; 
    }   
    if (mcpf.length == 11) { 
        mcpf = mcpf + '-'; 
	objeto.value = mcpf; 
    }
}

/*------------------------------------------------------------------------------
 * Funcao masc_ccusto - Mascara Codigo do Centro de Custos
 -----------------------------------------------------------------------------*/
function mascara_ccustos(objeto,ccusto){ 
    var mccusto = ''; 
    mccusto = mccusto + ccusto; 
    if (mccusto.length == 1) { 
        mccusto = mccusto + '.'; 
        objeto.value = mccusto; 
    }
    if (mccusto.length == 4) { 
        mccusto = mccusto + '.'; 
	objeto.value = mccusto; 
    }
    if (mccusto.length == 7) { 
        mccusto = mccusto + '.'; 
	objeto.value = mccusto; 
    }
}

/*------------------------------------------------------------------------------
 * Funcao mascara_data - Verifica se digitacao da data � valida
 -----------------------------------------------------------------------------*/
function mascara_data(objeto,data)
{
        var mydata = '';
        mydata = mydata + data;
        if (mydata.length == 2){
           mydata = mydata + '/';
           objeto.value = mydata;
        }
        if (mydata.length == 5){
           mydata = mydata + '/';
           objeto.value = mydata;
        }
        if (mydata.length == 10){
           verifica_data(objeto,data);
        }
}
/*------------------------------------------------------------------------------
 * Funcao verifica_data - Verifica se data � valida
 -----------------------------------------------------------------------------*/
function verifica_data (objeto,data) {
	dia = (objeto.value.substring(0,2));
   mes = (objeto.value.substring(3,5));
   ano = (objeto.value.substring(6,10));
   situacao = "";
   // verifica o dia valido para cada mes
   if ( (dia < 01) || (dia > 31) ) {
      situacao = "falsa" ;
   } else {
      if ((dia > 30) && (  mes == 2 || mes == 4 || mes == 6 || mes == 9 || mes == 11)) {
         situacao = "falsa";
      }
   }
   // verifica se o mes e valido
   if (mes < 01 || mes > 12 ) {
      situacao = "falsa";
   }
   // verifica se e ano bissexto
   if (mes == 2 && ( dia < 01 || dia > 29 || ( dia > 28 && (parseInt(ano / 4) != ano / 4)))) {
      situacao = "falsa";
   }
   if (objeto.value == "") {
      situacao = "falsa";
   }
   if (situacao == "falsa") {
      alert("A Data informada é inválida!"); 
      objeto.focus();
   }
}
/*------------------------------------------------------------------------------
 * Funcao mascara_hora - Verifica se digitacao da hora e valida
 -----------------------------------------------------------------------------*/
function mascara_hora(objeto,hora){
	var myhora = '';
        myhora = myhora + hora;
        if (myhora.length == 2){
           myhora = myhora + ':';
           objeto.value = myhora;
        }
        if (myhora.length == 5){
           verifica_hora(objeto,hora);
        }
}
/*------------------------------------------------------------------------------
 * Funcao verifica_hora - Verifica se hora e valida
 -----------------------------------------------------------------------------*/
function verifica_hora(objeto,hora){
	hrs = (objeto.value.substring(0,2));
   min = (objeto.value.substring(3,5));
   situacao = "";
   // verifica data e hora
   if ((hrs < 00 ) || (hrs > 23) || ( min < 00) ||( min > 59)){
      situacao = "falsa";
   }
   if (objeto.value == "") {
      situacao = "falsa";
   }
   if (situacao == "falsa") {
      alert("Hora inválida!");
      objeto.focus();
   }
}
 /*------------------------------------------------------------------------------
 * Funcao permite dados - Permite manipular campos do form 
 * -----------------------------------------------------------------------------*/
 function permitedados(tipo,vinculos) {
   var rads_a, rads_b;
   if (tipo == 'src') {
      document.relatorio.elements['dst'].className='campos' ;
      document.relatorio.elements['dst'].value="" ;
      document.relatorio.elements['src'].className='campos_disable' ;
      rads_a = document.relatorio.srctype;
      rads_b = document.relatorio.dsttype;
      document.relatorio.elements['dst'].focus() ;
      document.relatorio.elements['src'].value=vinculos;
      document.relatorio.elements['src'].readOnly = true;
      document.relatorio.elements['dst'].readOnly = false;
   }
   if (tipo == 'dst') {
      document.relatorio.elements['src'].className='campos' ;
      document.relatorio.elements['src'].value="" ;
      document.relatorio.elements['dst'].className='campos_disable' ;
      rads_b = document.relatorio.srctype;
      rads_a = document.relatorio.dsttype;
      document.relatorio.elements['src'].focus() ;
      document.relatorio.elements['dst'].value=vinculos;
      document.relatorio.elements['src'].readOnly = false;
      document.relatorio.elements['dst'].readOnly = true;
   }
   rads_a[0].checked=true ;

   for(i=0; i<rads_a.length;i++ ) {
      rads_a[i].disabled = true;
   }
   for(i=0; i<rads_b.length;i++ ) {
      rads_b[i].disabled = false;
   }
 }

 function grupos(tipo, valor) {
     var rads_a, rads_b;

     if (tipo == 'src') {
         if(valor != '') {
            document.relatorio.elements['src'].className='campos_disable';
            document.relatorio.elements['srctype'].className='campos_disable';
            rads_a = document.relatorio.srctype;
            rads_a[0].checked=true ;
                for(i=0; i<rads_a.length;i++ ) {
                  rads_a[i].disabled = true;
                }

         }else{
            document.relatorio.elements['src'].className='campos';
            document.relatorio.elements['srctype'].className='campos';
            rads_a = document.relatorio.srctype;
            rads_a[0].checked=true ;
                for(i=0; i<rads_a.length;i++ ) {
                  rads_a[i].disabled = false;
                }
         }
     }
     if(tipo == 'dst') {
            if(valor != '') {
            document.relatorio.elements['dst'].className='campos_disable';
            document.relatorio.elements['dsttype'].className='campos_disable';
            rads_a = document.relatorio.dsttype;
            rads_a[0].checked=true ;
                for(i=0; i<rads_a.length;i++ ) {
                  rads_a[i].disabled = true;
                }

            }else{
            document.relatorio.elements['dst'].className='campos';
            document.relatorio.elements['dsttype'].className='campos';
            rads_a = document.relatorio.dsttype;
            rads_a[0].checked=true ;
                for(i=0; i<rads_a.length;i++ ) {
                  rads_a[i].disabled = false;
                }
         }

     }
     if(tipo == 'und') {
            if(valor != '') {
                document.relatorio.elements['src'].className='campos_disable';
            }else{
                document.relatorio.elements['src'].className='campos';
            }
     }
     if(tipo == 'txo') {
            if(valor != '') {
                document.relatorio.elements['ramais_d'].className='campos_disable';
            }else{
                document.relatorio.elements['ramais_d'].className='campos';
            }
     }
     if(tipo == 'fls') {
            if(valor != '') {
                document.filas.elements['src'].className='campos_disable';
            }else{
                document.filas.elements['src'].className='campos';
            }
     }
     if(tipo == 'fld') {
            if(valor != '') {
                document.filas.elements['dst'].className='campos_disable';
            }else{
                document.filas.elements['dst'].className='campos';
            }
     }


 }

    function regpaginacao(pagina) {
         window.location.href="rel_chamadas.php?pag="+pagina+"&acao=imp";
    }
