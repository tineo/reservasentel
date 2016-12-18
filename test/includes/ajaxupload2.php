<?php
include('verot_upload.php');
class AjaxUpload{
   
	function Subir(){
		$TipodeArchivo=$_REQUEST["TipodeArchivo"];
		if($TipodeArchivo=='IMAGEN'){
			$handle = new Upload($_FILES['userfile']);	
			$Width=intval($_REQUEST["RecorteWidth"]);
			$Height=intval($_REQUEST["RecorteHeight"]);
			if ($handle->uploaded) {
				//Para realizar recorte a las imagenes
				if($Width>0 && $Height>0){
					$handle->image_resize = true;
					//$handle->image_ratio = true;
					$handle->image_ratio_fill = true; //si es mas corto inserta en blanco para cuadrar
					$handle->image_x = $Width;
					$handle->image_y = $Height;
				}			
				
				$handle->file_auto_rename = true;//Para renombrar el archivo
				//$handle->file_new_name_body = 'pruebaaa'; //Nuevo nombre a la imagen
				//$handle->file_name_body_add = '_uploaded';// Concatena al final con el nombre del archivo
				//$handle->file_name_body_pre = 'tb_';// Concatena al inicio con el nombre del archivo
				
				$handle->Process('../uploads/'.$_REQUEST["subdirectorio"].'/');
				
				if ($handle->processed) {
					//$info = getimagesize($handle->file_dst_pathname);
					///$nom_p = $handle->file_dst_name;
					$handle->clean();
					echo $handle->file_dst_name;				
				} else {
					echo $handle->error;
				}
			}
		}else{
			  $UploadDir='../uploads/'.$_REQUEST["subdirectorio"].'/';
			  $RetornoNombreArchivo='';
			  if (file_exists($UploadDir.basename($_FILES['userfile']['name']))) {
				$UpdatedFileName=$this->RenombrarArchivo($UploadDir,basename($_FILES['userfile']['name']));	
				$UploadFile=$UploadDir.$UpdatedFileName;		  
				move_uploaded_file($_FILES['userfile']['tmp_name'], $UploadFile);
				$RetornoNombreArchivo=$UpdatedFileName;	
			  }else{
				 $UploadFile=$UploadDir.basename($_FILES['userfile']['name']);
				 move_uploaded_file( $_FILES['userfile']['tmp_name'], $UploadFile);
				 $RetornoNombreArchivo=$_FILES['userfile']['name'];	
			  }
			  echo $RetornoNombreArchivo;		
		}			
	}  
  

	function Eliminar(){
	   $uploaddir = '../uploads/'.$_REQUEST["subdirectorio"].'/';
	   $img= $uploaddir . $_POST['imagen'];	
	   if (unlink($img)) {
		  echo "success";
	   } else {
		  echo "error";
	   }
	}
	
	function RenombrarArchivo($UploadDir,$file){
		$pos=strrpos($file,'.');
		$ext=substr($file,$pos); 
		$fName=substr($file,0,$pos);
		$exist=false;
		$i=1;		
		while(!$exist){
			$VerificarArchivo=$UploadDir.$fName.$i.$ext;			
			if(!file_exists($VerificarArchivo)){				 
				$exist=true;
				$file=$dr.$fName.$i++.$ext;
				break;
			}
			 $i++;
		}		
		return $file;		
	}	
}

$AjaxUploads = new AjaxUpload();

if($_POST['command'] == "Subir"){
 $AjaxUploads->Subir(); 
}else if($_POST['command'] == "Eliminar"){
 $AjaxUploads->Eliminar(); 
}


?>