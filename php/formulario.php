<?php

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
            <p>Pregunta 1: </p>
            <p>
                <input type="text" name="1" required>
                <span></span>
            </p>
            <p>Pregunta 2: </p>
            <p>
                <input type="text" name="2" required>
                <span></span>
            </p>
            <p>Pregunta 3: </p>
            <p>
                <input type="text" name="3" required>
                <span></span>
            </p>
            <p>Pregunta 4: </p>
            <p>
                <input type="text" name="4" required>
                <span></span>
            </p>
            <p>Pregunta 5: </p>
            <p>
                <input type="text" name="5" required>
                <span></span>
            </p>
            <p>Pregunta 6: </p>
            <p>
                <input type="text" name="6" required>
                <span></span>
            </p>
            <p>Pregunta 7: </p>
            <p>
                <input type="text" name="7" required>
                <span></span>
            </p>
            <p>Pregunta 8: </p>
            <p>
                <input type="text" name="8" required>
                <span></span>
            </p>
            <p>Pregunta 9: </p>
            <p>
                <input type="text" name="9" required>
                <span></span>
            </p>
            <p>Pregunta 10: </p>
            <p>
                <input type="text" name="10" required>
                <span></span>
            </p>
            <p>
                <input type="submit" name="enviar">
                <span></span>
            </p>
        </form>

        <?php if (!empty($mensaje)) echo "<p>$mensaje</p>"; ?>
    </main>
</body>
</html>