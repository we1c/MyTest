<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/
    $targetFolder = '../../../api/public/image'; // Relative to the root
// Define a destination
// $targetFolder = '../data'; // Relative to the root

//$verifyToken = md5('unique_salt' . $_POST['timestamp']);

//if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	//echo is_uploaded_file($tempFile);
	$targetPath = $targetFolder;
    $md5file = md5($tempFile);
   
    $to = substr($md5file , 0 , 2);
    $four = substr($md5file , 2 , 2);
    $explode = array_reverse(explode('.',$_FILES['Filedata']['name']));
// 	switch (strtolower($explode[0])){
//     	case 'jpg':
//     	case 'jpeg':
//     	case 'png':
//     	case 'gif':		
//     		$ext = 'jpg';
//     		break;
//     	default:
//     		$ext = $explode[0];		
//     }
    $ext = '_image.jpg';
	$targetFile = rtrim($targetPath,'/') . '/' .$to.'/'.$four.'/'. $md5file.$ext;
	$images = explode('/',$targetFile);
	 
	//print_r($images);
    if(!file_exists($targetFolder.'/'.$to.'/'.$four)){
        mkdir($targetFolder.'/'.$to.'/'.$four,0777,true);
    }   
   // Validate the file type
    $fileParts = pathinfo($_FILES['Filedata']['name']);
    if ($fileParts['extension']) {
        move_uploaded_file($tempFile,$targetFile);
        $str = substr($targetFile,5);
        echo $md5file.'||'.$str;
    } else {
    	echo '2';
    }
?>