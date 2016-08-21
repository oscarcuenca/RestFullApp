<?php
    // HP206C.php
    // Importamos la configuración
    require("config.php");
    // Leemos los valores que nos llegan por GET
    $temperatura = mysqli_real_escape_string($con, $_GET['TempFiltered']);
    $idObjeto = mysqli_real_escape_string($con, $_GET['idObjeto']);

    // Esta es la instrucción para insertar los valores
    $query = "INSERT INTO wp_temperatura(temperatura,idObjeto) VALUES ('$temperatura','$idObjeto')";
    // Ejecutamos la instrucción
    mysqli_query($con, $query);
    mysqli_close($con);
?>