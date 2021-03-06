<?php
	require_once "dbhandler.php";
	if (!isset($_SESSION)) {
		session_start();
	}
	
//CARICAMENTO IMMAGINE
	if(isset($_POST['carica_img'])){
		$titolo=mysqli_real_escape_string($connessione,sanitizeString($_POST['titolo'])); 
		$prezzo=mysqli_real_escape_string($connessione,sanitizeString($_POST['prezzo']));
		$tag1=mysqli_real_escape_string($connessione,sanitizeString($_POST['tag1']));
		$tag2=mysqli_real_escape_string($connessione,sanitizeString($_POST['tag2']));
		$tag3=mysqli_real_escape_string($connessione,sanitizeString($_POST['tag3']));
		$temp_name=$_FILES["file"]["name"];
		$ext = pathinfo($temp_name, PATHINFO_EXTENSION);
		$temp_name=$_FILES["file"]["tmp_name"];
		$estensioni = array('jpg','png','jpeg');
		$data=date('Y-m-d');
		if(empty($tag1)){
			$tag1='';
		}
		if(empty($tag2)){
			$tag2='';
		}
		if(empty($tag3)){
			$tag3='';
		}
		if(empty($titolo)){
			$errors['titolo']="Inserisci un titolo per la tua foto";
		}else if(empty($prezzo)){
			$errors['prezzo']="Inserisci un prezzo per la tua foto";
		}else if(!file_exists($temp_name))
				$errors['file']="Scegli una foto da caricare";
		else if(!isset($_POST['selectCategoria']) || $_POST['selectCategoria']==""){
			$errors['categoria']="Seleziona una categoria per la tua foto";
		}else if( ! in_array( $ext, $estensioni ) ) {
    			$errors['file']="Sono ammessi solo file .jpg, .jpeg e .png";			
		}
		else{
			$categoria=mysqli_real_escape_string($connessione,sanitizeString($_POST['selectCategoria']));
			$idcategoria=$_POST['selectCategoria'];
			$user=$_SESSION['username'];
			$connessione->query("INSERT INTO foto (titolo, venditore, prezzo, stato, categoria, data, tag1, tag2, tag3) 
				  VALUES('$titolo', '$user', '$prezzo', 'in attesa', '$idcategoria', '$data', '$tag1', '$tag2', '$tag3');");
			$result=$connessione->query("SELECT id FROM foto WHERE venditore='$user' ORDER BY id DESC LIMIT 1;");
			$ris=mysqli_fetch_assoc($result);
			$name=$ris['id']."_original.".pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
			if(isset($name)){
				if(!empty($name)){      
					$location='upload/';      
					if(!move_uploaded_file($temp_name, $location.$name)){
						$idfoto=$ris['id'];
						$connessione->query("DELETE FROM foto WHERE id='$idfoto';");
					}else{
						compressImage($location.$name,$location.$ris['id'].".".pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION),25);
					}
				}       
			}
			header("Location: fotoOk.php");
		}
	}

	function compressImage($source, $destination, $quality) {
	  $info = getimagesize($source);
	  if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg'){
		$image = imagecreatefromjpeg($source);
		imagejpeg($image, $destination, $quality);
	  }elseif ($info['mime'] == 'image/png' || $info['mime'] == 'image/PNG'){
		$image = imagecreatefrompng($source);
		imagepng($image,$destination);
	  }
	}

?>