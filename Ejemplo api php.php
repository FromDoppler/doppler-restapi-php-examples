<?php
$apikey = "Api Key";
$accountName = "Nombre de cuenta";
$htmlCode = 'Tu codigo html aqui';

function ObtainResult($options, $url, $returnId){
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		$headers = $http_response_header;
		$matches = array();
		preg_match('#HTTP/\d+\.\d+ (\d+)#', $headers[0], $matches);
		$statusCode = intval($matches[1]);
		if ($statusCode >= 200 && $statusCode < 300 && $returnId == true) {
			$decodeResult = json_decode($result, true);
			echo "Message : " . $decodeResult['message']  . "\r\n";
			echo "id de creacion : " . $decodeResult['createdResourceId'];
			return $decodeResult['createdResourceId'];
		} else if ($statusCode >= 200 && $statusCode < 300 && $returnId == false){
			$decodeResult = json_decode($result, true);
			echo "Message : " . $decodeResult['message']  . "\r\n";
		} else if ($statusCode >= 400) {
			echo "\r\n**** ERROR ****\r\n";
			echo "==== HEADERS ====";
			var_dump($headers);
			echo "==== BODY ====";
			var_dump($result);
		} else  {
			echo "\r\n**** UNEXPECTED STATUS CODE ****\r\n";
			echo "==== HEADERS ====";
			var_dump($headers);
			echo "==== BODY ====";
			var_dump($result);
		}
	}

	
function CreateList($apikey, $accountName, $listName){
	$url = "https://restapi.fromdoppler.com/accounts/" . $accountName . "/lists";
	$data = array(
		'name' => $listName
		);
	$options = array(
		'http' => array(
			'header' => "Authorization: token " . $apikey . "\r\nContent-type: application/json",
			'method' => 'POST',
			'content' => json_encode($data),
			'ignore_errors' => true
		)
	);
	$listId = ObtainResult($options, $url, true);
	return $listId;
}

function SubscribersToList($apikey, $accountName, $listId, $subscriberEmail){
	$url = "https://restapi.fromdoppler.com/accounts/" . $accountName . "/lists/" . $listId . "/subscribers"; // usar /accounts/$accountName/lists/$listId/subscribers/import en el caso de agregar mas de un subscriptor

	$data = array(
		'email' => $subscriberEmail
	);

	$options = array(
	  'http' => array(
		'header' => "Authorization: token " . $apikey . "\r\nContent-type: application/json\r\n",
		'method' => 'POST',
		'content' => json_encode($data),
		'ignore_errors' => true
	  )
	);
	ObtainResult($options, $url, false);
}

function CreateCampaign($apikey, $accountName, $campaignName, $campaignFromName, $campaignFromEmail, $campaignSubject, $campaignPreheader, $campaignReplyTo){
	$url = "https://restapi.fromdoppler.com/accounts/" . $accountName . "/campaigns";
	$data = array(
		'name' => $campaignName,
		'fromname' => $campaignFromName,
		'fromemail' => $campaignFromEmail,
		'subject' => $campaignSubject,
		'preheader' => $campaignPreheader,
		'replyTo' => $campaignReplyTo
	);

	$options = array(
	  'http' => array(
		'header' => "Authorization: token " . $apikey . "\r\nContent-type: application/json",
		'method' => 'POST',
		'content' => json_encode($data),
		'ignore_errors' => true
	  )
	);
	$campaignId = ObtainResult($options, $url, true);
	return $campaignId;
}

function ContentToCampaign($apikey, $accountName, $campaignId, $htmlCode){
	$url = "https://restapi.fromdoppler.com/accounts/" . $accountName . "/campaigns/" . $campaignId . "/content";
	$data = $htmlCode;
	$options = array(
	  'http' => array(
		'header' => "Authorization: token " . $apikey . "\r\nContent-type: text/html\r\n",
		'method' => 'PUT',
		'content' => $data,
		'ignore_errors' => true
	  )
	);
	ObtainResult($options, $url, false);
}

function ListToCampaign($apikey, $accountName, $campaignId, $listId){
	$url = "https://restapi.fromdoppler.com/accounts/" . $accountName . "/campaigns/" . $campaignId . "/recipients";
	$data = array(
		'Lists' => array(
			array('id' => $listId)
		)
	);
	$options = array(
		'http' => array(
			'header' => "Authorization: token $apikey\r\nContent-type: application/json\r\n",
			'method' => 'PUT',
			'content' => json_encode($data),
			'ignore_errors' => true
		)
	);
	ObtainResult($options, $url, false);
}

function SendCampaign ($apikey, $accountName, $campaignId){
	$url = "https://restapi.fromdoppler.com/accounts/" . $accountName . "/campaigns/" . $campaignId . "/shippings";

	$data = array(
		'type' => 'immediate' // immediate or scheduled
	);

	$options = array(
	  'http' => array(
		'header' => "Authorization: token " . $apikey . "\r\nContent-type: application/json\r\n",
		'method' => 'POST',
		'content' => json_encode($data),
		'ignore_errors' => true
	  )
	);
	ObtainResult ($options, $url, false);
}

$listId = CreateList($apikey, $accountName, "lista creada por api");

SubscribersToList($apikey, $accountName, $listId, "email subscriptor");

$campaignId = CreateCampaign($apikey, $accountName, 'Nombre de campaña', 'Nombre remitente', 'From Email', 'Asunto de campaña', 'Preencabezado campaña', 'Email remitente');

ContentToCampaign($apikey, $accountName, $campaignId, $htmlCode);

ListToCampaign($apikey, $accountName, $campaignId, $listId);

SendCampaign ($apikey, $accountName, $campaignId);
