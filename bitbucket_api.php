<?php

include("update.php");

class Bitbucket {
	private $authorization;

	public function __construct() {
		$data = file_get_contents("token_bitbucket.json");
		$token_bitbucket = json_decode($data, true);
        $this->authorization = "Bearer ".$token_bitbucket['token'];
    }

	private function getAuthorization() {
		return $this->authorization;
	}

	function getRepositorys($project="CBFF") {
		return $this->cUrlGet("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/".$project."/repos");
	}

	function getTagsForOneRepository($repo,$project="CBFF",$limit=5) {
		return $this->cUrlGet("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/".$project."/repos/".$repo."/tags/?limit=".$limit);
	}
	
	function getCommitsForOneRepository($repo,$project="CBFF",$until="develop",$limit=5) {
		return $this->cUrlGet("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/".$project."/repos/".$repo."/commits/?until=".$until."&limit=".$limit);
	}

	function DeleteBranch(string $REPO_NAME = null, $BRANCH_NAME, $project="CBFF")
	{
		$data = array("name" => $BRANCH_NAME);
		$payload = json_encode($data);

		return $this->cUrlDelete("https://bitbucket.telecom.com.ar/rest/branch-utils/latest/projects/$project/repos/$REPO_NAME/branches", $payload);
	}

	function CreatePR(string $REPO_NAME = null, $PR_TITLE, $FROM_BRANCH, $TO_BRANCH, $project="CBFF")
	{
		$data = array(
			"title" => $PR_TITLE,
			"description" => "",
			"state" => "OPEN",
			"open" => true,
			"closed" => false,
			"fromRef" => array(
				"id" => "refs/heads/$FROM_BRANCH",
				"repository" => array(
					"slug" => "$REPO_NAME",
					"name" => null,
					"project" => array(
						"key" => $project
					)
				)
			),
			"toRef" => array(
				"id" => "refs/heads/$TO_BRANCH",
				"repository" => array(
					"slug" => "$REPO_NAME",
					"name" => null,
					"project" => array(
						"key" => $project
					)
				)
			),
			"locked" => false,
			"reviewers" => [],
			"links" => array(
				"self" => [
					null
				]
			)
		);
		$payload = json_encode($data);

		return $this->cUrlPost("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/CBFF/repos/".$REPO_NAME."/pull-requests", $payload);
	}

	function GetPRByID($REPO_NAME,$PR_ID)
	{
		return $this->cUrlGet("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/CBFF/repos/".$REPO_NAME."/pull-requests/".$PR_ID."/merge");
	}

	function GetPR($REPO_NAME)
	{
		return $this->cUrlGet("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/CBFF/repos/$REPO_NAME/pull-requests/");
	}

	function MergePR($REPO_NAME,$PR_ID)
	{
		$headers = array(
			"X-Atlassian-Token: no-check"
		);
		return $this->cUrlPost("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/CBFF/repos/".$REPO_NAME."/pull-requests/".$PR_ID."/merge?version=0",false,$headers);
	}

	function verify($repo, $project, $until, $limit, $tags) {
		$update = new UpdateRepo;
		$response = $this->getCommitsForOneRepository($repo, $project, $until, $limit);
		$data = json_decode($response, true);
		$responsePR = $this->GetPR($repo);
		$dataPR = json_decode($responsePR, true);
		$prSize = 0;
		if (($dataPR ? $dataPR["size"] : 0)>0) {
			foreach ($dataPR["values"] as $keyPr => $valuePr) {
				if ($valuePr["toRef"]["displayId"]==$until) {
					$prSize++;
				}
			}
		}
		
		$break = false;
		foreach ($data["values"] as $key => $value) {
			if ($tags["size"]>0) {
				foreach ($tags["values"] as $keyTag => $valueTag) {
					if ($value["id"]==$valueTag["latestCommit"]) {
						$update->updateCommitAndTagAndPr($until,$repo,$value["id"],$valueTag["displayId"],$prSize);
						$break=true;
						break;
					}
				}
			} else {
				$update->updateCommitAndTagAndPr($until,$repo,$value["id"],"N/A",$prSize);
				$break=true;
			}
			
			if ($break) {
				break;
			}
		}
		unset($update);
	}

	private function cUrlPost($url, $payload, $headers_add=array(), $status = null)
	{
		$headers = array(
			"Content-Type: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Authorization: ".$this->getAuthorization()
		);
		foreach ($headers_add as $key => $value) {
			array_push($headers,$value);
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		if ($payload) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$respuesta = explode("\n\r\n", $head);
		$headers = $respuesta[0];
		$body = $respuesta[1];

		//var_dump($body);
		
		if(!$head)
		{
			return FALSE;
		}
		
		if($status === null)
		{
			if($httpCode < 400)
			{
				return $body;
			}
			else
			{
				return FALSE;
			}
		}
		elseif($status == $httpCode)
		{
			return $body;
		}

		return FALSE;
	}

	private function cUrlGet($url, $status = null)
	{
		$headers = array(
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Authorization: ".$this->getAuthorization()
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$respuesta = explode("\n\r\n", $head);
		$headers = $respuesta[0];
		$body = $respuesta[1];

		//var_dump($body);
		
		if(!$head)
		{
			return FALSE;
		}
		
		if($status === null)
		{
			if($httpCode < 400)
			{
				return $body;
			}
			else
			{
				return FALSE;
			}
		}
		elseif($status == $httpCode)
		{
			return $body;
		}

		return FALSE;
	}

	private function cUrlDelete($url, $payload, $headers_add=array(), $status = null)
	{
		$headers = array(
			"Content-Type: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Authorization: ".$this->getAuthorization()
		);
		foreach ($headers_add as $key => $value) {
			array_push($headers,$value);
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		if ($payload) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$respuesta = explode("\n\r\n", $head);
		$headers = $respuesta[0];
		$body = $respuesta[1];

		//var_dump($body);
		
		if(!$head)
		{
			return FALSE;
		}
		
		if($status === null)
		{
			if($httpCode < 400)
			{
				return $body;
			}
			else
			{
				return FALSE;
			}
		}
		elseif($status == $httpCode)
		{
			return $body;
		}

		return FALSE;
	}
}

if (!Empty($_GET["commit"])) {
	$repo = (Empty($_GET["repo"])) ? die("Repo es obligatorio") : $_GET["repo"];
	$limit = $_GET["limit"] ?? 1;
	$until = $_GET["until"] ?? "develop";
	$project = $_GET["project"] ?? "CBFF";
	$bitbucket = new Bitbucket;
	$response = $bitbucket->getCommitsForOneRepository($repo, $project, $until, $limit);
	$data = json_decode($response, true);
	include("coneccion.php");

	$query_string = "UPDATE repositorios SET ".$until."_last_commit='".$data["values"][0]["displayId"]."' WHERE name='".$repo."'";
	if (!$mysqli->query($query_string)) {
		printf("Errormessage: %s\n", $mysqli->error);
		echo 'Houston we have a problem '.mysqli_error($mysqli);
	}
	$mysqli->close();
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	// $extra = 'index.php';
	// header("Location: http://$host$uri/$extra");
	header("Location: http://$host$uri/");
	exit;
}
elseif (!Empty($_GET["commits"])) {
	$repo = (Empty($_GET["repo"])) ? die("Repo es obligatorio") : $_GET["repo"];
	$limit = $_GET["limit"] ?? 1;
	$until = "develop";
	$project = $_GET["project"] ?? "CBFF";
	$bitbucket = new Bitbucket;
	$response = $bitbucket->getCommitsForOneRepository($repo, $project, $until, $limit);
	$data = json_decode($response, true);
	include("coneccion.php");

	$query_string = "UPDATE repositorios SET ".$until."_last_commit='".$data["values"][0]["displayId"]."' WHERE name='".$repo."'";
	if (!$mysqli->query($query_string)) {
		printf("Errormessage: %s\n", $mysqli->error);
		echo 'Houston we have a problem '.mysqli_error($mysqli);
	}
	$until = "staging";
	$response = $bitbucket->getCommitsForOneRepository($repo, $project, $until, $limit);
	$data = json_decode($response, true);

	$query_string = "UPDATE repositorios SET ".$until."_last_commit='".$data["values"][0]["displayId"]."' WHERE name='".$repo."'";
	if (!$mysqli->query($query_string)) {
		printf("Errormessage: %s\n", $mysqli->error);
		echo 'Houston we have a problem '.mysqli_error($mysqli);
	}

	$until = "master";
	$response = $bitbucket->getCommitsForOneRepository($repo, $project, $until, $limit);
	$data = json_decode($response, true);

	$query_string = "UPDATE repositorios SET ".$until."_last_commit='".$data["values"][0]["displayId"]."' WHERE name='".$repo."'";
	if (!$mysqli->query($query_string)) {
		printf("Errormessage: %s\n", $mysqli->error);
		echo 'Houston we have a problem '.mysqli_error($mysqli);
	}

	$mysqli->close();
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	// $extra = 'index.php';
	// header("Location: http://$host$uri/$extra");
	header("Location: http://$host$uri/");
	exit;
}
elseif (!Empty($_GET["all"])) {
	$repo = (Empty($_GET["repo"])) ? die("Repo es obligatorio") : $_GET["repo"];
	$limit = $_GET["limit"] ?? 3;
	$project = $_GET["project"] ?? "CBFF";
	$bitbucket = new Bitbucket;

	
	$response = $bitbucket->getTagsForOneRepository($repo, $project, 55);
	$tags = json_decode($response, true);

	$until = "develop";
	$bitbucket->verify($repo, $project, $until, $limit, $tags);

	$until = "staging";
	$bitbucket->verify($repo, $project, $until, $limit, $tags);

	$until = "master";
	$bitbucket->verify($repo, $project, $until, $limit, $tags);

	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	// $extra = 'index.php';
	// header("Location: http://$host$uri/$extra");
	header("Location: http://$host$uri/");
	exit;
}
elseif (!Empty($_GET["stm"])) {
	$repo = (Empty($_GET["repo"])) ? die("Repo es obligatorio") : $_GET["repo"];
	$project = $_GET["project"] ?? "CBFF";
	$bitbucket = new Bitbucket;

	$body_pr = $bitbucket->CreatePR($repo, "CBFF-000: Staging to Master", "staging", "master", $project);
	$pull_requests = json_decode($body_pr, true);

	$body_get_merge = $bitbucket->GetPRByID($repo, $pull_requests["id"]);
	$merge = json_decode($body_get_merge, true);
	
	if ($merge)
	{
		if ($merge["canMerge"]) {
			$body_merge_pr = $bitbucket->MergePR($repo, $pull_requests["id"]);
			$merge_pr = json_decode($body_merge_pr, true);
			
			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			// $extra = 'index.php';
			// header("Location: http://$host$uri/$extra");
			header("Location: http://$host$uri/");
		}
		else {
			echo "====================================== [ERROR 2] ==============================================";
			echo "CANNOT MERGE THE PR #".$pull_requests['id']." FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: ";
			echo "https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview";
			echo "=============================================================================================";
		}
	}
	else {
		echo "====================================== [ERROR] ==============================================";
		echo "CANNOT MERGE THE PR #".$pull_requests['id']." FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: ";
		echo "https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview";
		echo "=============================================================================================";
	}
	exit;
}