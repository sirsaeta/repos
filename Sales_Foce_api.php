<?php

class Sales_Force {
	private $authorization;

	public function __construct() {
		$data = file_get_contents("token_sales_force.json");
		$token_sales_force = json_decode($data, true);
        $this->authorization = "Bearer ".$token_sales_force['access_token'];
    }

	private function getAuthorization() {
		return $this->authorization;
	}

	function getOrderStatus(string $idPurchase) {
		$headers = array(
			"Cookie: BrowserId=6qKoFWJWEeuYCZGv0C6kSw"
		);
		return $this->cUrlGet("https://telecomcrm.my.salesforce.com/services/apexrest/tmf-api/productOrderingManagement/v4/productOrder/?deepLevel=2&excludeNulls=false&externalId=$idPurchase",null,$headers);
	}

	function sendStatusCommentID(string $id, string $externalId, $currentStatusName, $requestedStatusName)
	{
		$data = array(
			"TecoOrderJson__c" => array(
				"id" => "$id",
                "externalId" => $externalId,
				"currentStatus" => array(
					"name" => $currentStatusName
				),
				"requestedStatus" => array(
					"name" => $requestedStatusName
				)
			)
		);
		$payload = json_encode($data);
		$headers = array(
			"Cookie: BrowserId=6qKoFWJWEeuYCZGv0C6kSw"
		);
		$url = "https://telecomcrm.my.salesforce.com/services/data/v48.0/sobjects/TecoOrderEvent__e";

		return $this->cUrlPost($url, $payload,$headers);
	}

	private function cUrlPost($url, $payload, $headers_add=array(), $status = null)
	{
		$headers = array(
			"Content-Type: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Authorization: ".$this->getAuthorization()
		);
		foreach ($headers_add as $key => $value) {
			array_push($headers,$value);
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		if ($payload) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$respuesta = explode("\n\r\n", $head);
		//var_dump($respuesta);
		//echo "<br>";
		$headers = $respuesta[0];
		if (count($respuesta)==3) {
			$body = $respuesta[2];
		} else {
			$body = $respuesta[1];
		}
		

		//var_dump($body);
		
		if(!$head)
		{
			return FALSE;
		}
		
		if($status === null)
		{
			if($httpCode < 400)
			{
				return $body;
			}
			else
			{
				return FALSE;
			}
		}
		elseif($status == $httpCode)
		{
			return $body;
		}

		return FALSE;
	}

	private function cUrlGet($url, $status = null, $headers_add=array())
	{
		$headers = array(
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Authorization: ".$this->getAuthorization()
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$respuesta = explode("\n\r\n", $head);
		$headers = $respuesta[0];
		$body = $respuesta[1];
        //var_dump($respuesta);
		//var_dump($body);
		
		if(!$head)
		{
			return FALSE;
		}
		
		if($status === null)
		{
			if($httpCode < 400)
			{
				return $body;
			}
			else
			{
				return FALSE;
			}
		}
		elseif($status == $httpCode)
		{
			return $body;
		}

		return FALSE;
	}

	private function cUrlDelete($url, $payload, $headers_add=array(), $status = null)
	{
		$headers = array(
			"Content-Type: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Authorization: ".$this->getAuthorization()
		);
		foreach ($headers_add as $key => $value) {
			array_push($headers,$value);
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		if ($payload) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$respuesta = explode("\n\r\n", $head);
		$headers = $respuesta[0];
		$body = $respuesta[1];

		//var_dump($body);
		
		if(!$head)
		{
			return FALSE;
		}
		
		if($status === null)
		{
			if($httpCode < 400)
			{
				return $body;
			}
			else
			{
				return FALSE;
			}
		}
		elseif($status == $httpCode)
		{
			return $body;
		}

		return FALSE;
	}
}

if (!Empty($_GET["getOrderStatus"])) {
	$idPurchase = (Empty($_GET["idPurchase"])) ? die("idPurchase es obligatorio") : $_GET["idPurchase"];
	$salesforce = new Sales_Force;
	$response = $salesforce->getOrderStatus($idPurchase);
	$data = json_decode($response, true);
	var_dump($data);
    file_put_contents("OrderStatusSF/$idPurchase.json",$response);
	/*$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	// $extra = 'index.php';
	// header("Location: http://$host$uri/$extra");
	header("Location: http://$host$uri/");*/
	exit;
}
elseif (!Empty($_GET["sendStatusCommentID"])) {
	$id = (Empty($_GET["id"])) ? die("id es obligatorio") : $_GET["id"];
	$externalId = (Empty($_GET["externalId"])) ? die("externalId es obligatorio") : $_GET["externalId"];
    $currentStatusName = $_GET["currentStatusName"] ?? "InProgress";
    $requestedStatusName = $_GET["requestedStatusName"] ?? "InProgress";
	$salesforce = new Sales_Force;
	$response = $salesforce->sendStatusCommentID($id, $externalId, $currentStatusName, $requestedStatusName);
	$data = json_decode($response, true);
	
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	// $extra = 'index.php';
	// header("Location: http://$host$uri/$extra");
	header("Location: http://$host$uri/");
	exit;
}