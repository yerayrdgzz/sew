<?php
include '../cronometro.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errorFormulario = false;
$id_usuario_nuevo = null;

// Datos de conexión
$servername = "localhost";
$username = "DBUSER2025";
$password = "DBPSWD2025";
$database = "uo284247_db";

$paso = 1;

// Lógica de procesamiento
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['enviar']) && $_POST['enviar'] == 'Iniciar prueba') {
        $profesion = $_POST['profesion'] ?? '';
        $edad = $_POST['edad'] ?? '';
        $genero = $_POST['genero'] ?? '';
        $pericia = $_POST['pericia'] ?? '';

        if (!empty($profesion) && !empty($edad) && ($edad > 0 && $edad <= 120) && !empty($genero)) {
            $conn = new mysqli($servername, $username, $password, $database);
            if (!$conn->connect_error) {
                $sql = "INSERT INTO usuarios (profesion, edad, genero, pericia) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sisi", $profesion, $edad, $genero, $pericia);
                    if ($stmt->execute()) {
                        $_SESSION['id_usuario'] = $conn->insert_id;
                        $crono = new Cronometro();
                        $crono->arrancar(); // Empieza a contar
                        
                        // Guardamos el estado del cronómetro en la sesión
                        $_SESSION['cronometro_tiempo'] = $crono->getTiempo();
                        $_SESSION['cronometro_inicio'] = $crono->getInicio();
                        $paso = 2;
                    }
                }
            }
        } else {
            $errorFormulario = true;
        }
    }
    if (isset($_POST['enviar']) && $_POST['enviar'] == 'Finalizar') {
        $crono = new Cronometro($_SESSION['cronometro_tiempo'], $_SESSION['cronometro_inicio']);
        $crono->parar();

        $tiempo_total = $crono->mostrar(); // Formato MM:SS.D
        $id_usuario = $_SESSION['id_usuario'];
        
        $conn = new mysqli($servername, $username, $password, $database);
        
        $sql_res = "INSERT INTO resultados (id_usuario, tiempo, completada) VALUES (?, ?, 0)";
        $stmt_res = $conn->prepare($sql_res);
        $stmt_res->bind_param("is", $id_usuario, $tiempo_total);

        if ($stmt_res->execute()) {
            $id_resultado = $conn->insert_id;
            $_SESSION['id_resultado'] = $id_resultado;

            $stmt_resp = $conn->prepare("INSERT INTO respuestas (id_resultado, id_pregunta, respuesta) VALUES (?, ?, ?)");
            if (isset($_POST['respuestas']) && is_array($_POST['respuestas'])) {
                foreach ($_POST['respuestas'] as $id_preg => $txt) {
                    $stmt_resp->bind_param("iis", $id_resultado, $id_preg, $txt);
                    $stmt_resp->execute();
                }
            }
            $paso = 3;
        }
    }
    if (isset($_POST['enviar']) && $_POST['enviar'] == 'Enviar Informe Final') {
        $conn = new mysqli($servername, $username, $password, $database);
        $id_res = $_SESSION['id_resultado'];

        // Actualizamos la tabla 'resultados' con dispositivo, valoración y comentarios
        $sql_up = "UPDATE resultados SET dispositivo = ?, comentarios_usuario = ?, propuestas = ?, valoracion = ?, completada = 1 WHERE id_resultado = ?";
        $stmt_up = $conn->prepare($sql_up);
        $stmt_up->bind_param("sssii", $_POST['dispositivo'], $_POST['comentarios_user'], $_POST['propuestas'], $_POST['valoracion'], $id_res);
        
        if ($stmt_up->execute()) {
            // Insertamos en la tabla 'observaciones' los comentarios del programador
            if (!empty($_POST['comentarios_prog'])) {
                $sql_obs = "INSERT INTO observaciones (id_resultado, comentarios) VALUES (?, ?)";
                $stmt_obs = $conn->prepare($sql_obs);
                $stmt_obs->bind_param("is", $id_res, $_POST['comentarios_prog']);
                $stmt_obs->execute();
            }
            $paso = 4;
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name ="author" content ="Yeray Rodríguez Granda" />
    <meta name ="description" content ="Pruebas de usabilidad" />
    <meta name ="keywords" content ="MotoGP, usabilidad" />
    <meta name ="viewport" content ="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
    <link rel="icon" type="image/png" href="multimedia/logo.png" />
    <title>MotoGP-Juegos</title>
</head>

<body>
    <main>
        <?php if ($paso == 1): ?>
            <section>
                <h2>Registro para Prueba de Usabilidad</h2>
                
                <?php if ($errorFormulario): ?>
                    <p>* Por favor, rellene todos los campos obligatorios.</p>
                <?php endif; ?>

                <form action="#" method="post">
                    <p>
                        <label for="profesion">Profesión:</label>
                        <input type="text" id="profesion" name="profesion" value="<?php echo isset($_POST['profesion']) ? $_POST['profesion'] : ''; ?>" required />
                    </p>

                    <p>
                        <label for="edad">Edad:</label>
                        <input type="number" id="edad" name="edad" min="1" max= '120' value="<?php echo isset($_POST['edad']) ? $_POST['edad'] : ''; ?>" required />
                    </p>

                    <fieldset>
                        <legend>Género</legend>
                        <label for="masc">Masculino</label> <input type="radio" name="genero" id="masc" value="Masculino" required />
                        <label for="fem">Femenino</label> <input type="radio" name="genero" id="fem" value="Femenino" />
                        <label for="otro">Otro</label> <input type="radio" name="genero" id="otro" value="Otro" />
                    </fieldset>

                    <p>
                        <label for="pericia">Pericia Informática (0 a 10):</label>
                        <input type="number" id="pericia" name="pericia" min="0" max="10" value="<?php echo isset($_POST['pericia']) ? $_POST['pericia'] : '5'; ?>" />
                    </p>

                    <input type="submit" name="enviar" value="Iniciar prueba" />
                </form>
            </section>
        <?php elseif ($paso == 2): ?>
            <section>
                <h2>Cuestionario de Usabilidad</h2>
                <p>Por favor, responda a las siguientes preguntas según lo observado en la web:</p>
                <form action="#" method="post">
                    
                    <p>
                        <label for="p1">1. ¿Cuál es la primera noticia que aparece en el inicio de la página?</label>
                        <textarea id="p1" name="respuestas[1]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p2">2. ¿Cuál es el nombre del piloto y su dorsal?</label>
                        <textarea id="p2" name="respuestas[2]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p3">3. ¿Cuál es el primer equipo del piloto?</label>
                        <textarea id="p3" name="respuestas[3]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p4">4. En el glosario de términos, ¿Qué es una pole?</label>
                        <textarea id="p4" name="respuestas[4]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p5">5. ¿Cuál es la temperatura del día 2025-06-05?</label>
                        <textarea id="p5" name="respuestas[5]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p6">6. ¿En alguna parte de la página podemos cargar archivos?</label>
                        <textarea id="p6" name="respuestas[6]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p7">7. ¿Quién es el ganador de la carrera del día?</label>
                        <textarea id="p7" name="respuestas[7]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p8">8. ¿Quién es el líder en la clasificación?</label>
                        <textarea id="p8" name="respuestas[8]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p9">9. ¿Cuál es el tiempo del ganador?</label>
                        <textarea id="p9" name="respuestas[9]" rows="2"  required></textarea>
                    </p>

                    <p>
                        <label for="p10">10. ¿Cuántas cartas tiene el juego de memoria?</label>
                        <textarea id="p10" name="respuestas[10]" rows="2"  required></textarea>
                    </p>

                    <input type="submit" name="enviar" value="Finalizar" />
                </form>
            </section>
        <?php elseif ($paso == 3): ?>
            <section>
                <h2>Paso 3: Informe Final y Observaciones</h2>
                <form action="#" method="post">
                    <p>
                        <label for="dispositivo">Dispositivo utilizado:</label>
                        <select name="dispositivo" id="dispositivo" required>
                            <option value="" disabled selected>Seleccione un dispositivo...</option>
                            <option value="Ordenador">Ordenador</option>
                            <option value="Tableta">Tableta</option>
                            <option value="Teléfono">Teléfono</option>
                        </select>
                    </p>

                    <p>
                        <label>Comentarios del Usuario:</label>
                        <textarea name="comentarios_user" rows="3"></textarea>
                    </p>

                    <p>
                        <label>Propuestas de mejora:</label>
                        <textarea name="propuestas" rows="3"></textarea>
                    </p>

                    <p>
                        <label>Valoración de la aplicación (1-10):</label>
                        <input type="number" name="valoracion" min="1" max="10" value="5" />
                    </p>

                    <p>
                        <label>Comentarios del Programador (Observaciones Técnicas):</label>
                        <textarea name="comentarios_prog" rows="4"></textarea>
                    </p>

                    <input type="submit" name="enviar" value="Enviar Informe Final" />
                </form>
            </section>
        <?php elseif ($paso == 4): ?>
            <section>
                <h2>¡Muchas gracias!</h2>
                <p>Todos los datos, incluyendo el tiempo registrado y las observaciones del programador, han sido guardados correctamente.</p>
                <p><a href="formulario.php">Volver al inicio</a></p>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>