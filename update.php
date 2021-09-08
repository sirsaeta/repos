<?php
class UpdateRepo {
	private $id;
	private $name;
	private $description;
	private $type;
	private $dev;
	private $test;
	private $prod;
	private $status;

	function validCampos($data) {
		$response["status"]=true;
		$response["message"]="";
		$this->id = $data->{"id"} ?? "";
		$this->name = $data->{"name"} ?? "";
		$this->type = $data->{"type"} ?? "";
		$this->description = $data->{"description"} ?? "";
		$this->dev = $data->{"dev"} ?? "";
		$this->test = $data->{"test"} ?? "";
		$this->prod = $data->{"prod"} ?? "";
		$this->status = $data->{"status"} ?? 0;
		if ($this->id=="") {
			$response["status"]=false;
			$response["message"]="Campo id es obligatorio";
		}
		elseif ($this->name=="") {
			$response["status"]=false;
			$response["message"]="Campo name es obligatorio";
		}
		elseif ($this->type=="") {
			$response["status"]=false;
			$response["message"]="Campo type es obligatorio";
		}
		return json_encode($response);
	}

	function updateAllCampos() {
		$response["status"]=true;
		$response["message"]="";
		include("coneccion.php");

		$query_string = "UPDATE repositorios SET name='".$this->name."', description='".$this->description."', type=".$this->type.", develop='".$this->dev."', staging='".$this->test."', master='".$this->prod."', status=".$this->status." WHERE id=".$this->id;
		if (!$mysqli->query($query_string)) {
			error_log("Errormessage: %s\n", $mysqli->error);
			$response["status"]=false;
			$response["message"]=$mysqli->error;
		}
		$mysqli->close();
		return $response;
	}

	function updateCommitAndTagAndPr($until,$repoName,$lastCommit,$tag,$pr) {
		$response["status"]=true;
		$response["message"]="";
		include("coneccion.php");
		
		$query_string = "UPDATE repositorios SET "
		.$until."_last_commit='".$lastCommit."', "
		.$until."='".$tag."' WHERE name='".$repoName."'";
		if (!$mysqli->query($query_string)) {
			error_log("query_string: ".$query_string);
			error_log("Errormessage: %s\n", $mysqli->error);
			$response["status"]=false;
			$response["message"]=$mysqli->error;
		}

		$query_string = "UPDATE repository_x_environment t1 
		INNER JOIN environment t2
		ON t1.id_environment = t2.id AND t2.name='".$until."'
		INNER JOIN repositorios t3
		ON t1.id_repository = t3.id AND t3.name='".$repoName."'
		SET 
		t1.last_commit='".$lastCommit."', t1.tag='".$tag."', t1.pr_pending=".$pr."
		";
		if (!$mysqli->query($query_string)) {
			error_log("Errormessage: ");
			error_log($mysqli->error);
			var_dump($mysqli->error);
			$response["status"]=false;
			$response["message"]=$mysqli->error;
		}
		
		$mysqli->close();
		return $response;
	}
}
	/*$json = file_get_contents('php://input');
	if (!empty($json))
	{
		// Get the JSON contents
		$json = file_get_contents('php://input');
		
		// decode the json data
		$data = json_decode($json);
		$update = new Update;
		$valid = $update->validCampos($data);
		if (!$valid["status"]) {
			die($valid["message"]);
		}
		
		$valid = $update->updateAllCampos();
		if (!$valid["status"]) {
			die($valid["message"]);
		}
	}
	else // $_POST is empty.
	{
		echo "Perform code for page without POST data. ";
	}*/