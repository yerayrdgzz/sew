import xml.etree.ElementTree as ET
import re

class Html:
    def __init__(self, ruta_xml, salida_html="xml/InfoCircuito.html"):
        self.ruta_xml = ruta_xml
        self.salida_html = salida_html

        # Atributos que se llenarán al leer el XML
        self.nombre = ""
        self.longitud = ""
        self.unidad_longitud = ""
        self.anchura = ""
        self.unidad_anchura = ""
        self.fecha = ""
        self.hora = ""
        self.vueltas = ""
        self.localidad = ""
        self.pais = ""
        self.referencias = []
        self.fotos = []
        self.videos = []
        self.clasificados = []
        self.nombre_campeon = ""
        self.duracion_campeon = ""
        self.duracion_legible = ""

        # Leer XML y cargar datos
        self.leer_xml()
    
    def leer_xml(self):
        try:
            arbol = ET.parse(self.ruta_xml)
            raiz = arbol.getroot()

            # Datos generales
            self.nombre = raiz.find("{*}name").text
            self.longitud = raiz.findtext("{*}length")
            self.unidad_longitud = raiz.find("{*}length").get("units") if raiz.find("{*}length") is not None else ""
            self.anchura = raiz.findtext("{*}width")
            self.unidad_anchura = raiz.find("{*}width").get("units") if raiz.find("{*}width") is not None else ""
            self.fecha = raiz.findtext("{*}date")
            self.hora = raiz.findtext("{*}hour")
            self.vueltas = raiz.findtext("{*}turns")
            self.localidad = raiz.findtext("{*}locality")
            self.pais = raiz.findtext("{*}country")

            # Multimedia
            self.referencias = [r.text for r in raiz.findall(".//{*}reference")]
            self.fotos = [f.text.replace("'", "") for f in raiz.findall(".//{*}image")]
            self.videos = [v.text.replace("'", "") for v in raiz.findall(".//{*}video")]

            # Campeón
            self.nombre_campeon = raiz.findtext("{*}champion/{*}name")
            self.duracion_campeon = raiz.findtext("{*}champion/{*}duration")
            self.duracion_legible = self.formatear_duracion_iso(self.duracion_campeon)

            # Clasificados
            self.clasificados = [c.text for c in raiz.findall(".//{*}classified")]

        except Exception as e:
            print("Error al leer XML:", e)
    
    def formatear_duracion_iso(self, duracion_iso):
        """Convierte duración ISO 8601 (PT41M11.95S) a MM:SS.ss"""
        if not duracion_iso:
            return ""
        match = re.match(r'PT(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?', duracion_iso)
        if match:
            minutos = int(match.group(1)) if match.group(1) else 0
            segundos = float(match.group(2)) if match.group(2) else 0.0
            return f"{minutos:02d}:{segundos:05.2f}"
        return duracion_iso

    def generar_html(self):
        """Construye y guarda el archivo HTML"""
        try:
            html = f"""<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>{self.nombre}</title>
                <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
            </head>
            <body>
                <h2>Datos del Circuito</h2>
                <table>
                    <tr><th>Nombre</th><td>{self.nombre}</td></tr>
                    <tr><th>Longitud</th><td>{self.longitud} {self.unidad_longitud}</td></tr>
                    <tr><th>Anchura</th><td>{self.anchura} {self.unidad_anchura}</td></tr>
                    <tr><th>Fecha</th><td>{self.fecha}</td></tr>
                    <tr><th>Hora</th><td>{self.hora}</td></tr>
                    <tr><th>Vueltas</th><td>{self.vueltas}</td></tr>
                    <tr><th>Localidad</th><td>{self.localidad}</td></tr>
                    <tr><th>País</th><td>{self.pais}</td></tr>
                </table>

                <h3>Referencias</h3>
                <ul>
                    {"".join(f"<li><a href='{r}' target='_blank'>{r}</a></li>" for r in self.referencias)}
                </ul>

                <h3>Galería de Fotos</h3>
                {"".join(f'<img src="C:/xampp/htdocs/MotoGP-Desktop/{f}" alt="Foto del circuito">' for f in self.fotos)}

                <h3>Videos del Circuito</h3>
                {"".join(f'<video controls><source src="C:/xampp/htdocs/MotoGP-Desktop/{v}" type="video/mp4"></video>' for v in self.videos)}

                <h3>Clasificados</h3>
                <ol>
                    {"".join(f"<li>{c}</li>" for c in self.clasificados)}
                </ol>

                <h3>Campeón</h3>
                <p>Nombre: {self.nombre_campeon}</p>
                <p>Duración: {self.duracion_legible}</p>
            </body>
            </html>
            """
            with open(self.salida_html, "w", encoding="utf-8") as f:
                f.write(html)

            print(f"Archivo HTML generado con éxito: {self.salida_html}")
        except Exception as e:
            print("Error al generar HTML:", e)


# Uso
if __name__ == "__main__":
    h = Html("xml/circuitoEsquema.xml")
    h.generar_html()
