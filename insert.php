<?php
	$json = file_get_contents('php://input');
	if (!empty($json))
	{
		// Get the JSON contents
		$json = file_get_contents('php://input');
		
		// decode the json data
		$data = json_decode($json);
		
		$name = $data->{"name"} ?? die("campo nombre es obligatorio");
		$description = $data->{"description"} ?? "";
		$type = $data->{"type"} ?? die("campo tipo es obligatorio");
		$dev = $data->{"dev"} ?? "";
		$test = $data->{"test"} ?? "";
		$prod = $data->{"prod"} ?? "";
		$status = $data->{"status"} ?? 0;

		include("coneccion.php");

		$mysqli->real_query("INSERT INTO repositorios (name, description, type, dev, test, prod, status) VALUES ('".$name."', '".$description."' ,".$type.", '".$dev."', '".$test."', '".$prod."', ".$status.")");

		include("read_repos.php");
		$mysqli->close();
	}
	else // $_POST is empty.
	{
		echo "Perform code for page without POST data. ";
	}