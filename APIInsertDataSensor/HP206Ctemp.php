<?php
    // HP206C.php
    // Importamos la configuraci�n
    require("config.php");
    // Leemos los valores que nos llegan por GET
    $temperatura = mysqli_real_escape_string($con, $_GET['TempFiltered']);
    $idObjeto = mysqli_real_escape_string($con, $_GET['idObjeto']);

    // Esta es la instrucci�n para insertar los valores
    $query = "INSERT INTO wp_temperatura(temperatura,idObjeto) VALUES ('$temperatura','$idObjeto')";
    // Ejecutamos la instrucci�n
    mysqli_query($con, $query);
    mysqli_close($con);
?>