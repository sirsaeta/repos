<?php
date_default_timezone_set("America/Argentina/Buenos_Aires");
class ReadDataExport {

	function getAll(string $file) {
		$response = file_get_contents("DB/$file.json");
        $data = json_decode($response, true);
        return $data;
	}

	function findPurchaseById(string $id)
	{
        $data = $this->getAll("purchase");
        echo "<table border=1 style='width: -webkit-fill-available;'>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>getOrderStatus</th>";
        echo "<th>documentNumber</th>";
        echo "<th>contact name</th>";
        echo "<th>codeNMU</th>";
        echo "<th>productCode</th>";
        echo "<th>slug</th>";
        echo "<th>product name</th>";
        echo "<th>status</th>";
        echo "</tr>";
        foreach ($data as $key => $value) {
            if (!Empty($value['id'])) {
                if ($value['id']==$id) {
                    //print_r($value);
                    echo "<tr>";
                    echo "<td>".$value['id']."</td>";
                    echo "<td><a href='http://localhost/repos/Sales_Foce_api.php?getOrderStatus=true&idPurchase=".urlencode($value['id'])."' target='_blank' >getOrderStatus</a></td>";
                    echo "<td>".$value['documentNumber']."</td>";
                    echo "<td>".$value['contact']['name']." ".$value['contact']['surname']."</td>";
                    if (!Empty($value['products'][0]['offering']['productSpecification'])) {
                        echo "<td>".$value['products'][0]['offering']['codeNMU']."</td>";
                        echo "<td>".$value['products'][0]['offering']['productCode']."</td>";
                        echo "<td>".$value['products'][0]['offering']['slug']."</td>";
                        echo "<td>".$value['products'][0]['offering']['productSpecification']['name']."</td>";
                    }
                    elseif (!Empty($value['products'][0]['offering']['products'])) {
                        echo "<td>".$value['products'][0]['offering']['codeNMU']."</td>";
                        echo "<td>".$value['products'][0]['offering']['productCode']."</td>";
                        echo "<td>".$value['products'][0]['offering']['slug']."</td>";
                        echo "<td>".$value['products'][0]['offering']['products'][0]['productSpecification']['name']."</td>";
                    }
                    else {
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                    }
                    echo "<td>";
                        print_r(json_encode($value['status']));
                    echo"</td>";
                    echo "</tr>";
                }
            }
        }
        echo "</table>";
	}

	function findPurchaseByProductCode(string $productCode, string $type='FULL')
	{
        $data = $this->getAll("purchase");
        echo "<table border=1 style='width: -webkit-fill-available;'>";
        echo "<tr>";
        echo "<th>#</th>";
        echo "<th>id</th>";
        echo "<th>getOrderStatus</th>";
        echo "<th>documentNumber</th>";
        echo "<th>contact name</th>";
        echo "<th>codeNMU</th>";
        echo "<th>productCode</th>";
        echo "<th>slug</th>";
        echo "<th>product name</th>";
        echo "<th>status</th>";
        echo "</tr>";
        foreach ($data as $key => $value) {
            if (!Empty($value['products'][0]['offering']['productCode'])) {
                if ($value['products'][0]['offering']['productCode']==$productCode && $value["flow"]==$type) {
                    //print_r($value);
                    echo "<tr>";
                    echo "<td>".$value['id']."</td>";
                    echo "<td>$key</td>";
                    echo "<td><a href='http://localhost/repos/Sales_Foce_api.php?getOrderStatus=true&idPurchase=".urlencode($value['id'])."' target='_blank' >getOrderStatus</a></td>";
                    echo "<td>".$value['documentNumber']."</td>";
                    echo "<td>".$value['contact']['name']." ".$value['contact']['surname']."</td>";
                    if (!Empty($value['products'][0]['offering']['productSpecification'])) {
                        echo "<td>".$value['products'][0]['offering']['codeNMU']."</td>";
                        echo "<td>".$value['products'][0]['offering']['productCode']."</td>";
                        echo "<td>".$value['products'][0]['offering']['slug']."</td>";
                        echo "<td>".$value['products'][0]['offering']['productSpecification']['name']."</td>";
                    }
                    elseif (!Empty($value['products'][0]['offering']['products'])) {
                        echo "<td>".$value['products'][0]['offering']['codeNMU']."</td>";
                        echo "<td>".$value['products'][0]['offering']['productCode']."</td>";
                        echo "<td>".$value['products'][0]['offering']['slug']."</td>";
                        echo "<td>".$value['products'][0]['offering']['products'][0]['productSpecification']['name']."</td>";
                    }
                    else {
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                    }
                    echo "<td>";
                        print_r(json_encode($value['status']));
                    echo"</td>";
                    echo "</tr>";
                }
            }
        }
        echo "</table>";
	}

	function findStockByProductCode(string $productCode)
	{
        $data = $this->getAll("productStock");
        foreach ($data as $key => $value) {
            if (!Empty($value['productCode'])) {
                if ($value['productCode']==$productCode) {
                    //print_r($value);
                    echo "<table border=1 style='width: -webkit-fill-available;'>";
                    echo "<tr>";
                    echo "<th>#</th>";
                    echo "<th>codeNMU</th>";
                    echo "<th>productCode</th>";
                    echo "<th>slug</th>";
                    echo "<th>depositCode</th>";
                    echo "<th>quantityExisting</th>";
                    echo "<th>quantityAvailable</th>";
                    echo "<th>available</th>";
                    echo "<th>reserved</th>";
                    echo "<th>recognized</th>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td>".$key."</td>";
                    echo "<td>".$value['codeNMU']."</td>";
                    echo "<td>".$value['productCode']."</td>";
                    echo "<td>".$value['slug']."</td>";
                    echo "<td>".$value['depositCode']."</td>";
                    echo "<td>".$value['listStock'][0]['quantityExisting']."</td>";
                    echo "<td>".$value['listStock'][0]['quantityAvailable']."</td>";
                    echo "<td>".$value['productInventory']['available']."</td>";
                    echo "<td>".$value['productInventory']['reserved']."</td>";
                    echo "<td>".$value['productInventory']['recognized']."</td>";
                    echo "</tr>";
                    echo "</table>";
                }
            }
        }
	}

    function GetPurchases(string $type)
    {
        $data = $this->getAll("purchase");
        echo "<table border=1 style='width: -webkit-fill-available;'>";
        echo "<tr>";
        echo "<th>#</th>";
        echo "<th>id</th>";
        echo "<th>getOrderStatus</th>";
        echo "<th>documentNumber</th>";
        echo "<th>contact name</th>";
        echo "<th>codeNMU</th>";
        echo "<th>productCode</th>";
        echo "<th>slug</th>";
        echo "<th>product name</th>";
        echo "<th>status</th>";
        echo "</tr>";
        foreach ($data as $key => $value) {
            if ($value["flow"]==$type) {
                //print_r($value);
                echo "<tr>";
                echo "<td>".$value['id']."</td>";
                echo "<td>$key</td>";
                echo "<td><a href='http://localhost/repos/Sales_Foce_api.php?getOrderStatus=true&idPurchase=".urlencode($value['id'])."' target='_blank' >getOrderStatus</a></td>";
                echo "<td>".$value['documentNumber']."</td>";
                echo "<td>".$value['contact']['name']." ".$value['contact']['surname']."</td>";
                if (!Empty($value['products'][0]['offering']['productSpecification'])) {
                    echo "<td>".$value['products'][0]['offering']['codeNMU']."</td>";
                    echo "<td>".$value['products'][0]['offering']['productCode']."</td>";
                    echo "<td>".$value['products'][0]['offering']['slug']."</td>";
                    echo "<td>".$value['products'][0]['offering']['productSpecification']['name']."</td>";
                }
                elseif (!Empty($value['products'][0]['offering']['products'])) {
                    echo "<td>".$value['products'][0]['offering']['codeNMU']."</td>";
                    echo "<td>".$value['products'][0]['offering']['productCode']."</td>";
                    echo "<td>".$value['products'][0]['offering']['slug']."</td>";
                    echo "<td>".$value['products'][0]['offering']['products'][0]['productSpecification']['name']."</td>";
                }
                else {
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "<td></td>";
                }
                echo "<td>";
                    print_r(json_encode($value['status']));
                echo"</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    function GetPurchasesReport(string $type, string $date)
    {
        $data = $this->getAll("purchase");
        echo "<table border=1 style='width: -webkit-fill-available;'>";
        echo "<tr>";
        echo "<th nowrap='nowrap'>Orden</th>";
        echo "<th nowrap='nowrap'>Fecha de Creacion</th>";
        echo "<th nowrap='nowrap'>Nombre</th>";
        echo "<th nowrap='nowrap'>Apellido</th>";
        echo "<th nowrap='nowrap'>Nro de documento</th>";
        echo "<th nowrap='nowrap'>Email</th>";
        echo "<th nowrap='nowrap'>Tel de contacto</th>";
        echo "<th nowrap='nowrap'>Tipo de envio</th>";
        echo "<th nowrap='nowrap'>Costo de envio</th>";
        echo "<th nowrap='nowrap'>Bonificacion de envio</th>";
        echo "<th nowrap='nowrap'>Provincia</th>";
        echo "<th nowrap='nowrap'>Localidad</th>";
        echo "<th nowrap='nowrap'>Calle</th>";
        echo "<th nowrap='nowrap'>Altura</th>";
        echo "<th nowrap='nowrap'>Adicional domicilio</th>";
        echo "<th nowrap='nowrap'>Codigo Postal</th>";
        echo "<th nowrap='nowrap'>Nombre autorizado</th>";
        echo "<th nowrap='nowrap'>DNI autorizado</th>";
        echo "<th nowrap='nowrap'>Medio de pago</th>";
        echo "<th nowrap='nowrap'>Tipo de tarjeta</th>";
        echo "<th nowrap='nowrap'>Total a pagar</th>";
        echo "<th nowrap='nowrap'>Cantidad de productos</th>";
        echo "<th nowrap='nowrap'>Codigo NMU</th>";
        echo "<th nowrap='nowrap'>SKU Name</th>";
        echo "<th nowrap='nowrap'>Codigo de Promo</th>";
        echo "<th nowrap='nowrap'>Promocion</th>";
        echo "<th nowrap='nowrap'>Estado</th>";
        echo "<th nowrap='nowrap'>Subestado</th>";
        echo "<th nowrap='nowrap'>Estado previo</th>";
        echo "<th nowrap='nowrap'>Ultima actualización</th>";
        echo "<th nowrap='nowrap'>Modelo de venta</th>";
        echo "</tr>";
        foreach ($data as $key => $value) {
            if ($value["flow"]==$type && substr($value['creationDate']['$date'],0,10)==$date) {
                //print_r($value);
                echo "<tr>";
                echo "<td nowrap='nowrap'>".(!Empty($_GET["legacyId"]) ? $value['legacyId'] : $value['id']."-v".$value['version'])."</td>";
                echo "<td nowrap='nowrap'>".date("Y-m-d H:i:s", strtotime($value['creationDate']['$date']))."</td>";
                echo "<td nowrap='nowrap'>".$value['contact']['name']."</td>";
                echo "<td nowrap='nowrap'>".$value['contact']['surname']."</td>";
                echo "<td nowrap='nowrap'>".$value['documentNumber']."</td>";
                echo "<td nowrap='nowrap'>".$value['contact']['email']."</td>";
                echo "<td nowrap='nowrap'>'".$value['contact']['countryCode']." ".$value['contact']['zoneCode']." ".$value['contact']['phone']."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? 'A domicilio' : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? '0' : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? '0' : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? $value['delivery']['stateOrProvince'] : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? $value['delivery']['locality'] : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? $value['delivery']['streetName'] : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? !Empty($value['delivery']['streetNumber']) ? $value['delivery']['streetNumber'] : "" : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? !Empty($value['delivery']['additionalInformation']) ? $value['delivery']['additionalInformation'] : "" : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? $value['delivery']['postCode'] : "")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? $value['delivery']['receiver'] ? $value['delivery']['receiver']['fullName']:"":"")."</td>";
                echo "<td nowrap='nowrap'>".($value['delivery'] ? $value['delivery']['receiver'] ? !Empty($value['delivery']['receiver']['documentNumber']) ? $value['delivery']['receiver']['documentNumber'] :"":"":"")."</td>";
                echo "<td nowrap='nowrap'>".($value['payment'] ? "TC":"")."</td>";
                echo "<td nowrap='nowrap'>".($value['payment'] ? $value['payment']['cardType']:"")."</td>";
                if ($value['products'][0]['offering']['type']==='Product') {
                    echo "<td nowrap='nowrap'>"."1"."</td>";
                    if ($value['flow']=='FULL') {
                        echo "<td nowrap='nowrap'>".($value['payment'] ? $value['payment']['promotion']['ptf']:"")."</td>";
                    } else {
                        echo "<td nowrap='nowrap'>".($value['products'][0]['offering']['productPrice'][0]['price']['taxIncludedChargeAmount'])."</td>";
                    }
                    echo "<td nowrap='nowrap'>".$value['products'][0]['offering']['codeNMU']."</td>";
                    echo "<td nowrap='nowrap'>".$value['products'][0]['offering']['productSpecification']['name']."</td>";
                    echo "<td nowrap='nowrap'>".""."</td>";
                    echo "<td nowrap='nowrap'>".""."</td>";
                }
                elseif ($value['products'][0]['offering']['type']==='Promotion') {
                    echo "<td nowrap='nowrap'>"."1"."</td>";
                    if ($value['flow']=='FULL') {
                        echo "<td nowrap='nowrap'>".($value['payment'] ? $value['payment']['promotion']['ptf']:"")."</td>";
                    } else {
                        echo "<td nowrap='nowrap'>".($value['products'][0]['offering']['productPrice'][0]['price']['taxIncludedChargeAmount'])."</td>";
                    }
                    echo "<td nowrap='nowrap'>".$value['products'][0]['offering']['codeNMU']."</td>";
                    echo "<td nowrap='nowrap'>".$value['products'][0]['offering']['products'][0]['productSpecification']['name']."</td>";
                    echo "<td nowrap='nowrap'>".$value['products'][0]['offering']['promotionCode']."</td>";
                    echo "<td nowrap='nowrap'>".$value['products'][0]['offering']['name']."</td>";
                }
                else {
                    echo "<td nowrap='nowrap'>"."1"."</td>";
                    if ($value['flow']=='FULL') {
                        echo "<td nowrap='nowrap'>".($value['payment'] ? $value['payment']['promotion']['ptf']:"")."</td>";
                    } else {
                        echo "<td nowrap='nowrap'>".($value['products'][0]['offering']['productPrice'][0]['price']['taxIncludedChargeAmount'])."</td>";
                    }
                    echo "<td nowrap='nowrap'></td>";
                    echo "<td nowrap='nowrap'></td>";
                    echo "<td nowrap='nowrap'></td>";
                    echo "<td nowrap='nowrap'></td>";
                }
                $status = "";
                $substatus = "";
                $ultimaActualizacion = "";
                foreach ($value['status'] as $key => $value) {
                    $status = $status.($status=="" ? "" : "<br>").(!Empty($value["status"]) ? $value['status'] : "-");
                    $substatus = $substatus.($substatus=="" ? "" : "<br>").(!Empty($value["substatus"]) ? $value['substatus'] : "-");
                    $ultimaActualizacion = $value['date']['$date'];
                }
                if ($ultimaActualizacion!="") {
                    $date = strtotime($ultimaActualizacion);
                    $ultimaActualizacion = date("Y-m-d H:i:s", $date);
                }
                echo "<td nowrap='nowrap'>".$status."</td>";
                echo "<td nowrap='nowrap'>".$substatus."</td>";
                echo "<td nowrap='nowrap'></td>";
                echo "<td nowrap='nowrap'>".$ultimaActualizacion."</td>";
                echo "<td nowrap='nowrap'>".(!Empty($value["flow"]) ? $value['flow'] : "")."</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

	function getStock()
	{
        $data = $this->getAll("productStock");
        echo "<table border=1 style='width: -webkit-fill-available;'>";
        echo "<tr>";
        echo "<th>#</th>";
        echo "<th>codeNMU</th>";
        echo "<th>productCode</th>";
        echo "<th>slug</th>";
        echo "<th>depositCode</th>";
        echo "<th>quantityExisting</th>";
        echo "<th>quantityAvailable</th>";
        echo "<th>available</th>";
        echo "<th>reserved</th>";
        echo "<th>recognized</th>";
        echo "</tr>";
        foreach ($data as $key => $value) {
            if (!Empty($value['productCode'])) {
                //print_r($value);
                echo "<tr>";
                echo "<td>".$key."</td>";
                echo "<td>".$value['codeNMU']."</td>";
                echo "<td>".$value['productCode']."</td>";
                echo "<td>".$value['slug']."</td>";
                echo "<td>".$value['depositCode']."</td>";
                echo "<td>".$value['listStock'][0]['quantityExisting']."</td>";
                echo "<td>".$value['listStock'][0]['quantityAvailable']."</td>";
                echo "<td>".$value['productInventory']['available']."</td>";
                echo "<td>".$value['productInventory']['reserved']."</td>";
                echo "<td>".$value['productInventory']['recognized']."</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
	}
    
    function GetPurchasesNoLegacy(string $type)
    {
        $data = $this->getAll("purchaseNoLegacy");
        echo "<table border=1 style='width: -webkit-fill-available;'>";
        echo "<tr>";
        echo "<th>#</th>";
        echo "<th>id</th>";
        echo "<th>find</th>";
        echo "<th>update</th>";
        echo "<th>creationDate</th>";
        echo "<th>getOrderStatus</th>";
        echo "<th>documentNumber</th>";
        echo "<th>contact name</th>";
        echo "<th>codeNMU</th>";
        echo "<th>productCode</th>";
        echo "<th>slug</th>";
        echo "<th>product name</th>";
        echo "<th>status</th>";
        echo "</tr>";
        foreach ($data as $key => $value) {
            if ($value["flow"]==$type) {
                //print_r($value);
                echo "<tr>";
                echo "<td>$key</td>";
                echo "<td>".$value['id']."</td>";
                echo "<td>db.purchase.find({id:\"".$value['id']."\"}).pretty()</td>";
	            echo "<td>db.purchase.update({id:\"".$value['id']."\"},{\$set: {legacyId:\"".$value['id']."-v".$value['version']."\"}})</td>";
                echo "<td>".$value['creationDate']['$date']."</td>";
                echo "<td><a href='http://localhost/repos/Sales_Foce_api.php?getOrderStatus=true&idPurchase=".urlencode($value['id'])."' target='_blank' >getOrderStatus</a></td>";
                echo "<td>".$value['documentNumber']."</td>";
                echo "<td>".$value['contact']['name']." ".$value['contact']['surname']."</td>";
                if (!Empty($value['products'][0]['offering']['productSpecification'])) {
                    echo "<td>".$value['products'][0]['offering']['codeNMU']."</td>";
                    echo "<td>".$value['products'][0]['offering']['productCode']."</td>";
                    echo "<td>".$value['products'][0]['offering']['slug']."</td>";
                    echo "<td>".$value['products'][0]['offering']['productSpecification']['name']."</td>";
                }
                elseif (!Empty($value['products'][0]['offering']['products'])) {
                    echo "<td>".$value['products'][0]['offering']['codeNMU']."</td>";
                    echo "<td>".$value['products'][0]['offering']['productCode']."</td>";
                    echo "<td>".$value['products'][0]['offering']['slug']."</td>";
                    echo "<td>".$value['products'][0]['offering']['products'][0]['productSpecification']['name']."</td>";
                }
                else {
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "<td></td>";
                }
                echo "<td>";
                    print_r(json_encode($value['status']));
                echo"</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }
}

if (!Empty($_GET["allData"])) {
	$file = (Empty($_GET["file"])) ? die("file es obligatorio") : $_GET["file"];
	$clase = new ReadDataExport;
	$response = $clase->getAll($file);
	//echo "<br>".$data['id'];
	var_dump($response);
	exit;
}
elseif (!Empty($_GET["findStockByProductCode"])) {
	$productCode = (Empty($_GET["productCode"])) ? die("productCode es obligatorio") : $_GET["productCode"];
	$clase = new ReadDataExport;
	$clase->findStockByProductCode($productCode);
}
elseif (!Empty($_GET["findPurchaseByProductCode"])) {
	$productCode = (Empty($_GET["productCode"])) ? die("productCode es obligatorio") : $_GET["productCode"];
    $type = $_GET["type"] ?? "FULL";
	$clase = new ReadDataExport;
	$clase->findPurchaseByProductCode($productCode,$type);
}
elseif (!Empty($_GET["findPurchaseById"])) {
	$id = (Empty($_GET["id"])) ? die("id es obligatorio") : $_GET["id"];
	$clase = new ReadDataExport;
	$clase->findPurchaseById($id);
}
elseif (!Empty($_GET["allStock"])) {
	$clase = new ReadDataExport;
	$clase->getStock();
}
elseif (!Empty($_GET["allPurchaseFull"])) {
    $type = $_GET["type"] ?? "FULL";
	$clase = new ReadDataExport;
	$clase->GetPurchases($type);
}
elseif (!Empty($_GET["allPurchaseFullNoLegacy"])) {
    $type = $_GET["type"] ?? "FULL";
	$clase = new ReadDataExport;
	$clase->GetPurchasesNoLegacy($type);
}
elseif (!Empty($_GET["purchaseReport"])) {
    $type = $_GET["type"] ?? "FULL";
    $date = $_GET["date"] ?? "2021-08-01";
	$clase = new ReadDataExport;
	$clase->GetPurchasesReport($type,$date);
}