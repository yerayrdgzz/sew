<?php

class Configuracion {

    private $conn;

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
            "CREATE TABLE IF NOT EXISTS profesiones (
                id_profesion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                profesion VARCHAR(100) NOT NULL UNIQUE
            )",
            "CREATE TABLE IF NOT EXISTS generos (
                id_genero INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                genero VARCHAR(50) NOT NULL UNIQUE
            )",
            "CREATE TABLE IF NOT EXISTS usuarios (
                id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_profesion INT UNSIGNED NOT NULL,
                edad TINYINT UNSIGNED NOT NULL,
                id_genero INT UNSIGNED NOT NULL,
                pericia_informatica TINYINT UNSIGNED NOT NULL CHECK (pericia_informatica BETWEEN 0 AND 10),
                FOREIGN KEY (id_profesion) REFERENCES profesiones(id_profesion) ON DELETE RESTRICT ON UPDATE CASCADE,
                FOREIGN KEY (id_genero) REFERENCES generos(id_genero) ON DELETE RESTRICT ON UPDATE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS resultados_test (
                id_test INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT UNSIGNED NOT NULL UNIQUE,
                dispositivo ENUM('ordenador','tableta','telefono') NOT NULL,
                tiempo_segundos INT UNSIGNED NOT NULL,
                completado BOOLEAN NOT NULL,
                comentarios TEXT,
                propuestas_mejora TEXT,
                valoracion TINYINT UNSIGNED CHECK (valoracion BETWEEN 0 AND 10),
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS observaciones (
                id_observacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT UNSIGNED NOT NULL UNIQUE,
                comentario_facilitador TEXT NOT NULL,
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
            )"
        ];

        // Crear Tablas
        foreach ($tablas as $sql) {
            if (!$conn->query($sql)) {
                $error = "Error al crear la tabla: " . $conn->error;
                $conn->close();
                return $error;
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
        $tablas = ["observaciones", "resultados_test", "usuarios", "profesiones", "generos"];
        
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
        
        // Verifica si la conexión o la selección de la BD falló
        if (is_string($conn)) {
            return $conn;
        }

        // 1. Establecer encabezados HTTP para la descarga
        $filename = "exportacion_datos_" . date('Ymd_His') . ".csv";

        // Limpiar el buffer de salida por si hay espacios o saltos de línea antes de los encabezados
        if (ob_get_contents()) {
            ob_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // 2. Usar php://output para enviar el contenido al navegador
        $output = fopen('php://output', 'w');
        
        // 3. Establecer el separador CSV (punto y coma o coma)
        $delimiter = ';'; 

        $tablas = ["profesiones", "generos", "usuarios", "resultados_test", "observaciones"];

        // Iterar sobre cada tabla
        foreach ($tablas as $tabla) {
            
            // Añadir un encabezado separador para distinguir las tablas
            fputcsv($output, ["--- Tabla: " . strtoupper($tabla) . " ---"], $delimiter);
            
            $result = $conn->query("SELECT * FROM $tabla");
            
            if ($result === FALSE) {
                // Si falla la consulta, cerramos y retornamos error (Aunque el cliente ya tiene encabezados, intentamos avisar)
                $conn->close();
                return "Error al consultar la tabla '$tabla': " . $conn->error;
            }

            // Encabezados de Columna
            $columns = $result->fetch_fields();
            $headers = [];
            foreach ($columns as $col) {
                $headers[] = $col->name;
            }
            fputcsv($output, $headers, $delimiter);

            // Datos
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row, $delimiter);
            }
            
            fputcsv($output, [''], $delimiter); // Fila vacía para separación visual
        }

        fclose($output);
        $conn->close();
        exit;        
        return "DESCARGA_EXITOSA";
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