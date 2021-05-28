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

	function updateCommitAndTag($until,$repoName,$lastCommit,$tag) {
		$response["status"]=true;
		$response["message"]="";
		include("coneccion.php");
		
		$query_string = "UPDATE repositorios SET "
		.$until."_last_commit='".$lastCommit."', "
		.$until."='".$tag."' WHERE name='".$repoName."'";
		if (!$mysqli->query($query_string)) {
			error_log("Errormessage: %s\n", $mysqli->error);
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