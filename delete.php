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

		# Check If Record Exists
		echo "<html><body><table>
		<tr>
		<th>Id</th>
		<th>Repositorio</th>
		<th>Tipo</th>
		<th>Estado</th>
		</tr>";
		if ($result = $mysqli->query("SELECT * FROM repositorios")) {
			printf("La selección devolvió %d filas.\n", $result->num_rows);
		
			while($obj = $result->fetch_object()){
				echo "<tr>
					<td>".$obj->id."</td>
					<td>".$obj->name."</td>
					<td>".$obj->type."</td>
					<td>".$obj->status."</td>
				</tr>";
			}
			/* liberar el conjunto de resultados */
			$result->close();
			unset($obj);
		}
		echo "</table></body></html>";
		$mysqli->close();
	}
	else // $_POST is empty.
	{
		echo "Perform code for page without POST data. ";
	}