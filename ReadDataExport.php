<?php

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