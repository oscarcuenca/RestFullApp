<?php
/*
Obtenermos los datos de un registro
*/


	//Importing database

require_once 'login_mysql.php';


if(isset($_POST['idObjeto']) && !empty($_POST['idObjeto'])){
$idObjeto = $_POST['idObjeto'];

	//Creating sql query with where clause to get an sensor
	$sql = "SELECT * FROM wp_altitude WHERE idObjeto='$idObjeto'";

if (mysqli_connect_error()){
echo 'Error de Conexi�n: ' . mysqli_connect_error();
exit();
}

//getting result
	$r = mysqli_query($con,$sql);
if (!$r){
echo 'No se pudo hacer la consulta: ' . mysqli_error($con);
echo json_encode("Registro inexistente");
exit();
}

	//creating a blank array
	$result = array();

	//looping through all the records fetched
	while($row = mysqli_fetch_array($r)){

		//Pushing name and id in the blank array created
		array_push($result,array(
			"Id"=>$row['Id'],
			"altitud"=>$row['altitud'],
			"Insertado"=>$row['Insertado']
		));
	}

	//Displaying the array in json format
	echo json_encode(array('result'=>$result));

	mysqli_close($con);

	}else{
	echo "Operacion fallida";
	}
	?>