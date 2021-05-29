<?php
if (Empty($_GET["repo"]))
{
    die("repo es obligatorio");
}
if (Empty($_GET["paso"]))
{
    die("paso es obligatorio");
}
if (!Empty($_GET["commit"]))
{
    die("variable no permitida");
}
if (!Empty($_GET["commits"]))
{
    die("variable no permitida");
}
if (!Empty($_GET["all"]))
{
    die("variable no permitida");
}
if (!Empty($_GET["stm"]))
{
    die("variable no permitida");
}
include("bitbucket_api.php");
$REPO_NAME = $_GET["repo"];
$PASO = $_GET["paso"];

$path = substr(shell_exec('echo %USERPROFILE%'), 0, -1)."\\Documents\\Programas\\eCommerce\\".$REPO_NAME;
echo "<pre>$path</pre>";
if (file_exists("$path")) {
    echo "<pre>El repository $REPO_NAME existe</pre>";
} else {
    echo "<pre>El repository $REPO_NAME no existe</pre>";
}
$salida = shell_exec('test_redeploy.bat '.$REPO_NAME.' '.$PASO);
if ($PASO==1) {
    echo "<pre>$salida</pre>";
	echo "==============================================================<br>";
	echo "=                                                            =<br>";
	echo "=    abrir el pom.xml, quitar en la version el SNAPSHOT      =<br>";
	echo "=                                                            =<br>";
	echo "==============================================================<br>";
	echo "=                                                            =<br>";
	echo "=     abrir el CHANGELOG.md, colocar la referencia           =<br>";
	echo "=     de la Historia en JIRA (opcional)                      =<br>";
	echo "=     ## 1.2.0 - 2021/05/20                                  =<br>";
	echo "=     ### Redeploy                                           =<br>";
	echo "=                                                            =<br>";
	echo "==============================================================<br>";
    echo "<a href='promote.php?paso=2&repo=$REPO_NAME'>siguiente</a>";
} elseif($PASO==2) {
    echo "<pre>$salida</pre>";
	echo "==============================================================<br>";
	echo "=                                                            =<br>";
	echo "=    Hacer PR feature a develop                              =<br>";
	echo "=                                                            =<br>";
	echo "==============================================================<br>";
	echo "=                                                            =<br>";
	echo "=     Mezclar a Develop                                      =<br>";
	echo "=                                                            =<br>";
	echo "==============================================================<br>";
    $bitbucket = new Bitbucket;

	$body_pr = $bitbucket->CreatePR($REPO_NAME, "CBFF-000: Promote_Image Redeploy", "feature/CBFF-000-promote-image", "develop");
	$pull_requests = json_decode($body_pr, true);

	$body_get_merge = $bitbucket->GetPR($REPO_NAME, $pull_requests["id"]);
	$merge = json_decode($body_get_merge, true);
	
	if ($merge)
	{
		if ($merge["canMerge"]) {
			$body_merge_pr = $bitbucket->MergePR($REPO_NAME, $pull_requests["id"]);
			$merge_pr = json_decode($body_merge_pr, true);
            var_dump($merge_pr);
            echo "==============================================================<br>";
            echo "=                                                            =<br>";
            echo "=    Crear PR develop a staging                              =<br>";
            echo "= Poner en Title prefix de comentarios 'Rollout_Strategy'    =<br>";
            echo "=                                                            =<br>";
            echo "==============================================================<br>";
            echo "=                                                            =<br>";
            echo "=     Mezclar PR develop a staging                           =<br>";
            echo "=                                                            =<br>";
            echo "==============================================================<br>";
            $body_pr = $bitbucket->CreatePR($REPO_NAME, "Rollout_Strategy", "develop", "staging");
            $pull_requests = json_decode($body_pr, true);
        
            $body_get_merge = $bitbucket->GetPR($REPO_NAME, $pull_requests["id"]);
            $merge = json_decode($body_get_merge, true);
            
            if ($merge)
            {
                if ($merge["canMerge"]) {
                    $body_merge_pr = $bitbucket->MergePR($REPO_NAME, $pull_requests["id"]);
                    $merge_pr = json_decode($body_merge_pr, true);
                    var_dump($merge_pr);
                }
                else {
                    echo "====================================== [ERROR 2] ==============================================<br>";
                    echo "CANNOT MERGE THE PR #".$pull_requests['id']." FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: ";
                    echo "https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview";
                    echo "=============================================================================================<br>";
                }
            }
            else {
                echo "====================================== [ERROR] ==============================================<br>";
                echo "CANNOT MERGE THE PR #".$pull_requests['id']." FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: ";
                echo "https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview";
                echo "=============================================================================================<br>";
            }
		}
		else {
			echo "====================================== [ERROR 2] ==============================================<br>";
			echo "CANNOT MERGE THE PR #".$pull_requests['id']." FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: ";
			echo "https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview";
			echo "=============================================================================================<br>";
		}
	}
	else {
		echo "====================================== [ERROR] ==============================================<br>";
		echo "CANNOT MERGE THE PR #".$pull_requests['id']." FOR ".$repo.". PLEASE, CHECK IT OUT AND FIX: ";
		echo "https://bitbucket.telecom.com.ar/projects/CBFF/repos/".$repo."/pull-requests/".$pull_requests['id']."/overview";
		echo "=============================================================================================<br>";
	}
    echo "<a href='promote.php?paso=3&repo=$REPO_NAME'>siguiente</a><br>";
    echo "<a href='index.php'>fin</a>";
} elseif($PASO==3) {
    echo "<pre>$salida</pre>";
    echo "<a href='index.php'>fin</a>";
}