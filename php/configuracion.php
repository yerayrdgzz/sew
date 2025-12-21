<?php

class Configuracion {
    private $servername = "localhost";
    private $username = "DBUSER2025";
    private $password = "DBPSWD2025";
    private $database = "uo284247_db";

    /* ======================================================
        CONEXIÓN BASE DE DATOS 
        ====================================================== */
    private function getConnection($selectDb = false) {
        // Crear conexión al servidor MySQL
        $conn = new mysqli($this->servername, $this->username, $this->password);

        if ($conn->connect_error) {
            return "Falló la conexión a MySQL: " . $conn->connect_error;
        }

        // Si se solicita seleccionar una BD
        if ($selectDb) {

            // Comprobar si existe la base de datos
            $dbExists = $conn->query("SHOW DATABASES LIKE '{$this->database}'");

            if ($dbExists->num_rows == 0) {
                $conn->close();
                return "La base de datos '{$this->database}' no existe. Por favor, créala primero.";
            }

            // Seleccionar la BD ahora que sabemos que existe
            if (!$conn->select_db($this->database)) {
                $conn->close();
                return "No se pudo seleccionar la base de datos '{$this->database}'.";
            }
        }

        return $conn;
    }


    /* ======================================================
        CREAR BASE DE DATOS Y TABLAS
    ====================================================== */
    public function crearBD() {
        $conn = $this->getConnection();
        
        // Verifica si la conexión falló (devolvió una cadena de error)
        if (is_string($conn)) {
            return $conn;
        }

        // Comprobar si la BD ya existe ANTES de intentar crearla
        $check_sql = "SHOW DATABASES LIKE '" . $this->database . "'";
        $result = $conn->query($check_sql);

        if ($result === FALSE) {
            $error = "Error al verificar la existencia de la BD: " . $conn->error;
            $conn->close();
            return $error;
        }

        $db_exists = $result->num_rows > 0;
        $mensaje_final = "";

        if (!$db_exists) {
            $sql_db = "CREATE DATABASE " . $this->database;
            if (!$conn->query($sql_db)) {
                $error = "Error al crear la Base de Datos: " . $conn->error;
                $conn->close();
                return $error;
            }
            $mensaje_final = "¡Éxito! Base de datos creada por primera vez.";
        } else {
            $mensaje_final = "La base de datos ya existía.";
        }

        if (!$conn->select_db($this->database)) {
            $error = "Error al seleccionar la Base de Datos: " . $conn->error;
            $conn->close();
            return $error;
        }

        
        // Definición de Tablas
        $tablas = [
            "CREATE TABLE `observaciones` (
                `id_observacion` int(10) UNSIGNED NOT NULL,
                `id_resultado` int(10) UNSIGNED NOT NULL,
                `comentarios` text DEFAULT NULL
            )",
            "CREATE TABLE `preguntas` (
                `id_pregunta` int(10) UNSIGNED NOT NULL,
                `pregunta` varchar(255) NOT NULL
            )",
            "CREATE TABLE `respuestas` (
                `id_respuesta` int(10) UNSIGNED NOT NULL,
                `id_resultado` int(10) UNSIGNED NOT NULL,
                `id_pregunta` int(10) UNSIGNED NOT NULL,
                `respuesta` varchar(500) NOT NULL
            )",
            "CREATE TABLE `resultados` (
                `id_resultado` int(10) UNSIGNED NOT NULL,
                `id_usuario` int(10) UNSIGNED NOT NULL,
                `dispositivo` enum('Ordenador','Tableta','Teléfono') NOT NULL,
                `tiempo` time NOT NULL,
                `completada` tinyint(1) NOT NULL,
                `comentarios_usuario` text DEFAULT NULL,
                `propuestas` text DEFAULT NULL,
                `valoracion` tinyint(3) UNSIGNED DEFAULT NULL
            )",
            "CREATE TABLE `usuarios` (
                `id_usuario` int(10) UNSIGNED NOT NULL ,
                `profesion` varchar(100) NOT NULL,
                `edad` tinyint(3) UNSIGNED NOT NULL,
                `genero` enum('Masculino','Femenino','Otro') NOT NULL,
                `pericia` tinyint(3) UNSIGNED DEFAULT NULL
            )",
        ];

        $operaciones_alter = [
            // Claves Primarias y Únicas
            "ALTER TABLE `observaciones` ADD PRIMARY KEY (`id_observacion`)",
            "ALTER TABLE `observaciones` ADD UNIQUE KEY `uk_observacion_resultado` (`id_resultado`)",
            "ALTER TABLE `preguntas` ADD PRIMARY KEY (`id_pregunta`)",
            "ALTER TABLE `respuestas` ADD PRIMARY KEY (`id_respuesta`)",
            "ALTER TABLE `resultados` ADD PRIMARY KEY (`id_resultado`)",
            "ALTER TABLE `usuarios` ADD PRIMARY KEY (`id_usuario`)",

            // Auto Incrementos
            "ALTER TABLE `observaciones` MODIFY `id_observacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `preguntas` MODIFY `id_pregunta` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1",
            "ALTER TABLE `respuestas` MODIFY `id_respuesta` int(10) UNSIGNED NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `resultados` MODIFY `id_resultado` int(10) UNSIGNED NOT NULL AUTO_INCREMENT",
            "ALTER TABLE `usuarios` MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT",

            // Índices de Claves Foráneas
            "ALTER TABLE `respuestas` ADD KEY `fk_respuesta_resultado` (`id_resultado`)",
            "ALTER TABLE `respuestas` ADD KEY `fk_respuesta_pregunta` (`id_pregunta`)",
            "ALTER TABLE `resultados` ADD KEY `fk_resultados_usuario` (`id_usuario`)",

            // Restricciones de Claves Foráneas (FK)
            "ALTER TABLE `observaciones` ADD CONSTRAINT `fk_observacion_resultado` FOREIGN KEY (`id_resultado`) REFERENCES `resultados` (`id_resultado`) ON UPDATE CASCADE",
            "ALTER TABLE `respuestas` ADD CONSTRAINT `fk_respuesta_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`) ON UPDATE CASCADE",
            "ALTER TABLE `respuestas` ADD CONSTRAINT `fk_respuesta_resultado` FOREIGN KEY (`id_resultado`) REFERENCES `resultados` (`id_resultado`) ON DELETE CASCADE ON UPDATE CASCADE",
            "ALTER TABLE `resultados` ADD CONSTRAINT `fk_resultados_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE"
        ];

        $result = $conn->query("SHOW TABLES LIKE 'observaciones'");

        if ($result->num_rows == 0) {
            foreach ($tablas as $sql) {
                $conn->query($sql);
            }
            foreach ($operaciones_alter as $sql_alter) {
                $conn->query($sql_alter);
            }
        }
        
        $conn->close();
        return $mensaje_final; 
    }

    /* ======================================================
        REINICIAR BASE DE DATOS (VACÍA LAS TABLAS)
    ====================================================== */
    public function reiniciarBD() {
        $conn = $this->getConnection(true);
        
        // Verifica si la conexión falló
        if (is_string($conn)) {
            return $conn;
        }

        // Orden inversa para evitar problemas de FK al truncar
        $tablas = ["observaciones", "preguntas", "respuestas", "resultados", "usuarios"];
        
        // Desactivar FK checks para TRUNCATE
        $conn->query('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tablas as $tabla) {
            if (!$conn->query("TRUNCATE TABLE $tabla")) {
                $error = "Error al vaciar la tabla '$tabla': " . $conn->error;
                $conn->query('SET FOREIGN_KEY_CHECKS = 1'); // Reactivar por si acaso
                $conn->close();
                return $error;
            }
        }

        // Reactivar FK checks
        $conn->query('SET FOREIGN_KEY_CHECKS = 1');
        $conn->close();
        return "Los datos de las tablas han sido vaciados correctamente.";
    }

    /* ======================================================
        ELIMINAR BASE DE DATOS COMPLETA
    ====================================================== */
    public function eliminarBD() {
        $conn = $this->getConnection();
        
        // Verifica si la conexión falló
        if (is_string($conn)) {
            return $conn;
        }

        $sql = "DROP DATABASE IF EXISTS " . $this->database;
        
        if ($conn->query($sql) === TRUE) {
            $conn->close();
            return "Base de datos eliminada correctamente.";
        } else {
            $error = "Error al eliminar la Base de Datos: " . $conn->error;
            $conn->close();
            return $error;
        }
    }

    /* ======================================================
        EXPORTAR TODAS LAS TABLAS A CSV
    ====================================================== */
    public function exportarCSV() {
        $conn = $this->getConnection(true);
        if (is_string($conn)) return $conn;

        $filename = "estudio_completo_motogp_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        $output = fopen('php://output', 'w');
        // BOM para acentos en Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // 1. Cabeceras exactamente como las has pedido
        $cabeceras = [
            'id_usuario', 'profesion', 'edad', 'genero', 'pericia', 
            'id_resultado', 'id_usuario_dup', 'dispositivo', 'tiempo', 'completada', 'comentarios_usuario', 'propuestas', 'valoracion',
            'id_respuesta_ref', 'id_resultado_ref', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7', 'P8', 'P9', 'P10',
            'id_observacion', 'id_resultado_obs', 'comentarios'
        ];
        fputcsv($output, $cabeceras, ',');

        // 2. Consulta SQL: Unimos las tablas y usamos MAX(CASE...) para poner las preguntas en columnas
        $query = "SELECT 
                    u.id_usuario, u.profesion, u.edad, u.genero, u.pericia,
                    res.id_resultado, res.id_usuario AS id_usuario_dup, res.dispositivo, res.tiempo, res.completada, res.comentarios_usuario, res.propuestas, res.valoracion,
                    MIN(resp.id_respuesta) as id_respuesta_ref, resp.id_resultado as id_resultado_ref,
                    MAX(CASE WHEN resp.id_pregunta = 1 THEN resp.respuesta END) AS P1,
                    MAX(CASE WHEN resp.id_pregunta = 2 THEN resp.respuesta END) AS P2,
                    MAX(CASE WHEN resp.id_pregunta = 3 THEN resp.respuesta END) AS P3,
                    MAX(CASE WHEN resp.id_pregunta = 4 THEN resp.respuesta END) AS P4,
                    MAX(CASE WHEN resp.id_pregunta = 5 THEN resp.respuesta END) AS P5,
                    MAX(CASE WHEN resp.id_pregunta = 6 THEN resp.respuesta END) AS P6,
                    MAX(CASE WHEN resp.id_pregunta = 7 THEN resp.respuesta END) AS P7,
                    MAX(CASE WHEN resp.id_pregunta = 8 THEN resp.respuesta END) AS P8,
                    MAX(CASE WHEN resp.id_pregunta = 9 THEN resp.respuesta END) AS P9,
                    MAX(CASE WHEN resp.id_pregunta = 10 THEN resp.respuesta END) AS P10,
                    obs.id_observacion, obs.id_resultado AS id_resultado_obs, obs.comentarios AS comentarios_obs
                FROM resultados res
                JOIN usuarios u ON res.id_usuario = u.id_usuario
                LEFT JOIN respuestas resp ON res.id_resultado = resp.id_resultado
                LEFT JOIN observaciones obs ON res.id_resultado = obs.id_resultado
                GROUP BY res.id_resultado
                ORDER BY res.id_resultado ASC";

        $result = $conn->query($query);

        while ($row = $result->fetch_assoc()) {
            $filaLimpia = [];
            foreach ($row as $valor) {
                // Aplicamos la limpieza de espacios, tabs y saltos de línea a cada celda
                $filaLimpia[] = $this->limpiarDato($valor);
            }
            fputcsv($output, $filaLimpia, ',');
        }

        fclose($output);
        $conn->close();
        exit;
    }

    // Función para garantizar que ningún dato rompa el formato del CSV
    private function limpiarDato($dato) {
        if ($dato === null) return "";
        // Quitamos espacios en blanco de los extremos
        $dato = trim($dato);
        // Sustituimos saltos de línea y tabuladores por un espacio simple
        $dato = str_replace(["\r", "\n", "\t"], " ", $dato);
        // Si hay múltiples espacios seguidos, los dejamos en uno solo
        $dato = preg_replace('/\s+/', ' ', $dato);
        return $dato;
    }
}


$config = new Configuracion();
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["accion"])) {
    switch ($_POST["accion"]) {
        case "crear":
            $mensaje = $config->crearBD();
            break;
        case "reiniciar":
            $mensaje = $config->reiniciarBD();
            break;
        case "eliminar":
            $mensaje = $config->eliminarBD();
            break;
        case "exportar":
            // 1. Ejecutar la función de descarga
            $mensaje = $config->exportarCSV();
            break;
    }
}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name ="author" content ="Yeray Rodríguez Granda" />
    <meta name ="description" content ="Cronómetro para medir el tiempo" />
    <meta name ="keywords" content ="MotoGP, tiempo, cronómetro" />
    <meta name ="viewport" content ="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
    <link rel="icon" type="image/png" href="multimedia/logo.png" />
    <title>MotoGP</title>
</head>

<body>
    <main>
        <h2>Configuración Test</h2>

        <form method="post">
            <button type="submit" name="accion" value="crear">Crear Base de Datos</button>  
        </form>

        <form method="post">
            <button type="submit" name="accion" value="reiniciar">Reiniciar Datos</button>
        </form>

        <form method="post">
            <button type="submit" name="accion" value="eliminar">Eliminar Base de Datos</button>
        </form>

        <form method="post">
            <button type="submit" name="accion" value="exportar">Exportar CSV</button>
        </form>

        <?php if (!empty($mensaje)) echo "<p>$mensaje</p>"; ?>
    </main>
</body>
</html>