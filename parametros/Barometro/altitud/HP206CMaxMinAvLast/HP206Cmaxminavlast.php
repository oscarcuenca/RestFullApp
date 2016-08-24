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
		echo 'Error de Conexión: ' . mysqli_connect_error();
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
	$json_object = json_decode( json_encode(array('result'=>$result)) );

	$output_result = array();

	if(isset($json_object->result)){

		// get min,max and average values for pressure
		$alt_values = get_alt_values($json_object->result);

		// get pressure result set with respected values
		$alt_result = get_alt_result_set_from_values($json_object->result,$alt_values);

		// get latest entry
		$latest_entry = get_latest_date_entry($json_object->result);

		// Wrap results in an array
		$output_result_altitud = array(
			'altitude' => $alt_result,
			'last_entry' => $latest_entry
		);
	}

	// Display final result
	//echo json_encode($output_result);
        echo json_encode(array('result_altitud'=>$output_result_altitud));

	// Close mysql connection
	mysqli_close($con);

}else{
	echo "Operacion fallida";
}

// get min,max and average values for pressure
function get_alt_values($result){

	$min = -1;
	$max = -1;
	$avg = -1;

	// get all pressure values
	$altitudes = array_map(function($result_item) {
	  return intval($result_item->altitud);
	}, $result);

	if($altitudes){
		$min = min($altitudes);
		$max = max($altitudes);
		//$avg = calculate_average($pressures); con decimales
		$avg = intval(calculate_average($altitudes));
	}

	return array(
		'min' => $min,
		'max' => $max,
		'avg' => $avg
	);
}

// get array of object of pressure with min/max values
function get_alt_result_set_from_values($array,$value){
	$min_objs = array();
	$max_objs = array();
	//$avg_objs = $value['avg'];
	$avg_objs = array(array('altmedia' => $value['avg']));
	foreach ($array as $item) {

		if($item->altitud == $value['min']){
			$min_objs[] = $item;
		}

		if($item->altitud == $value['max']){
			$max_objs[] = $item;
		}

		//if($item->presion == $value['avg']){
			//$avg_objs[] = $item;
		//}
	}

	return array(
		'min' => $min_objs,
		'max' => $max_objs,
		'avg' => $avg_objs,
	);
}

function calculate_average($arr) {
	$total = 0;
    $count = count($arr); //total numbers in array
    foreach ($arr as $value) {
        $total = $total + $value; // total value of array numbers
    }
    //$average = ($total/$count); // get average value
    $average = (float)$total / $count;
    return $average;
}

function get_latest_date_entry($array){

	$latest_date_item = null;

	// get all pressure values
	$date_list = array_map(function($result_item) {
	  return $result_item->Insertado;
	}, $array);

	$max = max(array_map('strtotime', $date_list));
	$latest_date = date('Y-m-d H:i:s', $max);

	foreach ($array as $item) {
		if($item->Insertado == $latest_date){
			$latest_date_item = $item;
			break;
		}
	}

	return $latest_date_item;
}
?>