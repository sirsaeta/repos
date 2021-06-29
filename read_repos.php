<script type="text/javascript">
    function theFunction (repo) {
        var branch = prompt("Nombre del banch", "feature/CBFF-000-refactoring");
        var ide = prompt("IDE", "idea64.exe");
		window.location.href = "open_local.php?repo=" + repo + "&branch=" + branch + "&ide=" + ide;
    }
</script>
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
	$query_read = "SELECT t1.*,t2.name as 'name_repo_type', t3.tag as tag_develop,
	t3.pr_pending as pr_develop, t4.tag as tag_staging,t4.pr_pending as pr_staging, t5.tag as tag_master,t5.pr_pending as 'pr_master' 
	FROM repositorios t1 
	INNER JOIN repository_type t2 ON t1.type=t2.id
	INNER JOIN repository_x_environment t3 ON t1.id=t3.id_repository AND t3.id_environment=1
	INNER JOIN repository_x_environment t4 ON t1.id=t4.id_repository AND t4.id_environment=2
	INNER JOIN repository_x_environment t5 ON t1.id=t5.id_repository AND t5.id_environment=3
	WHERE t1.status=1";
	/*$query_read = "SELECT t1.*,t2.name as 'name_repo_type', t3.*, t4.name as 'name_environment' 
	FROM repositorios t1 
	INNER JOIN repository_type t2 ON t1.type=t2.id
	INNER JOIN repository_x_environment t3 ON t1.id=t3.id_repository
	INNER JOIN environment t4 ON t4.id=t3.id_environment
	GROUD BY t3.id_repository";*/
	if ($result = $mysqli->query($query_read)) {
		printf("La selección devolvió %d filas.\n", $result->num_rows);
	
		while($obj = $result->fetch_object()){
			echo "<tr>
				<td>".$obj->id."</td>
				<td><a href='https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$obj->name."/commits' target='_blank'>".$obj->name."</a></td>
				<td style='background-color:".($obj->pr_develop==0 ? '#48C9B0' : '#85C1E9')."'>".$obj->tag_develop."</td>
				<td style='background-color:".(explode("-", $obj->tag_staging)[0]==explode("-", $obj->tag_develop)[0] ? '#48C9B0' : '#EC7063')."'>".$obj->tag_staging."</td>
				<td style='background-color:".(explode("-", $obj->tag_master)[0]==explode("-", $obj->tag_staging)[0] ? '#48C9B0' : '#EC7063')."'>".$obj->tag_master."</td>
				<td>".$obj->status."</td>
				<td><a href='bitbucket_api.php?all=true&repo=".$obj->name."'>Verificar</a></td>
				<td><a href='promote.php?paso=2&repo=".$obj->name."'>Develop to Staging</a></td>
				<td><a href='bitbucket_api.php?stm=true&repo=".$obj->name."'>Staging to Master</a></td>
				<td>".$obj->date_last_verify."</td>
				<td><button onclick='theFunction(\"$obj->name\")'>Open Code</button></td>
				<td><a href='promote.php?paso=4&repo=".$obj->name."'>Redeploy</a></td>
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