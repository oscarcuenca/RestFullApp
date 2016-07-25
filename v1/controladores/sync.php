<?php

include_once 'datos/ConexionBD.php';


class sync
{
    // [objeto]
    const ID_USUARIO = 'user_id';

    // [codigos]
    const ESTADO_EXITO = 100;
    const ESTADO_ERROR_BD = 102;
    const ESTADO_MALA_SINTAXIS = 103;

    // Mensajes estado
    const MENSAJE_100 = "Sincronizaci�n completa";
    const MENSAJE_103 = "Revise la sintaxis de su petici�n" ;

    // Campos JSON
    const INSERCIONES = 'inserciones';
    const MODIFICACIONES = 'modificaciones';
    const ELIMINACIONES = 'eliminaciones';

    /* A�ade todas los recursos que deseas enviar separados por coma ','
     *      ejemplo: array('cliente', 'factura', 'producto')
     */
    public static $tablas = array(objetos::TABLA_OBJETO);

    /**
     * Obtiene todos los registros de las tablas de la base de datos y se empaquetan
     * en un array
     * @param $segmentos array con los segmentos que vienen en la URL de la petici�n
     * @return array cuerpo de la respuesta
     * @throws ExcepcionApi
     */
    public static function get($segmentos)
    {

        $user_id = usuarios::autorizar();
        return self:: obtenerRecursos($user_id);


    }

    /**
     * Aplica los cambios que vienen descritos en formato JSON dentro del cuerpo de la petici�n
     * @param $segmentos
     * @return array arreglo con el cuerpo de la respuesta
     * @throws ExcepcionApi
     */
    public static function post($segmentos)
    {

        $user_id = usuarios::autorizar();


        $mensajePlano = file_get_contents('php://input');

        $mensajeDecodificado = json_decode($mensajePlano, PDO::FETCH_ASSOC);

        if (!empty($mensajeDecodificado)) {
            self::aplicarBatch($mensajeDecodificado, $user_id);
            // Contruir respuesta
            $respuesta['estado'] = self::ESTADO_EXITO;
            $respuesta['mensaje'] = utf8_encode(self::MENSAJE_100);
            http_response_code(200);
        } else {
            // Respuesta error
            throw new ExcepcionApi(self::ESTADO_MALA_SINTAXIS, self::MENSAJE_103, 422);
        }


        return $respuesta;

    }

    /**
     * Consulta los datos de todos los recursos sincronizables de la base de datos y los convierte en
     * un array asociativo para ser enviado con la respuesta.
     * @param $segmentos array con los segmentos enviados desde la URL
     * @param $idUsuario int con el identificador del usuario
     * @return mixed datos
     * @throws ExcepcionApi
     */
    private static function obtenerRecursos($user_id)
    {

        try {
            // Instancia PDO
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Preparar array de par�metros
            $parametros = array($user_id);

            // Procesar recursos a enviar
            foreach (self::$tablas as $tabla) {

                // Consulta gen�rica del recurso i
                $comando = 'SELECT * FROM ' . $tabla . ' WHERE ' . self::ID_USUARIO . '=?';

                // Preparar sentencia
                $sentencia = $pdo->prepare($comando);

                // Ejecutar sentencia preparada
                $sentencia->execute($parametros);

                // Extraer datos como array asociativo
                $respuesta[$tabla] = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            }

            // Estado 200 OK
            http_response_code(200);

            $respuesta['estado'] = self::ESTADO_EXITO;
            $respuesta['mensaje'] = utf8_encode(self::MENSAJE_100);

            return $respuesta;

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private static function aplicarBatch($payload, $user_id)
    {
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

        /*
         * Verificaci�n: Confirmar que existe al menos un tipo de operaci�n
         */
        if (!isset($payload[self::INSERCIONES]) && !isset($payload[self::MODIFICACIONES])
            && !isset($payload[self::ELIMINACIONES])
        ) {
            throw new ExcepcionApi(self::ESTADO_MALA_SINTAXIS, self::MENSAJE_103, 422);
        }


        try {

            // Comenzar transacci�n
            $pdo->beginTransaction();

            // Inserciones
            if (isset($payload[self::INSERCIONES]))
                objetos::insertarEnBatch($pdo, $payload[self::INSERCIONES], $user_id);
            // Modificaciones
            if (isset($payload[self::MODIFICACIONES]))
                objetos::modificarEnBatch($pdo, $payload[self::MODIFICACIONES], $user_id);
            // Eliminaciones
            if (isset($payload[self::ELIMINACIONES])) {
                objetos::eliminarEnBatch($pdo, $payload[self::ELIMINACIONES], $user_id);
            }

            // Confirmar cambios
            $pdo->commit();

        } catch (PDOException $e) {
            throw new ExcepcionApi($pdo->errorCode(), $e->getMessage(), 422);
        }
    }


}