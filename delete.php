<?php
	$json = file_get_contents('php://input');
	if (!empty($json))
	{
		// Get the JSON contents
		$json = file_get_contents('php://input');
		
		// decode the json data
		$data = json_decode($json);
		
		$id = $data->{"id"} ?? die("campo id es obligatorio");
		
		require("coneccion.php");

		if (!$mysqli->real_query("DELETE FROM repositorios WHERE id=".$id)) {
			printf("Errormessage: %s\n", $mysqli->error);
		}
		include("read_repos.php");
		$mysqli->close();
	}
	else // $_POST is empty.
	{
		echo "Perform code for page without POST data. ";
	}