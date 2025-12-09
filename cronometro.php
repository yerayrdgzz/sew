<?php
// Iniciar la sesión al principio del script
session_start();

class Cronometro
{
    private float $tiempo;
    private float $inicio;

    public function __construct(float $tiempo = 0.0, float $inicio = 0.0)
    {
        $this->tiempo = $tiempo;
        $this->inicio = $inicio;
    }

    public function arrancar(): void
    {
        // Solo arranca si no estaba ya en marcha (inicio == 0)
        if ($this->inicio === 0.0) {
            $this->inicio = microtime(true);
            $this->tiempo = 0.0;
        }
    }

    public function parar(): void
    {
        if ($this->inicio !== 0.0) {
            $fin = microtime(true);

            $transcurrido = $fin - $this->inicio;

            $this->tiempo += $transcurrido;
            
            $this->inicio = 0.0;
        }
    }

    public function getTiempo(): float
    {
        // Esto es necesario para guardar el estado
        return $this->tiempo;
    }

    public function getInicio(): float
    {
        // Esto es necesario para guardar el estado
        return $this->inicio;
    }

    public function mostrar(): string
    {
        $totalSegundos = $this->tiempo;
        
        // Si está arrancado, le sumamos el tiempo actual transcurrido
        if ($this->inicio !== 0.0) {
            $tiempo_actual = microtime(true) - $this->inicio;
            $totalSegundos += $tiempo_actual;
        }

        $minutos = floor($totalSegundos / 60);

        $segundos = $totalSegundos - ($minutos * 60);

        return sprintf("%02d:%04.1f", $minutos, $segundos);
    }
}


$mensaje = "Pulsa un botón";

if (isset($_SESSION['cronometro_tiempo']) && isset($_SESSION['cronometro_inicio'])) {
    $crono = new Cronometro($_SESSION['cronometro_tiempo'], $_SESSION['cronometro_inicio']);
} else {
    $crono = new Cronometro();
}

if ($_POST) {
    if (isset($_POST['arrancar'])) {
        $crono->arrancar();
        $mensaje = "Cronómetro **ARRANCADO**";
    }
    if (isset($_POST['parar'])) {
        $crono->parar();
        $mensaje = "Cronómetro **PARADO**";
    }
    
    $_SESSION['cronometro_tiempo'] = $crono->getTiempo();
    $_SESSION['cronometro_inicio'] = $crono->getInicio();
    
    if (isset($_POST['mostrar'])) {
        $mensaje = "Tiempo: **" . $crono->mostrar() . "**";
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
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <link rel="icon" type="image/png" href="multimedia/logo.png" />
    <title>MotoGP</title>
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
            <a href="cronometro.php" class="active">Cronómetro PHP</a>
        </nav>
    </header>
    <main>
        <h2>Cronómetro</h2>
        <form action="#" method="post">
            <input type="submit" name="arrancar" value="Arrancar">
            <input type="submit" name="parar" value="Parar">
            <input type="submit" name="mostrar" value="Mostrar tiempo">
        </form>
        <h3>Resultado</h3>
        <p><?php echo $mensaje; ?></p>
    </main>
</body>
</html>