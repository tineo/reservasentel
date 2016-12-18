//Autor  Fernando Torres Coral.
//Creado 08 de Febrero, 2010

//contenedor = Es donde esta el control
//id = el id de la imagen a leer
//subdirectorio = el directorio uploads es por defecto, indicar en que sub directorio se gusrdaran los archivo emjemplo uploads/otros 
//imagen = la imagen de la db
//

//Nuevo Control

function UploadImage(contenedor,id,subdirectorio,imagen,RecorteWidth,RecorteHeight){
	if(typeof RecorteWidth=='undefined'){
		RecorteWidth=0;
		RecorteHeight=0;	
	}
	var img = !$.trim(imagen)==''?"subirDisable.gif":"subirEnable.gif";
    var html="<table cellpadding='3' cellspacing='0'>"+
		     "  <tr>"+
		     "   <td id='"+contenedor+"boton' colspan='2' align='left' width='200px'><img src='images/"+img+"' alt='' id='"+contenedor+"img' /></td>"+
		     "  </tr>"+
		     " <tbody>"+
			 "  <tr><td nowrap='nowrap' class='gris1 "+contenedor+"mostrar' style='color:#E8310E' ></td></tr>";
	if(!$.trim(imagen)=='') html+=LoadImg(contenedor,id,subdirectorio,imagen);						   
						   
	html+=" </tbody>"+
		  "</table>";
	$('#'+contenedor).html(html);
	//Para el control PopUp PiroBox
	if(!$.trim(imagen)==''){
		$('.PiroboxEliminar').remove();
		$('#td_Grilla').piroBox_ext({piro_speed:700,bg_alpha:0.5,piro_scroll:true});
	}
	
	var UploadImages = new AjaxUpload($('#'+contenedor+'boton')[0].childNodes[0], {
					 action: 'includes/ajaxupload2.php?subdirectorio='+subdirectorio,
					 data : {
				       	 'key1' : "This data won't",
						 'key2' : "be send because",
						 'key3' : "we will overwrite it"
					 },	
					 onSubmit : function(file , ext){
				 		 // Allow only images. You should add security check on the server-side.
						 if (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext)){
							 this.setData({
								 'command': 'Subir',
								 'RecorteWidth':RecorteWidth,
								 'RecorteHeight':RecorteHeight,
								 'TipodeArchivo':'IMAGEN'
							 });					
							 $('#'+contenedor +' .'+contenedor+'mostrar').html('Subiendo... ' + file);	
						 }else{					
							 $('#'+contenedor +' .'+contenedor+'mostrar').html('Error: Solo esta permitido subir archivos con extensión jpg,png,jpeg y swf.');
							 return false;				
						 }		
					 },
					 onComplete : function(file,response){//Cuando ya subio el archivo
						 this.disable();
						 $('#'+contenedor +' .'+contenedor+'mostrar').html(LoadImg(contenedor,id,subdirectorio,response,RecorteWidth,RecorteHeight));
						 $('#'+contenedor+'img').attr("src","images/subirDisable.gif");
						 //Para el control PopUp PiroBox
						 $('.PiroboxEliminar').remove();
						 $('#td_Grilla').piroBox_ext({piro_speed:700,bg_alpha:0.5,piro_scroll:true});
					 }		
				});
	if(!$.trim(imagen)=='') UploadImages.disable();
}

function LoadImg(contenedor,id,subdirectorio,imagen,RecorteWidth,RecorteHeight){
	  var Rel=imagen.split('.')[1]=='swf'?"rel='iframe-956-90'":"rel='single'";
	  return  " <table cellpadding='3' cellspacing='0'>"+
	  		  " <tr>"+
			  "    <td id='"+id+"' img='"+imagen+"' style='border-right: #e7e7e7 1px solid; border-top: #e7e7e7 1px solid;border-left: #e7e7e7 1px solid; border-bottom: #e7e7e7 1px solid'>"+
			  "        <a href='uploads/"+subdirectorio+'/'+imagen+"' "+Rel+"  class='pirobox img textoLink'>"+imagen+"</a></td>"+
			  "    <td style='border-right: #e7e7e7 1px solid; border-top: #e7e7e7 1px solid;border-left: #e7e7e7 1px solid; border-bottom: #e7e7e7 1px solid'>"+
			  "        <a class='textoLink' href='javascript:;' onClick=\"ConfirmaEliminaImage('"+imagen+"','"+contenedor+"','"+id+"','"+subdirectorio+"',"+RecorteWidth+","+RecorteHeight+")\"><img src='images/ic-eliminar.gif' width='16' height='16' border='0' />&nbsp;Eliminar</a></td>"+
			  " </tr>"+
			  " <table>";
}
/*function LoadImg(contenedor,id,subdirectorio,imagen,RecorteWidth,RecorteHeight){
	  return  " <table cellpadding='3' cellspacing='0'>"+
	  		  " <tr>"+
			  "    <td id='"+id+"' img='"+imagen+"' style='border-right: #e7e7e7 1px solid; border-top: #e7e7e7 1px solid;border-left: #e7e7e7 1px solid; border-bottom: #e7e7e7 1px solid'>"+
			  "        <a class='img gris2' href='uploads/"+subdirectorio+'/'+imagen+"' onClick='return LoadIMGControls(this);' title='"+imagen+"'>"+imagen+"</a></td>"+
			  "    <td style='border-right: #e7e7e7 1px solid; border-top: #e7e7e7 1px solid;border-left: #e7e7e7 1px solid; border-bottom: #e7e7e7 1px solid'>"+
			  "        <a class='gris2' href='javascript:;' onClick=\"ConfirmaEliminaImage('"+imagen+"','"+contenedor+"','"+id+"','"+subdirectorio+"',"+RecorteWidth+","+RecorteHeight+")\"><img src='images/ic-eliminar.gif' width='16' height='16' border='0' />&nbsp;Eliminar</a></td>"+
			  " </tr>"+
			  " <table>";
}*/

function ConfirmaEliminaImage(imagen,contenedor,id,subdirectorio,RecorteWidth,RecorteHeight){
	if(!confirm("desea eliminar el archivo seleccionado?")) return;
	$.ajax({
	   type: "POST",
	   url: "includes/ajaxupload2.php?subdirectorio="+subdirectorio,
	   data: "command=Eliminar&imagen="+imagen,   
	   dataType:  "html",
	   success: function(j){
		  UploadImage(contenedor,id,subdirectorio,'',RecorteWidth,RecorteHeight);
	   },
	   error: function(){
		 alert("No se pudo eliminar el archivo.");
	   }
	 });
	
}












/*function LoadIMGControls(obj){  
    return parent.GB_showImage(obj.title,obj.href);
}*/

function UploadPDF(contenedor,id,subdirectorio,imagen){	
	var img = !$.trim(imagen)==''?"subirDisable.gif":"subirEnable.gif";
    var html="<table cellpadding='3' cellspacing='0'>"+
		     "  <tr>"+
		     "   <td id='"+contenedor+"boton' colspan='2' align='left' width='200px'><img src='images/"+img+"' alt='' id='"+contenedor+"img' /></td>"+
		     "  </tr>"+
		     " <tbody>"+
			 "  <tr><td nowrap='nowrap' class='gris1 "+contenedor+"mostrar' style='color:#E8310E' ></td></tr>";
	if(!$.trim(imagen)=='') html+=LoadPDF(contenedor,id,subdirectorio,imagen);						   
						   
	html+=" </tbody>"+
		  "</table>";
	$('#'+contenedor).html(html);	
	
	var UploadPDF = new AjaxUpload($('#'+contenedor+'boton')[0].childNodes[0], {
					 action: 'includes/ajaxupload2.php?subdirectorio='+subdirectorio,
					 data : {
				       	 'key1' : "This data won't",
						 'key2' : "be send because",
						 'key3' : "we will overwrite it"
					 },	
					 onSubmit : function(file , ext){
						 if (ext && /^(pdf|doc|docx|PDF|DOC|DOCX)$/.test(ext)){
							 this.setData({
								 'command': 'Subir',
								 'TipodeArchivo':'OFFICE'
							 });					
							 $('#'+contenedor +' .'+contenedor+'mostrar').html('Subiendo... ' + file);	
						 }else{					
							 $('#'+contenedor +' .'+contenedor+'mostrar').html('Error: Solo esta permitido subir archivos con extensión pdf y doc');
							 return false;				
						 }		
					 },
					 onComplete : function(file,response){//Cuando ya subio el archivo
						 this.disable();
						 $('#'+contenedor +' .'+contenedor+'mostrar').html(LoadPDF(contenedor,id,subdirectorio,response));
						 $('#'+contenedor+'img').attr("src","images/subirDisable.gif");
					 }		
				});
	if(!$.trim(imagen)=='') UploadPDF.disable();
}

function LoadPDF(contenedor,id,subdirectorio,imagen){
	  //var nomImagen = imagen.split("@")[1];
	  return  " <table cellpadding='3' cellspacing='0'>"+
		  	  " <tr>"+
			  "    <td id='"+id+"' img='"+imagen+"' style='border-right: #e7e7e7 1px solid; border-top: #e7e7e7 1px solid;border-left: #e7e7e7 1px solid; border-bottom: #e7e7e7 1px solid'>"+
			  "        <a class='img gris1' href='includes/download.php?ruta=../admin/uploads/"+subdirectorio+'/'+imagen+"&nomFile="+imagen+"' >"+imagen+"</a></td>"+
			  "    <td style='border-right: #e7e7e7 1px solid; border-top: #e7e7e7 1px solid;border-left: #e7e7e7 1px solid; border-bottom: #e7e7e7 1px solid'>"+
			  "        <a class='gris1' href='javascript:;' onClick=\"ConfirmaEliminaPDF('"+imagen+"','"+contenedor+"','"+id+"','"+subdirectorio+"')\"><img src='images/ic-eliminar.gif' width='16' height='16' border='0' />&nbsp;Eliminar</a></td>"+
			  " </tr>"+
			  " </table>";
}

function ConfirmaEliminaPDF(imagen,contenedor,id,subdirectorio){
	if(!confirm("desea eliminar el archivo seleccionado?")) return;
	$.ajax({
	   type: "POST",
	   url: "includes/ajaxupload2.php?subdirectorio="+subdirectorio,
	   data: "command=Eliminar&imagen="+imagen,   
	   dataType:  "html",
	   success: function(j){
		  UploadPDF(contenedor,id,subdirectorio,'');
	   },
	   error: function(){
		 alert("No se pudo eliminar el archivo.");
	   }
	 });
	
}

