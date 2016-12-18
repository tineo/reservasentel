//Fernando Torres.
// 20-06-2010
 
var RowColor='';
var ArrIdsDelete = [];
var params;
var PaginaActual=0;
var Dialogo;

Dialogo = function(obj,options) {
	this.init(obj,options);
}

$.extend(Dialogo.prototype, {  
	init: function(obj,options) {
		var defaults = {  
			autoOpen: false,  
			width: 300,  
			title: 'Dialogo Para Formularios',
			modal: true,
			resizable: false
		};
		var optionsa = $.extend(defaults, options);
		this.form = $('#'+obj);		
		$(this.form).dialog(optionsa)
		.bind( "dialogclose", function(event) {          
			if (/MSIE (5\.5|6).*Windows/.test(navigator.userAgent)){
				//habilitar los select en IE
				$("SELECT").each(function (i,select) {
					$(select).show();
				});
			}
		});
	},
	open: function() {
		// deshabilitar los select en IE
		if(/MSIE (5\.5|6).*Windows/.test(navigator.userAgent)) {
			$("SELECT").each(function (i,select) {
				$(select).hide();
			});
		}		
		// habilitar los select que estan dentro del Dialogo
		if(/MSIE (5\.5|6).*Windows/.test(navigator.userAgent)) {
			$("#" + this.form[0].id + " SELECT").each(function (i,select) {
				$(select).show();
			});
		}		
		$(this.form).dialog('open');	
	},
	close: function() {				
		$(this.form).dialog('close');	
	},
	title: function(title) {				
		$(this.form).dialog("option", "title", title);	
	}
});

function selectGetText(select) {
    select = $("#"+select)[0];
    return select.options[select.selectedIndex].text;
}

function callServer(params, onCallBack, MostrarLoading){
var defaults = {  
		type: 'POST',
		dataType: 'html',
		success: function(d){
			if (typeof onCallBack == "function") onCallBack(d);
		},
		error: function(msg,r){
			alert('Error en la pagina');	
		}
	};
	var option = $.extend(defaults, params);	
	if(typeof MostrarLoading=='undefined')	MostrarLoading=true;
	if(MostrarLoading) Loading();
	$.ajax(option);
}


function toEval(Cadena){
    return eval('(' + Cadena + ')');
}



function Loading(){        
  $.blockUI({ message:"<img src='images/loading3.gif'/>",overlayCSS: { backgroundColor: '#FFFFFF' } });   
}

function CheckGrid(contenedor,clas,elemento,btn){
 var count = false;
 ArrIdsDelete = [];
 $('#'+contenedor +' .'+clas).each(function(i,e){
	if(!e.disabled){
	  e.checked = elemento.checked;
	  if(e.checked){
		  count=true;
		  ArrIdsDelete.push(e.id);
	  }
	}
 });
 $("#"+btn).attr("disabled",!count);
}

function DisabledBotton(contenedor,clas,boton){
  var cont=false;
  ArrIdsDelete = [];
 $('#'+contenedor +' .'+clas).each(function(i,e){
	if(e.checked){
	  cont=true;
	  ArrIdsDelete.push(e.id);
	}
 });
 $("#"+boton).attr("disabled",!cont); 
}

/*function EditorGenerico(clase,Ancho,Alto){
$("."+clase).each(function(x,e){
       var nom_control = e.id;
        var sBasePath = 'fckeditor/';
        var oFCKeditor = new FCKeditor( nom_control );
        oFCKeditor.BasePath	= sBasePath;
        oFCKeditor.Width = Ancho;
        oFCKeditor.Height = Alto;
        oFCKeditor.ReplaceTextarea();
});
}*/


function ValidarEditor(cadena) {
    var Texto = cadena.replace(/<[^>]+>/g, "");
    var TextoFinal = Texto.replace(/&nbsp;/g,"")
    if($.trim(TextoFinal) === "") return false;
    else return true;  
}

function PaginarValidar(evento,text,funcion){
  var accion;
  if(evento.keyCode)
    accion = evento.keyCode;
  else if(evento.which)
    accion = evento.which; 

  var MaxValue = parseInt($(text).attr("MaxValue"),10);
  var Value = parseInt($.trim(text.value),10);
  var OldValue = parseInt($(text).attr("OldValue"),10);
  if(accion === 13){	
	if(OldValue == Value) return;	
	if(Value>MaxValue || Value<1 || isNaN(Value)){
	  //var msg = isNaN(Value)?"Es requerido ingresar el n&uacute;mero de p&aacute;gina":"La p&aacute;gina n&uacute;mero "+ Value +" no esta permitido.";
	  //alert(msg);
	  text.value = OldValue;
	  return;
	}	
	if (typeof funcion == "function") funcion(text.value);	
  }else if(isNaN(Value) && Value==''){
	text.value = OldValue;  
  }
}

function ValidFono(_Elemento,adicional){
 if(typeof adicional == "undefined") adicional = "";
 var _completo = "1234567890 -()." + adicional;
 var contador = 0;
 var _retorno = "";
   for (var i=0; i < _Elemento.value.length; i++) {
     ubicacion = _Elemento.value.substring(i, i + 1);      
     if ((_completo.indexOf(ubicacion) != -1)) {  
       contador++;
       _retorno += ubicacion;
     } else {
       _Elemento.value = _retorno; 
       return false;  
     }  
   }
   return true;
}

function ValidTexto(_Elemento, adicional){
 if(typeof adicional == "undefined") adicional = "";
  var _completo = "abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ" + adicional;
 var contador = 0;
 var _retorno = "";
   for (var i=0; i < _Elemento.value.length; i++) {
     ubicacion = _Elemento.value.substring(i, i + 1);      
     if ((_completo.indexOf(ubicacion) != -1)) {  
       contador++;
       _retorno += ubicacion;
     } else {
       _Elemento.value = _retorno; 
       return false;  
     }  
   }
   return true;
}

function ValidTextoNumero(_Elemento,adicional){
if(typeof adicional == "undefined") adicional = "";
 var _completo = "abcdefghijklmnopqrstuvwxyz1234567890 ABCDEFGHIJKLMNOPQRSTUVWXYZ" + adicional;
 var contador = 0;
 var _retorno = "";
   for (var i=0; i < _Elemento.value.length; i++) {
     ubicacion = _Elemento.value.substring(i, i + 1);      
     if ((_completo.indexOf(ubicacion) != -1)) {  
       contador++;
       _retorno += ubicacion;
     } else {
       _Elemento.value = _retorno; 
       return false;  
     }  
   }
   return true;
}

function ValidEntero(_Elemento,adicional){
 if(typeof adicional == "undefined") adicional = "";
 var _completo = "1234567890" + adicional;
 var contador = 0;
 var _retorno = "";
   for (var i=0; i < _Elemento.value.length; i++) {
     ubicacion = _Elemento.value.substring(i, i + 1);      
     if ((_completo.indexOf(ubicacion) != -1)) {  
       contador++;
       _retorno += ubicacion;
     } else {
       _Elemento.value = _retorno; 
       return false;  
     }  
   }
   return true;
}

function ValidarEmail(email,msj) {      
    var patron=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    if (patron.test(email.value)==false){ 
        alert(msj);
        email.focus();
        return false;
    }
}

function ValidEmail(email) {      
    var patron=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    if (patron.test(email.value)==false){ 
        return false;
    }
	return true;
}

/*function CantidadLetras(objota,limite) {
 var nnn=objota.value.length;
	if (nnn>=limite) {
		objota.form.contador.value=limite;
		objota.value=objota.value.substr(0,limite);
		window.event.keyCode=0;
		return false;
	}
 objota.form.contador.value=nnn;
 return true;
}*/
//onfocus="CantidadLetras(this,2048);" onchange="CantidadLetras(this,2048);" onkeypress="CantidadLetras(this,2048);" onkeyup="CantidadLetras(this,2048);" onblur="CantidadLetras(this,2048);"
/*function CantidadLetras(e,limite) {
	if ($(e)[0].value.length>=limite) {
		$(e)[0].value=$(e)[0].value.substr(0,limite);
		window.event.keyCode=0;
		return false;
	}
 return true;
}
*/
jQuery(function($) {
$.datepicker.regional['es'] = 
  {
    clearText: 'Borra',
    clearStatus: 'Borra fecha actual',
    closeText: 'Cerrar',
    closeStatus: 'Cerrar sin guardar',
    prevText: '<Anterior',
    prevBigText: '<<',
    prevStatus: 'Mostrar mes anterior',
    prevBigStatus: 'Mostrar a&ntilde;o anterior',
    nextText: 'Siguiente>',
    nextBigText: '>>',
    nextStatus: 'Mostrar mes siguiente',
    nextBigStatus: 'Mostrar a&ntilde;o siguiente',
    currentText: 'Hoy',
    currentStatus: 'Mostrar mes actual',
    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    monthStatus: 'Seleccionar otro mes',
    yearStatus: 'Seleccionar otro a&ntilde;o',
    weekHeader: 'Sm',
    weekStatus: 'Semana del a&ntilde;o',
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Mi&eacute;rcoles', 'Jueves', 'Viernes', 'S&aacute;bado'],
    dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mi&eacute;', 'Jue', 'Vie', 'S&aacute;b'],
    dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'S&aacute;'],
    dayStatus: 'Set DD as first week day',
    dateStatus: 'Select D, M d',
    dateFormat: 'dd/mm/yy',
    firstDay: 1,
    initStatus: 'Seleccionar fecha',
    isRTL: false
  };
  
  $.datepicker.setDefaults($.datepicker.regional['es']);
});

//***********************************************************************************************
// FechaValida(FechaValidar)
//
// Valida que el día y el mes introducidos sean correctos. Además valida que el año introducido
// sea o no bisiesto
//23/01/2010
//***********************************************************************************************
function FechaValida(FechaValidar){
var dia,mes,anio;
var Fecha=$.trim(FechaValidar);
var EslashUno=FechaValidar.substring(3,2);
var EslashDos=FechaValidar.substring(5,6);
if(Fecha.length!=10) return false; //Validar la longitud de la fecha
if(EslashUno!='/' || EslashDos!='/') return false;//Validar que la fecha contenga /
dia=Fecha.split('/')[0];
mes=Fecha.split('/')[1];
anio=Fecha.split('/')[2];
var elMes = parseInt(mes,10);
if(elMes>12) return false; //que el mes no sea mayor a 12
// MES FEBRERO
if(elMes == 2){
	if(esBisiesto(anio)){
		if(parseInt(dia) > 29) return false;
		else return true;
	}else{
		if(parseInt(dia) > 28) return false;
		else return true;
	}
}
//RESTO DE MESES
if(elMes== 4 || elMes==6 || elMes==9 || elMes==11){
	if(parseInt(dia) > 30) return false;
}
return true;

}
//*****************************************************************************************
// esBisiesto(anio)
//
// Determina si el año pasado com parámetro es o no bisiesto
//*****************************************************************************************
function esBisiesto(anio){
var BISIESTO;
if(parseInt(anio)%4==0){
	if(parseInt(anio)%100==0){
		if(parseInt(anio)%400==0) BISIESTO=true;
		else BISIESTO=false;	
	}else{
		BISIESTO=true;
	}
}else{
	BISIESTO=false;
}
return BISIESTO;
}

//*****************************************************************************************
// CantidadLetras(e,limite,SetContador)
//
// Contador de letras 
//onKeyUp="CantidadLetras(this,300,'_contador');" onblur="CantidadLetras(this,300,'_contador');"
//*****************************************************************************************
function CantidadLetras(e,limite,SetContador) {	
	if(typeof SetContador == "undefined") SetContador = "";
	var count = $(e)[0].value.length;
	if (count>=limite+1) {
		$(e)[0].value=$(e)[0].value.substr(0,limite);
		window.event.keyCode=0;
		return false;
	}
	if(SetContador!=''){
	    $('#'+SetContador).text(count);
	}
    return true;
}

function EditorBasico(Selector){
	return $(Selector).tinymce({
		// Location of TinyMCE script
		script_url : 'js/JQuery/Editor/tiny_mce/tiny_mce.js',
	
		// General options
		theme : "advanced",
		skin : "o2k7",
		plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
	
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontsizeselect,|,search,replace,|,code,|,preview",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,|,forecolor,backcolor",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		//theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false
	
		// Example content CSS (should be your site CSS)
		//content_css : "css/content.css",
	
		// Drop lists for link/image/media/template dialogs
		//template_external_list_url : "lists/template_list.js",
		//external_link_list_url : "lists/link_list.js",
		//external_image_list_url : "lists/image_list.js",
		//media_external_list_url : "lists/media_list.js"
	
		// Replace values for the template plugin
		
		});			
}