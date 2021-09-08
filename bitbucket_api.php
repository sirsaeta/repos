<?php

include("update.php");
include("bitbucket_local_api.php");

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

	function getRepositorys($project="CBFF", $limit=50, $start=0, $typeResponse="JSON") {
		$isLastPage=true;
		$result=array();
		while ($isLastPage) {
			$response = $this->cUrlGet("https://bitbucket.telecom.com.ar/rest/api/1.0/projects/".$project."/repos?limit=".$limit."&start=".$start."");
			$data = json_decode($response, true);
			foreach ($data['values'] as $key => $value) {
				array_push($result,$value);
			}
			$isLastPage=$data['isLastPage'];
			$start = $data['nextPageStart'];
		}
		if ($typeResponse=="JSON") {
			return json_encode($result, true);
		} else {
			return $result;
		}
	}

	function updateRepositorys($project="CBFF", $limit=50, $start=0) {
		include("coneccion.php");
		$bitbucket = new Bitbucket_Local;
		$bitbucket->CleanHref(1,$mysqli);
		$responseRepos=$bitbucket->getRepositorys($mysqli);
		$dataRepos = json_decode($responseRepos, true);
		$responseEnv=$bitbucket->getEnvironments($mysqli);
		$dataEnv = json_decode($responseEnv, true);
		$dataOrigen=$this->getRepositorys("CBFF",50,0,"ARRAY");
		$names = array_column($dataRepos, 'name');
		foreach ($dataOrigen as $key => $value) {
			$this->updateQueryRepositorys($value, 1, $mysqli);
			$found_key = array_search($value["name"], $names);
			if ($dataRepos[$found_key]["name"]===$value["name"]) {
				$this->SearchInfoByRepo($value["name"], $project, $dataEnv, $dataRepos[$found_key]["idtype"], $mysqli);
			}
		}
		$mysqli->close();
	}

	public function SearchInfoByRepo(String $repo, $project, $dataEnv, $type, $mysqli)
	{
		$response = $this->getTagsForOneRepository($repo, $project, 55);
		$tags = json_decode($response, true);
	
		$responsePR = $this->GetPR($repo);
		$dataPR = json_decode($responsePR, true);
	
		foreach ($dataEnv as $key => $value) {
			$until =  $value["name"];
			if ($type == 1 && $value["name"]=="testing") {
				$until = "staging";
			}
			elseif ($type == 1 && $value["name"]=="staging") {
				$until = "preprod";
			}
			$this->verify($repo, $project, $until, 3, $tags, $dataPR, $value["name"]);
		}
	}

	public function UpdateRepoCountPR($dataPR = null)
	{
		$prSize = 0;
		if (($dataPR ? $dataPR["size"] : 0)>0) {
			foreach ($dataPR["values"] as $keyPr => $valuePr) {
				if ($valuePr["toRef"]["displayId"]==$until) {
					$prSize++;
				}
			}
		}
		$query_string = "UPDATE repository_x_environment SET pr_pending='".$dataOrigen["name"]."',
		description='".(!Empty($dataOrigen["description"]) ? $dataOrigen["description"] : '')."',
		id_repo_origen=".$dataOrigen["id"].",
		href='".$dataOrigen["links"]["self"][0]["href"]."'
		WHERE name='".$dataOrigen['name']."' and origen=".$origen;
		if (!($mysqli->query($query_string) === TRUE)) {
			printf("Errormessage: %s\n", $mysqli->error);
			echo 'Houston we have a problem '.mysqli_error($mysqli);
		}
	}

	function updateQueryRepositorys($dataOrigen, $origen, $mysqli) {
		$query_string = "UPDATE repositorios SET name='".$dataOrigen["name"]."',
		description='".(!Empty($dataOrigen["description"]) ? $dataOrigen["description"] : '')."',
		id_repo_origen=".$dataOrigen["id"].",
		href='".$dataOrigen["links"]["self"][0]["href"]."'
		WHERE name='".$dataOrigen['name']."' and origen=".$origen;
		if (!($mysqli->query($query_string) === TRUE)) {
			printf("Errormessage: %s\n", $mysqli->error);
			echo 'Houston we have a problem '.mysqli_error($mysqli);
		}
		if ($mysqli->affected_rows===0) {
			$this->createQueryRepositorys($dataOrigen, 1, $mysqli);
		}
	}

	function createQueryRepositorys($dataOrigen, $origen, $mysqli) {
		$query_string = "INSERT INTO `repositorios`(`name`, `description`, `type`, `status`, `origen`, `id_repo_origen`, `href`) VALUES 
		('".$dataOrigen["name"]."','".(!Empty($dataOrigen["description"]) ? $dataOrigen["description"] : '')."',99,1,".$origen.",".$dataOrigen["id"].",'".$dataOrigen["links"]["self"][0]["href"]."')";
		if (!$mysqli->query($query_string)) {
			printf("Errormessage: %s\n", $mysqli->error);
			echo 'Houston we have a problem '.mysqli_error($mysqli);
		}
		$bitbucket = new Bitbucket_Local;
		$responseEnv = $bitbucket->createRepoXEnvironments($mysqli->insert_id);
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
		$url = "https://bitbucket.telecom.com.ar/rest/api/1.0/projects/CBFF/repos/".$REPO_NAME."/pull-requests/".$PR_ID."/merge?version=0";
		//echo $url;
		return $this->cUrlPost($url,false,$headers);
	}

	function verify($repo, $project, $until, $limit, $tags, $dataPR, $untilLocal) {
		$update = new UpdateRepo;
		$response = $this->getCommitsForOneRepository($repo, $project, $until, $limit);
		$data = json_decode($response, true);
		$prSize = 0;
		if (($dataPR ? $dataPR["size"] : 0)>0) {
			foreach ($dataPR["values"] as $keyPr => $valuePr) {
				if ($valuePr["toRef"]["displayId"]==$until) {
					$prSize++;
				}
			}
		}
		
		if ((!Empty($data["size"]) ? $data["size"] : 0)>0) {
			$break = false;
			foreach ($data["values"] as $key => $value) {
				if ($tags["size"]>0) {
					foreach ($tags["values"] as $keyTag => $valueTag) {
						if ($value["id"]==$valueTag["latestCommit"]) {
							$update->updateCommitAndTagAndPr($untilLocal,$repo,$value["id"],$valueTag["displayId"],$prSize);
							$break=true;
							break;
						}
					}
				} else {
					$update->updateCommitAndTagAndPr($untilLocal,$repo,$value["id"],"N/A",$prSize);
					$break=true;
				}
				
				if ($break) {
					break;
				}
			}
		}
		else {
			echo "Error no commits: repo->".$repo.", project->".$project.", until->".$until."<br>";
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
		//var_dump($respuesta);
		//echo "<br>";
		$headers = $respuesta[0];
		if (count($respuesta)==3) {
			$body = $respuesta[2];
		} else {
			$body = $respuesta[1];
		}
		

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

	$responsePR = $bitbucket->GetPR($repo);
	$dataPR = json_decode($responsePR, true);

	$until = "develop";
	$bitbucket->verify($repo, $project, $until, $limit, $tags, $dataPR);

	$until = "staging";
	$bitbucket->verify($repo, $project, $until, $limit, $tags, $dataPR);

	$until = "master";
	$bitbucket->verify($repo, $project, $until, $limit, $tags, $dataPR);

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

	$body_pr = $bitbucket->CreatePR($repo, "Rollout_Strategy", "staging", "master", $project);
	//var_dump($body_pr);
	if ($body_pr) {
		$pull_requests = json_decode($body_pr, true);
		//var_dump($pull_requests);

		$body_get_merge = $bitbucket->GetPRByID($repo, $pull_requests["id"]);
		$merge = json_decode($body_get_merge, true);
		
		if ($merge)
		{
			if ($merge["canMerge"]) {
				sleep(1);
				$body_merge_pr = $bitbucket->MergePR($repo, $pull_requests["id"]);
				$merge_pr = json_decode($body_merge_pr, true);
				
				sleep(1);
				$host  = $_SERVER['HTTP_HOST'];
				$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
				// $extra = 'index.php';
				// header("Location: http://$host$uri/$extra");
				header("Location: http://$host$uri/");
			}
			else {
				echo "====================================== [ERROR 2] ==============================================";
				echo "CANNOT MERGE THE PR #".$pull_requests['id']." FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: ";
				echo "<a target='_blank' href='https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview' >https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview</a>";
				echo "=============================================================================================";
			}
		}
		else {
			echo "====================================== [ERROR] ==============================================";
			echo "CANNOT MERGE THE PR #".$pull_requests['id']." FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: ";
			echo "<a target='_blank' href='https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview' >https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview</a>";
			echo "=============================================================================================";
		}
	}
	else {
		echo "====================================== [ERROR CREATE PR] ==============================================<br>";
		echo "CANNOT CREATE THE PR  FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: <br>";
		echo "<a href='https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests'>https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests</a><br>";
		echo "=============================================================================================<br>";
	}
	
	exit;
}
elseif (!Empty($_GET["repos"])) {
	$project = $_GET["project"] ?? "CBFF";
	$limit = $_GET["limit"] ?? 50;
	$start = $_GET["start"] ?? 0;
	$bitbucket = new Bitbucket;

	
	$response = $bitbucket->getRepositorys($project,$limit,$start);
	print_r($response);
}
elseif (!Empty($_GET["updateRepositorys"])) {
	$project = $_GET["project"] ?? "CBFF";
	$limit = $_GET["limit"] ?? 50;
	$start = $_GET["start"] ?? 0;
	$bitbucket = new Bitbucket;
	$response = $bitbucket->updateRepositorys($project,$limit,$start);
	print_r($response);
}