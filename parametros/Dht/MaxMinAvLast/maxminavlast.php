<?php
/*
Obtenermos los datos de un registro
*/
//Importing database

require_once 'login_mysql.php';

if(isset($_POST['idObjeto']) && !empty($_POST['idObjeto'])){

	$idObjeto = $_POST['idObjeto'];

	//Creating sql query with where clause to get an sensor
	$sql = "SELECT * FROM wp_humedad_temperatura WHERE idObjeto='$idObjeto'";

	if (mysqli_connect_errno()){
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
			"temperatura"=>$row['temperatura'],
			"humedad"=>$row['humedad'],
			"Insertado"=>$row['Insertado']
		));
	}

	//Displaying the array in json format
	$json_object = json_decode( json_encode(array('result'=>$result)) );

	$output_result = array();

	if(isset($json_object->result)){

		// get min,max and average values for temprature
		$temp_values = get_temp_values($json_object->result);

		// get min,max and average values for humidity
		$humidity_values = get_humidity_values($json_object->result);

		// get temprature result set with respected values
		$temp_result = get_temp_result_set_from_values($json_object->result,$temp_values);

		// get humidity result set with respected values
		$humidity_result = get_humidity_result_set_from_values($json_object->result,$humidity_values);

		// get latest entry
		$latest_entry = get_latest_date_entry($json_object->result);

		// Wrap results in an array
		$output_result = array(
			'temprature' => $temp_result,
			'humidity' => $humidity_result,
			'last_entry' => $latest_entry
		);
	}

	// Display final result
	echo json_encode($output_result);

	// Close mysql connection
	mysqli_close($con);

}else{
	echo "Operacion fallida";
}

// get min,max and average values for temprature
function get_temp_values($result){

	$min = -1;
	$max = -1;
	$avg = -1;

	// get all temprateure values
	$tempratures = array_map(function($result_item) {
	  return intval($result_item->temperatura);
	  //si ves que aquí no te retorna valores decimales, provar strval en lugar de intval
       /*
       $var = '122.34343El';
       $valor_float_de_var = floatval($var);
       echo $valor_float_de_var; // 122.34343
       */
	}, $result);

	if($tempratures){
		$min = min($tempratures);
		$max = max($tempratures);
		$avg = intval(calculate_average($tempratures));
	}

	return array(
		'min' => $min,
		'max' => $max,
		'avg' => $avg
	);
}

// get min,max and average values for humidity
function get_humidity_values($result){

	$min = -1;
	$max = -1;
	$avg = -1;

	// get all temprateure values
	$humidities = array_map(function($result_item) {
	  return intval($result_item->humedad);
	}, $result);

	if($humidities){
		$min = min($humidities);
		$max = max($humidities);
		$avg = intval(calculate_average($humidities));
	}

	return array(
		'min' => $min,
		'max' => $max,
		'avg' => $avg
	);
}

// get array of object of tempratuere with min/max values
function get_temp_result_set_from_values($array,$value){
	$min_objs = array();
	$max_objs = array();
	$avg_objs = array();
	foreach ($array as $item) {

		if($item->temperatura == $value['min']){
			$min_objs[] = $item;
		}

		if($item->temperatura == $value['max']){
			$max_objs[] = $item;
		}

		if($item->temperatura == $value['avg']){
			$avg_objs[] = $item;
		}
	}

	return array(
		'min' => $min_objs,
		'max' => $max_objs,
		'avg' => $avg_objs,
	);
}

// get array of object of humidity with min/max values
function get_humidity_result_set_from_values($array,$value){
	$min_objs = array();
	$max_objs = array();
	$avg_objs = array();
	foreach ($array as $item) {

		if($item->humedad == $value['min']){
			$min_objs[] = $item;
		}

		if($item->humedad == $value['max']){
			$max_objs[] = $item;
		}

		if($item->humedad == $value['avg']){
			$avg_objs[] = $item;
		}
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
    $average = ($total/$count); // get average value
    return $average;
}

function get_latest_date_entry($array){

	$latest_date_item = null;

	// get all temprateure values
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
