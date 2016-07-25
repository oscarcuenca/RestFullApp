<?php
    // iot.php
    // Importamos la configuración
    require("config.php");
    // Leemos los valores que nos llegan por GET
    $temperatura = mysqli_real_escape_string($con, $_GET['temperatura']);
    $humedad = mysqli_real_escape_string($con, $_GET['humedad']);
    $idObjeto = mysqli_real_escape_string($con, $_GET['idObjeto']);
    
    // Esta es la instrucción para insertar los valores
    $query = "INSERT INTO wp_humedad_temperatura(humedad,temperatura,idObjeto) VALUES ('$humedad','$temperatura','$idObjeto')";
    // Ejecutamos la instrucción
    mysqli_query($con, $query);
    mysqli_close($con);
?>
