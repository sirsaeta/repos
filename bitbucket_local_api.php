<?php

class Bitbucket_Local {
	private $authorization;

	// public function __construct() {
	// 	$data = file_get_contents("token_bitbucket.json");
	// 	$token_bitbucket = json_decode($data, true);
    //     $this->authorization = "Bearer ".$token_bitbucket['token'];
    // }

	// private function getAuthorization() {
	// 	return $this->authorization;
	// }

	function getRepositorys($project="CBFF", $limit=50, $start=0) {
        include("coneccion.php");
		$query_read = "SELECT t1.*,t2.name as 'name_repo_type', 
        t3.tag as tag_develop, t3.pr_pending as pr_develop, 
        t4.tag as tag_testing, t4.pr_pending as pr_testing, 
        t5.tag as tag_master, t5.pr_pending as 'pr_master', 
        t6.tag as tag_staging, t6.pr_pending as 'pr_staging'  
        FROM repositorios t1 
        INNER JOIN repository_type t2 ON t1.type=t2.id
        INNER JOIN repository_x_environment t3 ON t1.id=t3.id_repository AND t3.id_environment=1
        INNER JOIN repository_x_environment t4 ON t1.id=t4.id_repository AND t4.id_environment=2
        INNER JOIN repository_x_environment t5 ON t1.id=t5.id_repository AND t5.id_environment=3
        INNER JOIN repository_x_environment t6 ON t1.id=t6.id_repository AND t6.id_environment=4
        WHERE t1.status=1";
        if ($result = $mysqli->query($query_read)) {
            $repos = array();
            while($obj = $result->fetch_object()){
                array_push($repos,array(
                    'id' => $obj->id,
                    'name' => $obj->name,
                    'idtype' => $obj->type,
                    'type' => $obj->name_repo_type,
                    'pr_develop' => $obj->pr_develop,
                    'tag_develop' => $obj->tag_develop,
                    'tag_staging' => $obj->tag_staging,
                    'pr_staging' => $obj->pr_staging,
                    'tag_master' => $obj->tag_master,
                    'pr_master' => $obj->pr_master,
                    'tag_testing' => $obj->tag_testing,
                    'pr_testing' => $obj->pr_testing,
                    'status' => $obj->status,
                ));
            }
            /* liberar el conjunto de resultados */
            $result->close();
            unset($obj);
            return json_encode($repos, true);
        }
        else {
            $response = "Errormessage: ".$mysqli->error;
            return json_encode($response, true);
        }
	}

    public function CleanHref($origen = 1, $mysqli=false) {
        if (!$mysqli) {
            include("coneccion.php");
        }
		$query_string = "UPDATE repositorios SET href=''
		WHERE origen=".$origen;
		if (!($mysqli->query($query_string) === TRUE)) {
			printf("Errormessage: %s\n", $mysqli->error);
			echo 'Houston we have a problem '.mysqli_error($mysqli);
		}

    }

	function getEnvironments($mysqli=false) {
        if (!$mysqli) {
            include("coneccion.php");
        }
		$query_read = "SELECT *  
        FROM environment
        WHERE status=1";
        if ($result = $mysqli->query($query_read)) {
            $envs = array();
            while($obj = $result->fetch_object()){
                array_push($envs,array(
                    'id' => $obj->id,
                    'name' => $obj->name,
                    'status' => $obj->status
                ));
            }
            /* liberar el conjunto de resultados */
            $result->close();
            unset($obj);
            return json_encode($envs, true);
        }
        else {
            $response = "Errormessage: ".$mysqli->error;
            return json_encode($response, true);
        }
	}

	function createRepoXEnvironments($id_repository) {
        include("coneccion.php");
		$query_read = "SELECT id 
        FROM environment
        WHERE status=1";
        $enviromentsResponse = $this->getEnvironments();
        $enviromentsData = json_decode($enviromentsResponse);
        if ($enviromentsData) {
            foreach ($enviromentsData as $key => $value) {
                $query_string = "INSERT INTO `repository_x_environment`(`id_repository`, `id_environment`, `status`) VALUES 
                (".$id_repository.",".$value['id'].",1)";
                if (!$mysqli->query($query_string)) {
                    printf("query_string: %s\n", $query_string);
                    printf("Errormessage: %s\n", $mysqli->error);
                    echo 'Houston we have a problem '.mysqli_error($mysqli);
                }
            }
        }
        else {
            $response = "Errormessage: ".$mysqli->error;
            return json_encode($response, true);
        }
	}
}
if (!Empty($_GET["repos"])) {
	$project = $_GET["project"] ?? "CBFF";
	$limit = $_GET["limit"] ?? 50;
	$start = $_GET["start"] ?? 0;
	$bitbucket = new Bitbucket_Local;

	
	$response = $bitbucket->getRepositorys($project,$limit,$start);
	print_r($response);
}