<?php
	# Check If Record Exists
	echo "<html><body style='background-color:#E5E8E8'><table border=1>";
	echo "<tr>
		<th rowspan=2>Id</th>
		<th rowspan=2>Repositorio</th>
		<th colspan=3>Versión</th>
		<th rowspan=2>Tipo</th>
		<th rowspan=2 colspan=3>Acciones</th>
		<th rowspan=2>Fecha Verificación</th>
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
				<td style='background-color:#85C1E9'>".$obj->develop."</td>
				<td style='background-color:".(explode("-", $obj->develop)[0]==explode("-", $obj->staging)[0] ? '#48C9B0' : '#EC7063')."'>".$obj->staging."</td>
				<td style='background-color:".(explode("-", $obj->master)[0]==explode("-", $obj->staging)[0] ? '#48C9B0' : '#EC7063')."'>".$obj->master."</td>
				<td>".$obj->status."</td>
				<td><a href='bitbucket_api.php?all=true&repo=".$obj->name."'>Verificar</a></td>
				<td><a href='promote.php?paso=1&repo=".$obj->name."'>Develop to Staging</a></td>
				<td><a href='bitbucket_api.php?stm=true&repo=".$obj->name."'>Staging to Master</a></td>
				<td>".$obj->date_last_verify."</td>
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