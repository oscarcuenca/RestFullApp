<?php


class objetos

{
    // [/objeto]
    const TABLA_OBJETO = "objetos";
    const ID_OBJETO = "idObjeto";
    const DESCRIPCION_NOMBRE = "descripcionNombre";
    const MARCA_MARCA = "marca";
    const MODELO = "modelo";
    const CORREO = "correo";
    const ID_USUARIO = "user_id";
    const VERSION = 'version';
    // [/objeto]


    // [codigos]
    const ESTADO_EXITO = 100;
    const ESTADO_ERROR = 101;
    const ESTADO_ERROR_BD = 102;
    const ESTADO_MALA_SINTAXIS = 103;
    const ESTADO_NO_ENCONTRADO = 104;
    // [/codigos]

	// Campos JSON
    const INSERCIONES = "inserciones";
    const MODIFICACIONES = "modificaciones";
    const ELIMINACIONES = 'eliminaciones';



    public static function get($peticion)
    {

        $user_id= usuarios::autorizar();


        if (empty($peticion[0]))
            return self::obtenerObjetos($user_id);
        else
            return self::obtenerObjetos($user_id, $peticion[0]);

    }

    public static function post($segmentos)
    {
        $user_id = usuarios::autorizar();

        $payload = file_get_contents('php://input');
        $payload = json_decode($payload);

        $idObjeto = objetos::insertar($user_id, $payload);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "Objeto creado",
            "id" => $idObjeto
        ];

    }

    public static function put($peticion)
    {
        $user_id = usuarios::autorizar();

        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $objeto = json_decode($body);

            if (self::modificar($user_id, $objeto, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro actualizado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El objeto al que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
    }

    public static function delete($peticion)
    {
        $user_id = usuarios::autorizar();

        if (!empty($peticion[0])) {
            if (self::eliminar($user_id, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro eliminado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El objeto al que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }

    }

    /**
     * Obtiene la colección de objetos o un solo objecto indicado por el identificador
     * @param int $idUsuario identificador del usuario
     * @param null $idObjeto identificador del objeto (Opcional)
     * @return array registros de la tabla objetos
     * @throws Exception
     */
    private static function obtenerObjetos($user_id, $idObjeto = NULL)
    {
        try {
            if (!$idObjeto) {
                $comando = "SELECT * FROM " . self::TABLA_OBJETO .
                    " WHERE " . self::ID_USUARIO . "=?";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idUsuario
                $sentencia->bindParam(1, $user_id, PDO::PARAM_INT);

            } else {
                $comando = "SELECT * FROM " . self::TABLA_OBJETO .
                    " WHERE " . self::ID_OBJETO . "=? AND " .
                    self::ID_USUARIO . "=?";
                   }

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idObjeto e idUsuario
                $sentencia->bindParam(1, $idObjeto, PDO::PARAM_INT);
                $sentencia->bindParam(2, $user_id, PDO::PARAM_INT);


            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "estado" => self::ESTADO_EXITO,
                        "datos" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Añade un nuevo objeto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param mixed $objeto datos del objeto
     * @return string identificador del objeto
     * @throws ExcepcionApi
     */
    private function insertar($user_id, $objeto)
    {
        if ($objeto) {
            try {

                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

                // Sentencia INSERT
                $comando = "INSERT INTO " . self::TABLA_OBJETO . " ( " .
                    self::ID_OBJETO. "," .
                    self::DESCRIPCION_NOMBRE . "," .
                    self::MARCA_MARCA . "," .
                    self::MODELO . "," .
                    self::CORREO . "," .
                    self::ID_USUARIO . ")" .
                    " VALUES(?,?,?,?,?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                // Generar Pk
                $idObjeto = 'C-'.self::generarUuid();

                $sentencia->bindParam(1, $idObjeto);
                $sentencia->bindParam(2, $descripcionNombre);
                $sentencia->bindParam(3, $marca);
                $sentencia->bindParam(4, $modelo);
                $sentencia->bindParam(5, $correo);
                $sentencia->bindParam(6, $user_id);


                $descripcionNombre = $objeto->descripcionNombre;
                $marca = $objeto->marca;
                $modelo = $objeto->modelo;
                $correo = $objeto->correo;

                $sentencia->execute();

                // Retornar en el último id insertado
                return $idObjeto;

            } catch (PDOException $e) {
                throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
            }
        } else {
            throw new ExcepcionApi(
                self::ESTADO_ERROR_PARAMETROS,
                utf8_encode("Error en existencia o sintaxis de parámetros"));
        }

    }

    /**
     * Actualiza el objeto especificado por idUsuario
     * @param int $idUsuario
     * @param object $objeto objeto con los valores nuevos del objeto
     * @param int $idObejto
     * @return PDOStatement
     * @throws Exception
     */
    private function modificar($user_id, $objeto, $idObjeto)
    {
        try {
            // Creando consulta UPDATE
            $consulta = "UPDATE " . self::TABLA_OBJETO .
                " SET " . self::DESCRIPCION_NOMBRE . "=?," .
                self::MARCA_MARCA . "=?," .
                self::MODELO . "=?," .
                self::CORREO . "=? " .
                " WHERE " . self::ID_OBJETO . "=? AND " . self::ID_USUARIO . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);

            $sentencia->bindParam(1, $descripcionNombre);
            $sentencia->bindParam(2, $marca);
            $sentencia->bindParam(3, $modelo);
            $sentencia->bindParam(4, $correo);
            $sentencia->bindParam(5, $idObjeto);
            $sentencia->bindParam(6, $user_id);

            $descripcionNombre = $objeto->descripcionNombre;
            $marca = $objeto->marca;
            $modelo = $objeto->modelo;
            $correo = $objeto->correo;

            // Ejecutar la sentencia
            $sentencia->execute();

            return $sentencia->rowCount();

         } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }

              }

    /**
     * Elimina un objeto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param int $idObjeto identificador del objeto
     * @return bool true si la eliminación se pudo realizar, en caso contrario false
     * @throws Exception excepcion por errores en la base de datos
     */
    private function eliminar($user_id, $idObjeto)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::TABLA_OBJETO .
                " WHERE " . self::ID_OBJETO . "=? AND " .
                self::ID_USUARIO . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $idObjeto);
            $sentencia->bindParam(2, $user_id);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

/**
     * Inserta n elementos de seguidos en la tabla objetos
     * @param int $idUsuario identificador del usuario
     * @param mixed $contacto datos del objetos
     * @return string identificador del objetos
     * @throws ExcepcionApi
     */
    public static function insertarEnBatch(PDO $pdo, $listaObjetos, $user_id)
    {
        // Sentencia INSERT
        $comando = 'INSERT INTO ' . self::TABLA_OBJETO . " ( " .
            self::ID_OBJETO . "," .
            self::DESCRIPCION_NOMBRE . "," .
            self::MARCA_MARCA . "," .
            self::MODELO . "," .
            self::CORREO . "," .
            self::ID_USUARIO . "," .
            self::VERSION . ")" .
            " VALUES(?,?,?,?,?,?,?)";

        // Preparar la sentencia
        $sentencia = $pdo->prepare($comando);

        $sentencia->bindParam(1, $idObjeto);
        $sentencia->bindParam(2, $descripcionNombre);
        $sentencia->bindParam(3, $marca);
        $sentencia->bindParam(4, $modelo);
        $sentencia->bindParam(5, $correo);
        $sentencia->bindParam(6, $user_id);
        $sentencia->bindParam(7, $version);


        foreach ($listaObjetos as $item) {
            $idObjeto = $item[self::ID_OBJETO];
            $descripcionNombre = $item[self::DESCRIPCION_NOMBRE];
            $marca = $item[self::MARCA_MARCA];
            $modelo = $item[self::MODELO];
            $correo = $item[self::CORREO];
            $correo = $item[self::VERSION];

            $sentencia->execute();

        }

    }

    /**
     * Aplica n modificaciones de objetos
     * @param PDO $pdo instancia controlador de base de datos
     * @param $arrayContactos lista de objetos
     * @param $idUsuario identificador del usuario
     */
    public static function modificarEnBatch(PDO $pdo, $arrayObjetos, $user_id)
    {
// Preparar operación de modificación para cada objeto
        $comando = 'UPDATE ' . self::TABLA_OBJETO . ' SET ' .
            self::DESCRIPCION_NOMBRE . '=?,' .
            self::MARCA_MARCA . '=?,' .
            self::MODELO . '=?,' .
            self::CORREO . '=?,' .
            self::VERSION . '=?,' .
            ' WHERE ' . self::ID_OBJETO . '=? AND ' . self::ID_USUARIO . '=?';

        // Preparar la sentencia update
        $sentencia = $pdo->prepare($comando);

        // Ligar parametros
        $sentencia->bindParam(1, $descripcionNombre);
        $sentencia->bindParam(2, $marca);
        $sentencia->bindParam(3, $modelo);
        $sentencia->bindParam(4, $correo);
        $sentencia->bindParam(5, $version);
        $sentencia->bindParam(6, $idObjeto);
        $sentencia->bindParam(7, $user_id);


        // Procesar array de objetos
        foreach ($arrayObjetos as $objeto) {
            $idObjeto = $objeto[self::ID_OBJETO];
            $descripcionNombre = $objeto[self::DESCRIPCION_NOMBRE];
            $marca = $objeto[self::MARCA_MARCA];
            $modelo = $objeto[self::MODELO];
            $correo = $objeto[self::CORREO];
            $version = $item[self::VERSION];
            $sentencia->execute();
        }

    }

    /**
     * Aplina n eliminaciones a la tabla 'objeto'
     * @param PDO $pdo instancia controlador de base de datos
     * @param $arrayIds lista de objetos
     * @param $idUsuario identificador del usuario
     */
    public static function eliminarEnBatch(PDO $pdo, $arrayIds, $user_id)
    {
        // Crear sentencia DELETE
        $comando = 'DELETE FROM ' . self::TABLA_OBJETO .
            ' WHERE ' . self::ID_OBJETO . ' = ? AND ' . self::ID_USUARIO . '=?';

        // Preparar sentencia en el contenedor
        $sentencia = $pdo->prepare($comando);


        // Procesar todas las ids
        foreach ($arrayIds as $id) {
            $sentencia->execute(array($id, $user_id));
        }

    }

    /**
     * Genera id aleatoria con formato UUID
     * @return string identificador
     */
    function generarUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
