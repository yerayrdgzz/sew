
<!DOCTYPE HTML>

<html lang="es">
<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <meta name ="author" content ="Yeray Rodríguez Granda" />
    <meta name ="description" content ="Las clasificaciones de MotoGP" />
    <meta name ="keywords" content ="Clasificaciones, MotoGP, Podio, Ganadores" />
    <meta name ="viewport" content ="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <link rel="icon" type="image/png" href="multimedia/logo.png" />
    <title>MotoGP-Clasificaciones</title>
</head>

<body>
    <!-- Datos con el contenidos que aparece en el navegador -->
    <header>
        <h1><a href="index.html">MotoGP Desktop</a></h1>
        <nav>
            <a href="index.html">Inicio</a> 
            <a href="piloto.html">Piloto</a>
            <a href="circuito.html">Circuito</a>
            <a href="meteorologia.html">Meteorología</a>
            <a href="clasificaciones.php" class="active">Clasificaciones</a>
            <a href="juegos.html">Juegos</a>
            <a href="ayuda.html">Ayuda</a>
            <a href="cronometro.php">Cronómetro PHP</a>
        </nav>
    </header>
    <p>Estás en: <a href="index.html">Inicio</a> >> <strong>Clasificaciones</strong></p>
    <h2>Las clasificaciones de MotoGP</h2>
    
<?php
// Iniciar la sesión al principio del script
session_start();

class Clasificacion
{
    private string $documento;

    public function __construct()
    {
        $this->documento = "C:/xampp/htdocs/MotoGP-Desktop/xml/circuitoEsquema.xml";
    }

    public function consultar(): void
    {
        // Usando el valor del atributo documento, lee el contenido del archivo.
        $datos = file_get_contents($this->documento);
        
        if ($datos === false || $datos === null) {
            echo "<h3>Error: No se pudo leer el archivo XML en la ruta: " . htmlspecialchars($this->documento) . "</h3>";
            return;
        }

        // Se convierte el string en un objeto XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($datos);
        libxml_clear_errors();
        
        if ($xml === false) {
             echo "<h3>Error: No se pudo convertir el XML a objeto SimpleXMLElement.</h3>";
             return;
        }
        
        // Extracción de datos del campeón (<champion><name> y <champion><duration>)
        $nombreGanador = (string)$xml->champion->name;
        $tiempoGanador = (string)$xml->champion->duration;

        preg_match('/PT(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?/', $tiempoGanador, $m);
        $minutos = isset($m[1]) ? intval($m[1]) : 0;
        $segundos_float = isset($m[2]) ? floatval($m[2]) : 0.0;

        $segundos = floor($segundos_float);
        $milis = round(($segundos_float - $segundos) * 1000);

        $tiempo_formateado = sprintf('%02d:%02d.%2d', $minutos, $segundos, $milis);

        $clasificados = [];

        foreach ($xml->classifieds->classified as $item) {
            $clasificados[] = [
                'position' => (string)$item['position'],  // atributo
                'name'     => (string)$item              // contenido del nodo
            ];
        }

        // Estructura HTML simple para mostrar la información
        $output = "
        <section>
            <h3>Información del Ganador</h3>
            <ul>
            <li>El nombre del ganador de la carrera es: " . htmlspecialchars($nombreGanador) . "</li>
            <li>El tiempo empleado para ello fue: " . htmlspecialchars($tiempo_formateado) . "S</li>
            </ul>
        </section>
        <section>
            <h4>Clasificación de la carrera</h4>
            <ol>
            <li>" . htmlspecialchars($clasificados[0]['name']) . "</li>
            <li>" . htmlspecialchars($clasificados[1]['name']) . "</li>
            <li>" . htmlspecialchars($clasificados[2]['name']) . "</li>
            </ul>
        </section>";



        echo $output;
    }

}
$clasificacion = new Clasificacion();
$clasificacion->consultar();
?>
</body>
</html>