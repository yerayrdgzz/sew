<?php include 'cronometro.php'; ?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name ="author" content ="Yeray Rodríguez Granda" />
    <meta name ="description" content ="Cronómetro para medir el tiempo" />
    <meta name ="keywords" content ="MotoGP, Tiempo, segundos, Arrancar" />
    <meta name ="viewport" content ="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <link rel="icon" type="image/png" href="multimedia/logo.png" />
    <title>MotoGP-Cronómetro</title>
</head>
<body>
    <header>
        <h1><a href="index.html">MotoGP Desktop</a></h1>
        <nav>
            <a href="index.html">Inicio</a> 
            <a href="piloto.html">Piloto</a>
            <a href="circuito.html">Circuito</a>
            <a href="meteorologia.html">Meteorología</a>
            <a href="clasificaciones.php">Clasificaciones</a>
            <a href="juegos.html">Juegos</a>
            <a href="ayuda.html">Ayuda</a>
        </nav>
    </header>
    <p>Estás en: <a href="index.html">Inicio</a> >> <a href="juegos.html">Juegos</a> >> <strong>Cronómetro PHP</strong></p>
    <main>
        <h2>Cronómetro</h2>
        <p> Funcionalidades disponsible para el cronómetro: </p>
        <!-- Decidí no poner labels ya que parecio una interfaz mas limpia-->
        <form action="#" method="post">
            <input type="submit" name="arrancar" value="Arrancar">
            <input type="submit" name="parar" value="Parar">
            <input type="submit" name="mostrar" value="Mostrar tiempo">
        </form>
        <h3>Resultado</h3>
        <p> <?php echo $mensaje; ?></p>
    </main>
</body>
</html>