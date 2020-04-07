<?php
	/**
	*
	*/
	define('UPLOAD_DIR', 'images/');
	$face = $_POST['face'];
	$screen = $_POST['screen'];

	$face = str_replace('data:image/jpeg;base64,', '', $face);
	$face = str_replace(' ', '+', $face);
	$face_data = base64_decode($face);
	
	$screen = str_replace('data:image/png;base64', '', $screen);
	$screen = str_replace(' ', '+', $screen);
	$screen_data = base64_decode($screen);

	$face_file = UPLOAD_DIR . "face/". uniqid() . '.jpeg';
	$screen_file = UPLOAD_DIR . "screen/". uniqid() . '.png';

	$face_res = file_put_contents($face_file, $face_data);
	$screen_res = file_put_contents($screen_file, $screen_data);

	
	if ($face_res && $screen_res){
		echo "[face path: ]" . $face_file ." [screen path :]" . $screen_file;
	}
?>