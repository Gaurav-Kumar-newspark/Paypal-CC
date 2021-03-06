<?php 
$globalpost = $_POST;
$fromGetInvoice = (isset($_REQUEST["invoiceid"]) && !empty($_REQUEST["invoiceid"]))?$_REQUEST["invoiceid"]:"";
include_once '../../../init.php';
include_once("../../../includes/functions.php");
include_once("../../../includes/gatewayfunctions.php");
include_once("../../../includes/invoicefunctions.php");
use WHMCS\Database\Capsule;
global $CONFIG;


	//Get The Orginal System URL which will be configured in the General Settings
	/*$SystemURLFromGeneral = $CONFIG['SystemURL'];
	$bar = "/";
	if(substr($SystemURLFromGeneral, -1) == "/")
	{
		$bar =  "";
	}
	$SystemURLFromGeneral = $SystemURLFromGeneral.$bar;*/

	$gatewaymodule = $ModuleName = "paypalGatewayv2"; # Enter your gateway module name here replacing template
	$GATEWAY = $params = getGatewayVariables($ModuleName); //Fetch Gateway Configuration


	//Fetch Hosting URL(Request URL) from the configuration of gateway
	$requestUrl = (isset($params["requestUrl"]) && !empty($params["requestUrl"]))?$params["requestUrl"]:"";
	$bar = "/";
	if(substr($requestUrl, -1) == "/")
	{
		$bar =  "";
	}
	$requestUrl = $requestUrl.$bar;

	//Fetch Hosting URL(Return Url URL) from the configuration of gateway
	$SystemURLFromGeneral = (isset($params["returnbackurl"]) && !empty($params["returnbackurl"]))?$params["returnbackurl"]:"";
	$bar = "/";
	if(substr($SystemURLFromGeneral, -1) == "/")
	{
		$bar =  "";
	}
	$SystemURLFromGeneral = $SystemURLFromGeneral.$bar;

	if(isset($globalpost) && !empty($globalpost))
	{
		$globalpost['custom'] = (isset($globalpost['custom']) && !empty($globalpost['custom']))?$globalpost['custom']:$fromGetInvoice;
		$logData = $globalpost;

		//global $CONFIG;
		$ginvioid = $invoiceid = $globalpost['custom'];
		$tranx_id = $globalpost['txn_id'];
		$fee = $globalpost['payment_fee'];
		$amount = $globalpost['payment_gross'];
		
		$invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY["name"]); # Checks invoice ID is a valid invoice number or ends processing

		if($globalpost['payment_status'] == "Completed")
		{
			$invoicestatustis = "";
			$invoicedasta = Capsule::table('tblinvoices')->where('id', '=', $ginvioid)->get();
			$invoicestatustis = (isset($invoicedasta[0]->status) && !empty($invoicedasta[0]->status))?$invoicedasta[0]->status:"";
			if($invoicestatustis != "Paid")
			{
				checkCbTransID($tranx_id);

				//Process to check the response valid from paypal
				$postipn = "cmd=_notify-validate";
				$orgipn = "";
				foreach( $globalpost as $key => $value ) 
				{
				    $orgipn .= (string) $key . " => " . $value . "\n";
				    $postipn .= "&" . $key . "=" . urlencode(WHMCS\Input\Sanitize::decode($value));
				}
				

				$paypaltest = (isset($params["PaypalTest"]) && $params["PaypalTest"] == "on")?"on":"";
				$url = "https://www.paypal.com/cgi-bin/webscr";
				if(!empty($paypaltest))
				{
					if($paypaltest == "on")
					{
						$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
					}
				}
				$reply = curltocheckandverify($url,$postipn,$whmcs);
				//Ends here

				if( !strcmp($reply, "VERIFIED") ) 
				{	
					addInvoicePayment($invoiceid, $tranx_id, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
					logTransaction($GATEWAY["name"], $logData, "Successful"); # Save to Gateway Log: name, data array, status
				}
			}
			
			if(isset($_GET["subaction"]) && $_GET["subaction"] == "back")
			{
				$RedirectLink = $SystemURLFromGeneral."viewinvoice.php?id=".$invoiceid;
				header("Location: ".$RedirectLink); /* Redirect browser */
				exit();
			}
			else
			{

				$RedirectLink = $requestUrl."viewinvoice.php?id=".$invoiceid;
				header("Location: ".$RedirectLink); /* Redirect browser */
				exit();
			}
		}	
		else
		{
			logTransaction($GATEWAY["name"], $logData, "Unsuccessful"); # Save to Gateway Log: name, data array, status
			if(isset($_GET["subaction"]) && $_GET["subaction"] == "back")
			{
				$RedirectLink = $SystemURLFromGeneral."viewinvoice.php?id=".$invoiceid;
				header("Location: ".$RedirectLink); /* Redirect browser */
				exit();
			}
			else
			{
				$RedirectLink = $requestUrl."viewinvoice.php?id=".$invoiceid;
				header("Location: ".$RedirectLink); /* Redirect browser */
				exit();
			}	
		}
	}
	else
	{
		if(isset($_GET['invoiceid']))
		{

			logTransaction($GATEWAY["name"], 'Cancel from paypal', "cancel"); # Save to Gateway Log: name, data array, status
			$globalpost["result"] = "cancel";
			$globalpost["custom"] = $_GET['invoiceid'];
			if(isset($_GET["subaction"]) && $_GET["subaction"] == "back")
			{
				$RedirectLink = $SystemURLFromGeneral."viewinvoice.php?id=".$_GET['invoiceid'];
				header("Location: ".$RedirectLink); /* Redirect browser */
				exit();
			}
			else
			{
				$RedirectLink = $requestUrl."viewinvoice.php?id=".$_GET['invoiceid'];
				header("Location: ".$RedirectLink); /* Redirect browser */
				exit();
			}
		}
		else
		{	
			logTransaction($GATEWAY["name"], 'Cancel from paypal', "cancel"); # Save to Gateway Log: name, data array, status
			$globalpost["result"] = "cancel";
			$globalpost["custom"] = "No Invoice Found!!";
			if(isset($_GET["subaction"]) && $_GET["subaction"] == "back")
			{
				$RedirectLink = $SystemURLFromGeneral."clientarea.php?action=invoices";
				header("Location: ".$RedirectLink); /* Redirect browser */
				exit();
			}
			else
			{
				$RedirectLink = $requestUrl."clientarea.php?action=invoices";
				header("Location: ".$RedirectLink); /* Redirect browser */
				exit();
			}
		}
	}

function curltocheckandverify($url = "",$postdata = "",$whmcs = "")
{
	$return = "";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_USERAGENT, "WHMCS V" . $whmcs->getVersion()->getCasual());
	$return = curl_exec($ch);
	curl_close($ch);
	return $return;
}

?>