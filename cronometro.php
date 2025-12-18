<?php
// Iniciar la sesión al principio del script
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

    public function getTiempo(): float { return $this->tiempo; }
    public function getInicio(): float { return $this->inicio; }

    public function mostrar(): string
    {
        $totalSegundos = $this->tiempo;
        if ($this->inicio !== 0.0) {
            $totalSegundos += (microtime(true) - $this->inicio);
        }
        $minutos = floor($totalSegundos / 60);
        $segundos = $totalSegundos - ($minutos * 60);
        return sprintf("%02d:%04.1f", $minutos, $segundos);
    }
}

// Lógica de procesamiento de estado
if (isset($_SESSION['cronometro_tiempo']) && isset($_SESSION['cronometro_inicio'])) {
    $crono = new Cronometro($_SESSION['cronometro_tiempo'], $_SESSION['cronometro_inicio']);
} else {
    $crono = new Cronometro();
}

$mensaje = "Pulsa un botón";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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