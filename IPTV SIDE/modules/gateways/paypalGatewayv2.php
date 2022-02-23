<?php
use WHMCS\Database\Capsule;

/**
 *  
 */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Meta_Data_Parameters
 *
 * @return array
 */
function paypalGatewayv2_MetaData() {
    return array(
        'DisplayName' => 'Paypal Gateway Module V2',
        'APIVersion' => '2.0', // Use API Version 1.1 
    );
}

function paypalGatewayv2_config($params) {
	$requestUrl = (isset($params["requestUrl"]) && !empty($params["requestUrl"]))?$params["requestUrl"]:"";
	$ipndata = '<div class="alert alert-danger clearfix" role="alert" style="margin:0;"><i class="fas fa-info-circle fa-3x pull-left fa-fw"></i><div style="margin-left: 56px;"><p><strong>Important. Please add Request URL to get the IPN URL</strong></p> </div></div>';
	if($requestUrl != "")
	{
		$bar = "/";
		if(substr($requestUrl, -1) == "/")
		{
			$bar =  "";
		}
		$requestUrl = $requestUrl.$bar;
		$ipndata = '<div class="alert alert-info clearfix" role="alert" style="margin:0;"><i class="fas fa-info-circle fa-3x pull-left fa-fw"></i><div style="margin-left: 56px;"><p><strong>Important.</strong> Make sure you have setup the IPN on paypal account.</p> <div class="input-group"><span class="input-group-addon">IPN URL</span><input type="text" id="qbcronPhp" value="'.$requestUrl.'modules/gateways/callback/paypalGatewayv2.php" class="form-control" readonly="readonly"><span class="input-group-btn"><button class="btn btn-default copy-to-clipboard" data-clipboard-target="#qbcronPhp" type="button"><i class="fal fa-copy" title="Copy to clipboard"></i><span class="sr-only">Copy to clipboard&gt;</span></button></span></div> </div></div>';
	}






    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Paypal Gateway Module V2',
        ),
        'requestUrl' => array(
            'FriendlyName' => 'Hosting Site URL',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Enter your Hosting Site URL- http://yourwebsite.com',
        ),
        'returnbackurl' => array(
            'FriendlyName' => 'Return URL',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Enter your Request Site URL- http://yourwebsite.com',
        ),
        'companyNamewillbe' => array(
            'FriendlyName' => 'Item Name',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => "(  Order's details  that's sending to PayPal , example Hosting  Products Name etc )",
        ),
        // a text field type allows for single line text input
        'reciveremail' => array(
            'FriendlyName' => 'Paypal Business Email',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '',
        ),
        // a text field type allows for single line text input
        'PaypalTest' => array(
            'FriendlyName' => 'Development',
            'Type' => 'yesno',
            'Default' => 'on',
            'Size' => '100',
            'Description' => 'Click for development',
        ),
        'importantnote' => array(
            'Description' => $ipndata,
        ),
        'PGV' => array(
            'Description' => '<h2>Version 2.0</h2>',
        ),
    );
}


function paypalGatewayv2_link($params)
{
	 // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];


	$InvoiceId = (isset($params["invoiceid"]) && !empty($params["invoiceid"]))?$params["invoiceid"]:"";
	$requestUrl = (isset($params["requestUrl"]) && !empty($params["requestUrl"]))?$params["requestUrl"]:"";
	$PayPalEmail = (isset($params["reciveremail"]) && !empty($params["reciveremail"]))?$params["reciveremail"]:"";
	$companyNamewillbe = (isset($params["companyNamewillbe"]) && !empty($params["companyNamewillbe"]))?$params["companyNamewillbe"]:"";
	if($requestUrl != "")
	{
		$bar = "/";
		if(substr($requestUrl, -1) == "/")
		{
			$bar =  "";
		}
		$requestUrl = $requestUrl.$bar;
		$newteamwillbe = $companyNamewillbe." - Invoice #".$InvoiceId;

		$htmlOutput = "<form method='post' action='".$requestUrl."paypalrequesting.php'>";
		$htmlOutput.= "<input type='hidden' value='_xclick' name='cmd'>";
		$htmlOutput.= "<input type='hidden'  value='".$PayPalEmail."' name='business'>";
		$htmlOutput.= "<input type='hidden'  value='".$newteamwillbe."' name='item_name'>";
		$htmlOutput.= "<input type='hidden'  value='".$amount."' name='amount'>";
		$htmlOutput.= "<input type='hidden'  value='".$firstname."' name='first_name'>";
		$htmlOutput.= "<input type='hidden'  value='".$lastname."' name='last_name'>";
		$htmlOutput.= "<input type='hidden'  value='".$email."' name='email'>";
		$htmlOutput.= "<input type='hidden'  value='".$address1."' name='address1'>";
		$htmlOutput.= "<input type='hidden'  value='".$city."' name='city'>";
		$htmlOutput.= "<input type='hidden'  value='".$state."' name='state'>";
		$htmlOutput.= "<input type='hidden'  value='".$postcode."' name='zip'>";
		$htmlOutput.= "<input type='hidden'  value='".$country."' name='country'>";
		$htmlOutput.= "<input type='hidden'  value='".$country."' name='country'>";
		$htmlOutput.= "<input type='hidden'  value='".$phone."' name='night_phone_a'>";
		$htmlOutput.= "<input type='hidden'  value='".$phone."' name='night_phone_b'>";
		$htmlOutput.= "<input type='hidden'  value='".$currencyCode."' name='currency_code'>";
		$htmlOutput.= "<input type='hidden'  value='".$InvoiceId."' name='custom'>";
		$htmlOutput.= "<input type='hidden'  value='utf-8' name='charset'>";
		$htmlOutput.= "<input type='hidden'  value='2' name='rm'>";
        $htmlOutput.= "<input type='hidden'  value='WHMCS-BuyNowBF' name='bn'>";
		$htmlOutput.= "<input type='hidden'  value='AddPayment2' name='AddPayment2'>";
		$htmlOutput.= "<button type='submit' name='AddPaymentany' style='padding: 0px;'><img src='modules/gateways/paypalGatewayv2/images/x-click-but03.gif'></button>";
		$htmlOutput.= '</form>';
	}
	else
	{
		$htmlOutput = "Sorry for the inconvenience, Please try with another payment method";
	}
	return $htmlOutput;
}
/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function paypalGatewayv2_refund($params)
{
    // Gateway Configuration Parameters
    $accountId = $params['accountID'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];
    // Transaction Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];
    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];
    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];
    // perform API call to initiate refund and interpret result
    return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
        // Unique Transaction ID for the refund transaction
        'transid' => $refundTransactionId,
        // Optional fee amount for the fee value refunded
        'fees' => $feeAmount,
    );
}
/**
 * Cancel subscription.
 *
 * If the payment gateway creates subscriptions and stores the subscription
 * ID in tblhosting.subscriptionid, this function is called upon cancellation
 * or request by an admin user.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/subscription-management/
 *
 * @return array Transaction response status
 */
function paypalGatewayv2_cancelSubscription($params)
{
    // Gateway Configuration Parameters
    $accountId = $params['accountID'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];
    // Subscription Parameters
    $subscriptionIdToCancel = $params['subscriptionID'];
    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];
    // perform API call to cancel subscription and interpret result
    return array(
        // 'success' if successful, any other value for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
    );
}