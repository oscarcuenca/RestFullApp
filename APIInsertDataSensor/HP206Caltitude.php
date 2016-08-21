<?php
    // HP206C.php
    // Importamos la configuración
    require("config.php");
    // Leemos los valores que nos llegan por GET
    
    $altitud = mysqli_real_escape_string($con, $_GET['AltFiltered']);
    $idObjeto = mysqli_real_escape_string($con, $_GET['idObjeto']);

    // Esta es la instrucción para insertar los valores
    $query = "INSERT INTO wp_altitude(altitud,idObjeto) VALUES ('$altitud','$idObjeto')";
    // Ejecutamos la instrucción
    mysqli_query($con, $query);
    mysqli_close($con);
?>