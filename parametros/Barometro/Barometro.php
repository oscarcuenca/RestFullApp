<?php
/*
Obtenermos los datos de un registro
*/
//Importing database

require_once 'login_mysql.php';

if(isset($_POST['idObjeto']) && !empty($_POST['idObjeto'])){

	$idObjeto = $_POST['idObjeto'];

	//Creating sql query with where clause to get an sensor
	$sql =
           "SELECT
      wp_temperatura.temperatura,
      wp_temperatura.Insertado_temp,
      wp_pressure.presion,
      wp_pressure.Insertado_press,
      wp_altitude.altitud,
      wp_altitude.Insertado_alt
FROM
      wp_temperatura, wp_pressure, wp_altitude
WHERE
      wp_temperatura.idObjeto = '$idObjeto'
      AND wp_pressure.idObjeto = '$idObjeto'
      AND wp_altitude.idObjeto = '$idObjeto'";


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
			//"Id"=>$row['Id'],
			"temperatura"=>$row['temperatura'],
			"Insertado_temp"=>$row['Insertado_temp'],
			"presion"=>$row['presion'],
			"Insertado_press"=>$row['Insertado_press'],
			"altitud"=>$row['altitud'],
			"Insertado_alt"=>$row['Insertado_alt']
		));
	}

	//Displaying the array in json format
    $json_object = json_decode( json_encode(array('result'=>$result)) );
	//$json_object = json_encode(json_decode( json_encode(array('result'=>$result))) );


	$output_result = array();

	if(isset($json_object->result)){

		// get min,max and average values for temp,alt, press
		$temp_values = get_temp_values($json_object->result);
		$press_values = get_press_values($json_object->result);
		$alt_values = get_alt_values($json_object->result);

		// get temp result set with respected values
		$temp_result = get_temp_result_set_from_values($json_object->result,$temp_values);
        $press_result = get_press_result_set_from_values($json_object->result,$press_values);
        $alt_result = get_alt_result_set_from_values($json_object->result,$alt_values);
		// get latest entry
		$latest_entry_temp = get_latest_date_entry_temp($json_object->result);
		$latest_entry_press = get_latest_date_entry_press($json_object->result);
		$latest_entry_alt = get_latest_date_entry_alt($json_object->result);


		// Wrap results in an array
		$output_result = array(
			'temperature' => $temp_result,
			'pressure' => $press_result,
			'altitude' => $alt_result,
			'last_entry_temp' => $latest_entry_temp,
			'last_entry_press' => $latest_entry_press,
			'last_entry_alt' => $latest_entry_alt
		);
	}
	// Display final result
	//echo json_encode($output_result);
        echo json_encode(array('result'=>$output_result));

	// Close mysql connection
	mysqli_close($con);

}else{
	echo "Operacion fallida";
}

// get min,max and average values for temperature, pressure, altitude
function get_temp_values($result){

	$min = -1;
	$max = -1;
	$avg = -1;

	// get all pressure values
	$temperatures = array_map(function($result_item) {
	  return intval($result_item->temperatura);
	}, $result);

	if($temperatures){
		$min = min($temperatures);
		$max = max($temperatures);
		//$avg = calculate_average($temperatures); con decimales
		$avg = intval(calculate_average($temperatures));
	}

	return array(
		'min' => $min,
		'max' => $max,
		'avg' => $avg
	);
}
function get_press_values($result){

	$min = -1;
	$max = -1;
	$avg = -1;

	// get all pressure values
	$pressures = array_map(function($result_item) {
	  return intval($result_item->presion);
	}, $result);

	if($pressures){
		$min = min($pressures);
		$max = max($pressures);
		//$avg = calculate_average($pressures); con decimales
		$avg = intval(calculate_average($pressures));
	}

	return array(
		'min' => $min,
		'max' => $max,
		'avg' => $avg
	);
}

function get_alt_values($result){

	$min = -1;
	$max = -1;
	$avg = -1;

	// get all altitude values
	$altitudes = array_map(function($result_item) {
	  return intval($result_item->altitud);
	}, $result);

	if($altitudes){
		$min = min($altitudes);
		$max = max($altitudes);
		//$avg = calculate_average($temperatures); con decimales
		$avg = intval(calculate_average($altitudes));
	}

	return array(
		'min' => $min,
		'max' => $max,
		'avg' => $avg
	);
}

// get array of object of temperatures with min/max values
function get_temp_result_set_from_values($array,$value){
	$min_objs = array();
	$max_objs = array();
	$avg_objs = array(array('tempmedia' => $value['avg']));


	foreach ($array as $item) {

		if($item->temperatura == $value['min']){
			$min_objs[] = $item;
		}

		if($item->temperatura == $value['max']){
			$max_objs[] = $item;
		}

	}
	  	return array(
		'min' => $min_objs,
		'max' => $max_objs,
		'avg' => $avg_objs,
	);
}

// get array of object of temperatures with min/max values
function get_press_result_set_from_values($array,$value){
	$min_objs = array();
	$max_objs = array();
	$avg_objs = array(array('pressmedia' => $value['avg']));

	foreach ($array as $item) {

		if($item->presion == $value['min']){
			$min_objs[] = $item;
		}

		if($item->presion == $value['max']){
			$max_objs[] = $item;
		}
	}

	return array(
		'min' => $min_objs,
		'max' => $max_objs,
		'avg' => $avg_objs,
	);
}
function get_alt_result_set_from_values($array,$value){
	$min_objs = array();
	$max_objs = array();
	$avg_objs = array(array('altmedia' => $value['avg']));

	foreach ($array as $item) {

		if($item->altitud == $value['min']){
			$min_objs[] = $item;
		}

		if($item->altitud == $value['max']){
			$max_objs[] = $item;
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
    //$average = ($total/$count); // get average value
    $average = (float)$total / $count;
    return $average;
}

function get_latest_date_entry_temp($array){

	$latest_date_item = null;

	// get all temperature values
	$date_list = array_map(function($result_item) {
	  return $result_item->Insertado_temp;
	}, $array);

	$max = max(array_map('strtotime', $date_list));
	$latest_date_temp = date('Y-m-d H:i:s', $max);

	foreach ($array as $item) {
		if($item->Insertado_temp == $latest_date_temp){
			$latest_date_item = $item;
			break;
		}
	}

	return $latest_date_item;
}
function get_latest_date_entry_press($array){

	$latest_date_item = null;

	// get all temperature values
	$date_list = array_map(function($result_item) {
	  return $result_item->Insertado_press;
	}, $array);

	$max = max(array_map('strtotime', $date_list));
	$latest_date_press = date('Y-m-d H:i:s', $max);

	foreach ($array as $item) {
		if($item->Insertado_press == $latest_date_press){
			$latest_date_item = $item;
			break;
		}
	}

	return $latest_date_item;
}
function get_latest_date_entry_alt($array){

	$latest_date_item = null;

	// get all temperature values
	$date_list = array_map(function($result_item) {
	  return $result_item->Insertado_alt;
	}, $array);

	$max = max(array_map('strtotime', $date_list));
	$latest_date_alt = date('Y-m-d H:i:s', $max);

	foreach ($array as $item) {
		if($item->Insertado_alt == $latest_date_alt){
			$latest_date_item = $item;
			break;
		}
	}

	return $latest_date_item;
}
?>