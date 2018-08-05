<?php
header('Content-Type: application/json');
error_reporting(0);
//The username or email address of the account.
define('USERNAME', 'YOUR NIM HERE');
//The password of the account.
define('PASSWORD', 'YOUR PASSWORD HERE');
//id semester (sementara manual)
define('SEMESTER', '60000352');
//Set a user agent. This basically tells the server that we are using Chrome ;)
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.2309.372 Safari/537.36');
//Where our cookie information will be stored (needed for authentication).
define('COOKIE_FILE', 'cookie.txt');
//URL of the login form.
define('LOGIN_FORM_URL', 'https://siakad.akakom.ac.id/');
//Login action URL. Sometimes, this is the same URL as the login form.
define('LOGIN_ACTION_URL', 'https://siakad.akakom.ac.id/index.php?pModule=zdKbnKU=&pSub=zdKbnKU=&pAct=0dWjppyl');

//An associative array that represents the required form fields.
//You will need to change the keys / index names to match the name of the form
//fields.
$postValues = array(
	'username' => USERNAME,
	'password' => PASSWORD
);

//Initiate cURL.
$ch = curl_init();

//Set the URL that we want to send our POST request to. In this
//case, it's the action URL of the login form.
curl_setopt($ch, CURLOPT_URL, LOGIN_ACTION_URL);
//Tell cURL that we want to carry out a POST request.
curl_setopt($ch, CURLOPT_POST, true);
//Set our post fields / date (from the array above).
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postValues));
//We don't want any HTTPS errors.
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//Where our cookie details are saved. This is typically required
//for authentication, as the session ID is usually saved in the cookie file.
curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);
//Sets the user agent. Some websites will attempt to block bot user agents.
//Hence the reason I gave it a Chrome user agent.
curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
//Tells cURL to return the output once the request has been executed.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//Allows us to set the referer header. In this particular case, we are 
//fooling the server into thinking that we were referred by the login form.
curl_setopt($ch, CURLOPT_REFERER, LOGIN_FORM_URL);
//Do we want to follow any redirects?
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
//Execute the login request.
curl_exec($ch);

//Check for errors!
if(curl_errno($ch)){
	//echo 'Curl error: ' . curl_error($ch);
}

//We should be logged in by now. Let's attempt to access a password protected page
curl_setopt($ch, CURLOPT_URL, 'https://siakad.akakom.ac.id/index.php?pModule=wsaVl5yfncmQqMqpoaal&pSub=wsaVl5yfncmQqMqpoaal&pAct=18yZqg==');
//Use the same cookie file.
curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);

//Use the same user agent, just in case it is used by the server for session validation.
curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);

//We don't want any HTTPS / SSL errors.
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

//Execute the GET request and print out the result.
$abc = curl_exec($ch);
if($abc) {
	/*
	// FIND content
	$str = '<select name="lstSemester">';
	$arr = explode($str, $abc);
	$arr = explode('</table', $arr[1]);
	// reconstruct
	$new = $str . $arr[0] . '</select>';

	echo htmlentities($new);
	*/
	$postValues = array('lstSemester' => SEMESTER);

	curl_setopt($ch, CURLOPT_URL, 'https://siakad.akakom.ac.id/index.php?pModule=wsaVl5yfncmQqMqpoaal&pSub=wsaVl5yfncmQqMqpoaal&pAct=18yZqg==');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postValues));
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);
	curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_REFERER, 'https://siakad.akakom.ac.id/index.php?pModule=wsaVl5yfncmQqMqpoaal&pSub=wsaVl5yfncmQqMqpoaal&pAct=18yZqg==');

	$ct = curl_exec($ch);

	// FIND content
	$str = '<table class="table-common" width="100%">';
	$arr = explode($str, $ct);
	$arr = explode('</table', $arr[1]);
	// reconstruct
	$new = $str . $arr[0] . '</table>';

	//echo $new;

	$DOM = new DOMDocument();
	$DOM->loadHTML($new);
	$header = $DOM->getElementsByTagName('th');
	$detail = $DOM->getElementsByTagName('td');

	foreach($header as $NodeHeader) 
	{
		$aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
	}
	$i = 0;
	$j = 0;

	foreach($detail as $sNodeDetail) 
	{
		$aDataTableDetailHTML[$j][] = trim($sNodeDetail->textContent);
		$i = $i + 1;
		$j = $i % count($aDataTableHeaderHTML) == 0 ? $j + 1 : $j;
	}

	// get
	foreach ($aDataTableDetailHTML as $value) {
		if($value[3] != ''){
			$data[] = $value;
		}
	}


	// cek jumlah yg sudah ada nilai (data baru)
	$jml_new = 0;
	foreach ($data as $matkul) {
		// nilai ada di index ke-6
		// php 5.3+
		$matkul[6] == "" ?: $jml_new++;
	}
	// cek jumlah nilai lama
	$data_old = json_decode(file_get_contents('nilai.json'));
	$jml_old = 0;
	foreach ($data_old as $matkul) {
		$matkul[6] == "" ?: $jml_old++;
	}
	// jika beda, kirim notifikasi
	if($jml_new !== $jml_old) {
		sendNotif();
	}

	$jesen = json_encode($data);	
	// update file
	$file = fopen('nilai.json','w');
	fwrite($file, $jesen);
	fclose($file);
	
	// echo	  
	//echo $jesen;

	curl_close($ch);


} else {
	echo "err";
}

// kirim notif (FCM)
function sendNotif() {
	#API access key from Google API's Console
	define( 'API_ACCESS_KEY', 'FCM API KEY HERE' );
	//$registrationIds = $_GET['id'];
	$topic = 'global';
	#prep the bundle
	$msg = array
	(
		'body' 	=> 'nilai baru',
		'title'	=> 'Update Nilai',
		'icon'	=> 'myicon',/*Default Icon*/
		'sound' => 'mySound'/*Default sound*/
	);
	$fields = array
	(
	//'to'		=> $registrationIds,
		'to' => '/topics/' . $topic,
		'notification'	=> $msg
	);
	$headers = array
	(
		'Authorization: key=' . API_ACCESS_KEY,
		'Content-Type: application/json'
	);
	#Send Reponse To FireBase Server	
	$ch = curl_init();
	curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
	curl_setopt( $ch,CURLOPT_POST, true );
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	$result = curl_exec($ch );
	curl_close( $ch );
}
