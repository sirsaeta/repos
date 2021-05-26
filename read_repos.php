<?php
	# Check If Record Exists
	echo "<html><body><table border=1>";
	echo "<tr>
		<th rowspan=2>Id</th>
		<th rowspan=2>Repositorio</th>
		<th colspan=3>Versión</th>
		<th rowspan=2>Tipo</th>
		<th rowspan=2>Estado</th>
	</tr>";
	echo "<tr>
		<th>Dev</th>
		<th>Test</th>
		<th>Prod</th>
	</tr>";
	if ($result = $mysqli->query("SELECT t1.*,t2.name as 'name_repo_type' FROM repositorios t1 INNER JOIN repository_type t2 ON t1.type=t2.id")) {
		printf("La selección devolvió %d filas.\n", $result->num_rows);
	
		while($obj = $result->fetch_object()){
			echo "<tr>
				<td>".$obj->id."</td>
				<td><a href='https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$obj->name."/commits' target='_blank'>".$obj->name."</a></td>
				<td>".$obj->dev."</td>
				<td>".$obj->test."</td>
				<td>".$obj->prod."</td>
				<td>".$obj->name_repo_type."</td>
				<td>".$obj->status."</td>
			</tr>";
		}
		/* liberar el conjunto de resultados */
		$result->close();
		unset($obj);
	}
	else {
		printf("Errormessage: %s\n", $mysqli->error);
	}
	echo "</table></body></html>";