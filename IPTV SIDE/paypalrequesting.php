<?php
include_once 'init.php';
include_once 'includes/gatewayfunctions.php';
use WHMCS\Database\Capsule;

global $CONFIG;

$ModuleName = "paypalGatewayv2";
$params = getGatewayVariables($ModuleName);
if(isset($_POST['AddPayment2']))
{
	$invoiceid =  (isset($_POST["custom"]) && !empty($_POST["custom"]))?$_POST["custom"]:"";
	$requestUrl = (isset($params["requestUrl"]) && !empty($params["requestUrl"]))?$params["requestUrl"]:"";
	$bar = "/";
	if(substr($requestUrl, -1) == "/")
	{
		$bar =  "";
	}
	$requestUrl = $requestUrl.$bar;
	$returnandcancel = $requestUrl.'modules/gateways/callback/paypalGatewayv2.php?invoiceid='.$invoiceid.'&subaction=back';



	$paypaltest = (isset($params["PaypalTest"]) && $params["PaypalTest"] == "on")?"on":"";
	$PaypalLinkToRequest = "https://www.paypal.com/cgi-bin/webscr";
	if(!empty($paypaltest))
	{
		if($paypaltest == "on")
		{
			$PaypalLinkToRequest = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		}
	}

	$datafromback = "";
	foreach ($_POST as $kname => $kval) 
	{
		$datafromback.= "<input type='hidden' name='".$kname."' value='".$kval."'>";
	}

	$finalHtml = "<form method='POST' action='".$PaypalLinkToRequest."' id='PayPalFrom'>";
	$finalHtml.= $datafromback;
	$finalHtml.= "<input type='hidden' value='".$returnandcancel."' name='return'>";
	$finalHtml.= "<input type='hidden' value='".$returnandcancel."' name='cancel_return'>";
	$finalHtml.= "</form>";
	$finalHtml.= "<script>
			document.getElementById('PayPalFrom').submit();
			</script>";
	echo $finalHtml;
}
else
{
	header("Location: index.php"); /* Redirect browser */
	exit();
}
?>