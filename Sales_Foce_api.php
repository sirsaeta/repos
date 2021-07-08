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
			"Cookie: BrowserId=6qKoFWJWEeuYCZGv0C6kSw",
			"Authorization: ".$this->getAuthorization()
		);
		$url = "https://telecomcrm.my.salesforce.com/services/apexrest/tmf-api/productOrderingManagement/v4/productOrder/?deepLevel=2&excludeNulls=false&externalId=".urlencode($idPurchase);
		return $this->cUrlGet($url,null,$headers);
	}

	public function GetOrderByID(string $idPurchase)
	{
		$dataOrders = file_get_contents("DB/productOrders.json");
		$orders = json_decode($dataOrders, true);
		foreach ($orders as $key => $value) {
			if ($value['externalId']==$idPurchase || $value['externalId']==substr($idPurchase,0,24)) {
				file_put_contents("Order/$idPurchase.json",json_encode($value));
				print_r(json_encode($value['originalSubmitOrderRequest'], JSON_UNESCAPED_UNICODE));
			}
		}
	}

	public function GetPurchaseByID(string $idPurchase)
	{
		$data = array();
		$payload = json_encode($data);
		$headers = array();
		$url = "https://xi5edmsqa2.execute-api.us-east-1.amazonaws.com/v1/purchase/$idPurchase";

		return $this->cUrlPost($url, $payload,$headers);
	}

	public function GetStockByProductCode(string $productCode)
	{
		$headers = array(
			"Host: api.store.personal.com.ar",
			"User-Agent: PostmanRuntime/7.28.1",
			"Postman-Token: 05a75f3d-ce8e-4ae7-b139-35a35539e807"
		);
		return $this->cUrlGet("https://api.store.personal.com.ar/stock/$productCode",null,$headers);
	}

	function sendStatusCommentID(string $id, string $externalId, $currentStatusName, $requestedStatusName)
	{
		$data = array(
			"TecoOrderJson__c" => "{\"id\":\"$id\",\"externalId\":\"$externalId\",\"currentStatus\": {\"name\":\"$currentStatusName\"},\"requestedStatus\": {\"name\":\"$requestedStatusName\"}}"
		);
		$payload = json_encode($data);
		$headers = array(
			"Cookie: BrowserId=6qKoFWJWEeuYCZGv0C6kSw",
			"Authorization: ".$this->getAuthorization()
		);
		$url = "https://telecomcrm.my.salesforce.com/services/data/v48.0/sobjects/TecoOrderEvent__e";

		return $this->cUrlPost($url, $payload,$headers);
	}

	private function cUrlPost($url, $payload, $headers_add=array(), $status = null)
	{
		$headers = array(
			"Content-Type: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache"
		);
		foreach ($headers_add as $key => $value) {
			array_push($headers,$value);
		}
		//var_dump($payload);
		
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
			"Content-Type: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache"
		);
		foreach ($headers_add as $key => $value) {
			array_push($headers,$value);
		}
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
	$isCompleted = false;
	$isInProgress = false;
	$isDespachado = false;
	$salesforce = new Sales_Force;
	$response = $salesforce->getOrderStatus($idPurchase);
	$data = json_decode($response, true);
	//$data = $data[1];
	//echo "<br>".$data['id'];
	//var_dump($data);
    file_put_contents("OrderStatusSF/$idPurchase.json",$response);
	/*$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	// $extra = 'index.php';
	// header("Location: http://$host$uri/$extra");
	header("Location: http://$host$uri/");*/
	//https://xi5edmsqa2.execute-api.us-east-1.amazonaws.com/v1/purchase/:id
	if (str_contains(substr($idPurchase, -4),"-v")) {
		$responsePurchase = $salesforce->GetPurchaseByID(substr($idPurchase,0,24));
	}
	else {
		$responsePurchase = $salesforce->GetPurchaseByID($idPurchase);
	}
	$dataPurchase = json_decode($responsePurchase, true);
    file_put_contents("Purchases/$idPurchase.json",$responsePurchase);
	$dataStock = null;
	if ($responsePurchase) {
		$responseStock = $salesforce->GetStockByProductCode($dataPurchase['products'][0]['offering']['productCode']);
		$dataStock = json_decode($responseStock, true);
		file_put_contents("Stock/".$dataPurchase['products'][0]['offering']['productCode'].".json",$responseStock);
	}
	
	//echo "<br>".$data['id'];
	//var_dump($data);
	//var_dump($dataPurchase);
	//var_dump($responseStock);
	echo "<table border=1 style='width: -webkit-fill-available;'>";
	echo "<tr>";
	echo "<th colspan=4>"."FAN"."</th>";
	echo "<th colspan=7>"."PURCHASE"."</th>";
	echo "<th rowspan=2>"."ACCION"."</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>"."id"."</td>";
	echo "<td>"."requestedStatus"."</td>";
	echo "<td>"."currentStatus"."</td>";
	echo "<td>"."TrackingStatus"."</td>";
	echo "<td>"."id"."</td>";
	echo "<td>"."version"."</td>";
	echo "<td>"."documentNumber"."</td>";
	echo "<td>"."productCode"."</td>";
	echo "<td>"."slug"."</td>";
	echo "<td>"."Stock"."</td>";
	echo "<td>"."status"."</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>".$data['id']."</td>";
	echo "<td>".$data['requestedStatus']['name']."</td>";
	echo "<td>".$data['currentStatus']['name']."</td>";
	echo "<td>".$data['vlOrder']['TrackingStatus__c']."</td>";
	echo "<td>".$dataPurchase['id']."</td>";
	echo "<td>".$dataPurchase['version']."</td>";
	echo "<td>".$dataPurchase['documentNumber']."</td>";
	if (!Empty($dataPurchase['products'][0]['offering']['productSpecification'])) {
		echo "<td>".$dataPurchase['products'][0]['offering']['productCode']."</td>";
		echo "<td>".$dataPurchase['products'][0]['offering']['slug']."</td>";
	}
	elseif (!Empty($dataPurchase['products'][0]['offering']['products'])) {
		echo "<td>".$dataPurchase['products'][0]['offering']['productCode']."</td>";
		echo "<td>".$dataPurchase['products'][0]['offering']['slug']."</td>";
	}
	else {
		echo "<td></td>";
		echo "<td></td>";
	}
	if($dataStock) 
	echo "<td>available:".$dataStock['stock']['productInventory']["available"]."<br><br>recognized:".
	$dataStock['stock']['productInventory']["recognized"]."<br><br>reserved:".
	$dataStock['stock']['productInventory']["reserved"]."</td>";
	else echo "<td></td>";
	echo "<td>";
		//print_r(json_encode($dataPurchase['status']));
		foreach ($dataPurchase['status'] as $key => $value) {
			$subStatus = !Empty($value["substatus"]) ? $value['substatus'] : '';
			echo "date: ".$value['date']."<br>";
			echo "status: ".$value['status']."<br>";
			echo "substatus: ".($subStatus)."<br><br>";
			if ($subStatus=="CRM - Completed") {
				$isCompleted=true;
			}
			elseif ($subStatus=="CRM - Despachado") {
				$isDespachado=true;
			}
			elseif ($subStatus=="CRM - InProgress") {
				$isInProgress=true;
			}
		}
	echo"</td>";
	echo "<td>";
	if (!Empty($data["id"])) {
		if (!$isInProgress && !$isDespachado && !$isCompleted) {
			echo "<br><a href='http://localhost/repos/Sales_Foce_api.php?sendStatusCommentID=true&externalId=".urlencode($idPurchase)."&id=".$data["id"]."'>Enviar InProgress</a>";
		}
		if (!$isDespachado && !$isCompleted) {
			echo "<br><br><a href='http://localhost/repos/Sales_Foce_api.php?sendStatusCommentID=true&externalId=".urlencode($idPurchase)."&id=".$data["id"]."&currentStatusName=Despachado&requestedStatusName=Despachado'>Enviar Despachado</a>";
		}
		if (!$isCompleted && $data['vlOrder']['TrackingStatus__c']=="Entregado" && $data['currentStatus']['name']=="Completed" && $data['requestedStatus']['name']=="Completed") {
			echo "<br><br><a href='http://localhost/repos/Sales_Foce_api.php?sendStatusCommentID=true&externalId=".urlencode($idPurchase)."&id=".$data["id"]."&currentStatusName=Completed&requestedStatusName=Completed'>Enviar Completed</a>";
		}
	}
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "<br><br><a href='http://localhost/repos/Sales_Foce_api.php?getOrderStatus=true&idPurchase=".urlencode($dataPurchase['id']."-v".$dataPurchase['version'])."'>Actualizar</a><br><br>";
	echo $dataPurchase['id']."-v".$dataPurchase['version']."<br><br>";
	echo "db.purchase.find({id:\"".$dataPurchase['id']."\"}).pretty()<br><br>";
	echo "db.purchase.update({id:\"".$dataPurchase['id']."\"},{\$set: {legacyId:\"".$dataPurchase['id']."-v".$dataPurchase['version']."\"}})<br><br>";
	$salesforce->GetOrderByID($idPurchase);
	exit;
}
elseif (!Empty($_GET["sendStatusCommentID"])) {
	$id = (Empty($_GET["id"])) ? die("id es obligatorio") : $_GET["id"];
	$externalId = (Empty($_GET["externalId"])) ? die("externalId es obligatorio") : $_GET["externalId"];
    $currentStatusName = $_GET["currentStatusName"] ?? "InProgress";
    $requestedStatusName = $_GET["requestedStatusName"] ?? "InProgress";
	$salesforce = new Sales_Force;
	$response = $salesforce->sendStatusCommentID($id, $externalId, $currentStatusName, $requestedStatusName);
	//$data = json_decode($response, true);
	//var_dump($data);
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'Sales_Foce_api.php?getOrderStatus=true&idPurchase='.urlencode($externalId);
	header("Location: http://$host$uri/$extra");
	//header("Location: http://$host$uri/");
	exit;
}
elseif (!Empty($_GET["getOrderByID"])) {
	$id = (Empty($_GET["id"])) ? die("id es obligatorio") : $_GET["id"];
	$salesforce = new Sales_Force;
	$salesforce->GetOrderByID($id);
	//$data = json_decode($response, true);
	//var_dump($data);
	exit;
}