<?php
/*
Obtenermos los datos de todos los registros
*/


	//Importing database
	require_once 'login_mysql.php';

	//Creating sql query with where clause to get an specific parameter
	$sql = "SELECT * FROM wp_temperatura";

//getting result
	$r = mysqli_query($con,$sql);

	//creating a blank array
	$result = array();

	//looping through all the records fetched
	while($row = mysqli_fetch_array($r)){

		//Pushing parameter and id in the blank array created
		array_push($result,array(
			"Id"=>$row['Id'],
			"temperatura"=>$row['temperatura'],
			"Insertado"=>$row['Insertado']
		));
	}

	//Displaying the array in json format
	echo json_encode(array('result'=>$result));

	mysqli_close($con);