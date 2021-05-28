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
		return $this->cUrlGet("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/CBFF/repos/$REPO_NAME/pull-requests", $payload)
	}

	function verify($repo, $project, $until, $limit, $tags) {
		$update = new UpdateRepo;
		$response = $this->getCommitsForOneRepository($repo, $project, $until, $limit);
		$data = json_decode($response, true);
		
		$break = false;
		foreach ($data["values"] as $key => $value) {
			foreach ($tags["values"] as $keyTag => $valueTag) {
				if ($value["id"]===$valueTag["latestCommit"]) {
					$update->updateCommitAndTag($until,$repo,$value["id"],$valueTag["displayId"]);
					$break=true;
					break;
				}
			}
			if ($break) {
				break;
			}
		}
		unset($update);
	}

	private function cUrlPost($url, $payload, $status = null)
	{
		$headers = array(
			"Content-Type: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Authorization: ".$this->getAuthorization()
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
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
				return true;
			}
			else
			{
				return FALSE;
			}
		}
		elseif($status == $httpCode)
		{
			return true;
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

	$bitbucket->CreatePR($repo, $PR_TITLE, $FROM_BRANCH, $TO_BRANCH, $project);

	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	// $extra = 'index.php';
	// header("Location: http://$host$uri/$extra");
	header("Location: http://$host$uri/");
	exit;
}