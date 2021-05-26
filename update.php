<?php
	$json = file_get_contents('php://input');
	if (!empty($json))
	{
		// Get the JSON contents
		$json = file_get_contents('php://input');
		
		// decode the json data
		$data = json_decode($json);
		
		$id = $data->{"id"} ?? die("campo id es obligatorio");
		$name = $data->{"name"} ?? die("campo nombre es obligatorio");
		$description = $data->{"description"} ?? "";
		$type = $data->{"type"} ?? die("campo tipo es obligatorio");
		$dev = $data->{"dev"} ?? "";
		$test = $data->{"test"} ?? "";
		$prod = $data->{"prod"} ?? "";
		$status = $data->{"status"} ?? 0;
		
		include("coneccion.php");

		$query_string = "UPDATE repositorios SET name='".$name."', description='".$description."', type=".$type.", dev='".$dev."', test='".$test."', prod='".$prod."', status=".$status." WHERE id=".$id;
		if (!$mysqli->query($query_string)) {
			printf("Errormessage: %s\n", $mysqli->error);
			echo 'Houston we have a problem '.mysqli_error($mysqli);
		}
		include("read_repos.php");
		$mysqli->close();
	}
	else // $_POST is empty.
	{
		echo "Perform code for page without POST data. ";
	}