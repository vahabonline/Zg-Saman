<?php

    /*
     *::: www.vahabonline.ir
     *::: myvahab@gmail.com
     */
	 
	function redirect($url){
		if(!headers_sent()) {
			header('Location: '. $url);
			exit;
		}
	}

    $Amount = intval($_POST['amount']);
    $systemUrl = $_POST['systemurl'];
    
	if($_POST['currencies'] == 'Rial'){
		$Amount = round($Amount/10);
	}
	
	if($_POST['afp']=='on'){
		$Fee = round($Amount*0.01);
	} else {
		$Fee = 0;
	}
	
	switch($_POST['mirrorname']){
		case 'آلمان': 
			$mirror = 'de';
			break;
		case 'ایران':
			$mirror = 'ir';
			break;
		default:
			$mirror = 'de';
			break;
	}
	
	$CallbackURL = $systemUrl . '/modules/gateways/callback/zp_saman.php?invoiceid='. $_POST['invoiceid'] .'&Amount='. $Amount;
	try {
		$client = new SoapClient('https://'. $mirror .'.zarinpal.com/pg/services/WebGate/wsdl', array('encoding' => 'UTF-8'));
	
		$result = $client->PaymentRequest(
											array(
													'MerchantID' 	=> $_POST['merchantID'],
													'Amount' 		=> $Amount+$Fee,
													'Description' 	=> 'Invoice ID: '. $_POST['invoiceid'],
													'Email' 		=> $_POST['email'],
													'Mobile' 		=> $_POST['cellnum'],
													'CallbackURL' 	=> $CallbackURL
												)
										);
	} catch (Exception $e) {
		echo '<h2>وقوع وقفه!</h2>';
		echo $e->getMessage();
	}
	if($result->Status == 100){ 
	    $vahabid = $result->Authority;
		$url = "https://$mirror.zarinpal.com/pg/StartPay/$vahabid/Sep";
		redirect($url);
	} else {
		echo "<body style='text-align:center;margin-top:10px;direction:rtl;background:#FFFFFF;'>";
		echo '<img src="'.$systemUrl.'/assets/vahabonline/wait.gif">';
		echo "<h2>وقوع خطا در ارتباط!</h2>"
			.'کد خطا'. $result->Status;
		echo '<br/><br/><div>بازگشت تا چند ثانیه دیگر</div></body>';
			header('Refresh: 8; URL='.$systemUrl.'/viewinvoice.php?id='.$_POST['invoiceid']);
	}
	
?>