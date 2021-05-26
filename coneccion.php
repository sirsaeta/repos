<?php
	$hostname="localhost";
	$username="root";
	$password="";
	$dbname="basic-crud";
	$mysqli = new mysqli($hostname,$username, $password, $dbname);

	/* comprueba la conexión */
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
    if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
    }
	//$mysqli->select_db("world"); //en caso de querer cambiar de base de datos
	