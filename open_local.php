<?php
if (Empty($_GET["repo"]))
{
    die("repo es obligatorio");
}
$REPO_NAME = $_GET["repo"];
$branch = $_GET["branch"];

$path = substr(shell_exec('echo %USERPROFILE%'), 0, -1)."\\Documents\\Programas\\eCommerce\\".$REPO_NAME;
echo "<pre>$path</pre>";
if (file_exists("$path")) {
    echo "<pre>El repository $REPO_NAME existe</pre>";
} else {
    echo "<pre>El repository $REPO_NAME no existe</pre>";
}
$salida = shell_exec("open_local.bat $REPO_NAME $branch");
    echo "<pre>$salida</pre>";
    echo "<a href='index.php'>fin</a>";

			
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    // $extra = 'index.php';
    // header("Location: http://$host$uri/$extra");
    header("Location: http://$host$uri/");