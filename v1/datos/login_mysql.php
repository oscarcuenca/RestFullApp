<?php
/**
 * Provee las constantes para conectarse a la base de datos
 * Mysql.
 */
define("NOMBRE_HOST", "mysql.hostinger.es");// Nombre del host
define("BASE_DE_DATOS", "u466913894_miweb"); // Nombre de la base de modelos
define("USUARIO", "u466913894_web"); // Nombre del usuario
define("CONTRASENA", "mividaandrea123k"); // Constraseña

$con = mysqli_connect(NOMBRE_HOST,USUARIO,CONTRASENA) or die('Unable to Connect');