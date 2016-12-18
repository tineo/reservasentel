<?php
include('../../config.php');
class AjaxUpload{
    function Subir(){
	   $uploaddir = '../uploads/'.$_REQUEST["subdirectorio"].'/';
	   $nuevaImg=md5(uniqid(rand(), true)).'@'.$_FILES['userfile']['name'];
		$uploadfile = $uploaddir . basename($nuevaImg);		
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		  echo $nuevaImg;		  
		} else {
		  echo "error";
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
}

$AjaxUploads = new AjaxUpload();

if($_POST['command'] == "Subir"){
 $AjaxUploads->Subir(); 
}else if($_POST['command'] == "Eliminar"){
 $AjaxUploads->Eliminar(); 
}

?>