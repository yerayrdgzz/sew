import xml.etree.ElementTree as ET

class SVG:
    def __init__(self, ruta_xml, salida_svg="xml/altimetria.svg"):
        # Parámetros del SVG
        self.SVG_WIDTH = 820
        self.SVG_HEIGHT = 350
        self.PADDING = 10
        self.GRAPH_WIDTH = 735

        self.ruta_xml = ruta_xml
        self.salida_svg = salida_svg

        # Datos que se llenarán al leer XML
        self.altitudes = []
        self.ejex = []

        # Leer XML y calcular puntos
        self.leer_xml()
        self.calcular_puntos()

    def leer_xml(self):
        """Carga las altitudes y eje X desde el XML"""
        try:
            arbol = ET.parse(self.ruta_xml)
            raiz = arbol.getroot()

            eje_x = 0
            for tramo in raiz.findall('{*}sections/{*}section'):
                altitud = float(tramo.find('{*}coordinates/{*}altitude').text)
                self.altitudes.append(altitud)
                self.ejex.append(eje_x)
                eje_x += 15  # distancia en X entre tramos
        except Exception as e:
            print("Error al leer XML:", e)

    def calcular_puntos(self):
        """Normaliza altitudes y calcula coordenadas para el SVG"""
        self.puntos = []
        if not self.altitudes:
            return

        self.max_altitud = max(self.altitudes)
        self.min_altitud = min(self.altitudes)

        rango_base = 20
        rango_superior = self.SVG_HEIGHT - 2.5 * self.PADDING

        for altitud, x in zip(self.altitudes, self.ejex):
            y_normalizado = (altitud - self.min_altitud) / (self.max_altitud - self.min_altitud) * (rango_superior - rango_base)
            y = rango_base + y_normalizado
            y = self.SVG_HEIGHT - self.PADDING - y
            self.puntos.append(f"{x + self.PADDING},{y}")

    def generar_eje_y(self, step=5):
        """Genera líneas y etiquetas del eje Y"""
        y_axis_marks = []
        if not self.altitudes:
            return ""

        alt_min_round = int(self.min_altitud // step * step)
        alt_max_round = int((self.max_altitud // step + 1) * step)

        rango_base = 20
        rango_superior = self.SVG_HEIGHT - 2.5 * self.PADDING

        for alt in range(alt_min_round, alt_max_round, step):
            y_normalizado = (alt - self.min_altitud) / (self.max_altitud - self.min_altitud) * (rango_superior - rango_base)
            y = self.SVG_HEIGHT - self.PADDING - (rango_base + y_normalizado)
            y_axis_marks.append(
                f'<line x1="{self.PADDING}" y1="{y}" x2="{self.GRAPH_WIDTH + self.PADDING}" y2="{y}" stroke="#ccc" stroke-width="1" />'
                f'<text x="{self.GRAPH_WIDTH + self.PADDING + 10}" y="{y + 4}" font-size="12" text-anchor="start">{alt} m</text>'
            )
        return ''.join(y_axis_marks)

    def generar_svg(self):
        """Genera el contenido SVG y lo guarda en archivo"""
        # Calcular ancho del marco según última X de la línea
        if self.puntos:
            ultima_x = float(self.puntos[-1].split(",")[0])
            rect_width = ultima_x - self.PADDING
        else:
            rect_width = self.GRAPH_WIDTH - self.PADDING

        # Generar marcas del eje Y ajustadas al nuevo ancho
        y_axis_marks = []
        if self.altitudes:
            step = 5  # cada 5 m, puedes cambiarlo
            alt_min_round = int(self.min_altitud // step * step)
            alt_max_round = int((self.max_altitud // step + 1) * step)
            rango_base = 20
            rango_superior = self.SVG_HEIGHT - 2.5 * self.PADDING

            for alt in range(alt_min_round, alt_max_round, step):
                y_normalizado = (alt - self.min_altitud) / (self.max_altitud - self.min_altitud) * (rango_superior - rango_base)
                y = self.SVG_HEIGHT - self.PADDING - (rango_base + y_normalizado)
                y_axis_marks.append(
                    f'<line x1="{self.PADDING}" y1="{y}" x2="{rect_width+10}" y2="{y}" stroke="#ccc" stroke-width="1" />'
                    f'<text x="{rect_width + 15}" y="{y + 4}" fill="white" font-size="12" text-anchor="start">{alt} m</text>'
                )

        svg_content = f"""<?xml version="1.0" encoding="UTF-8"?>
        <svg xmlns="http://www.w3.org/2000/svg" width="{self.SVG_WIDTH}" height="{self.SVG_HEIGHT}" viewBox="0 0 {self.SVG_WIDTH} {self.SVG_HEIGHT}">
            <!-- Líneas y etiquetas del eje Y -->
            {''.join(y_axis_marks)}

            <!-- Polilínea de altitud -->
            <polyline style="fill:none;stroke:red;stroke-width:4" points="{' '.join(self.puntos)}" />

            <!-- Marco del gráfico -->
            <rect x="{self.PADDING}" y="{self.PADDING}" width="{rect_width}" height="{self.SVG_HEIGHT - 2 * self.PADDING}" style="fill:none;stroke:red;stroke-width:2" />

            <!-- Etiqueta del eje Y (vertical a la derecha del marco) -->
            <text x="{rect_width + 45}" y="{(self.SVG_HEIGHT / 2) + 30}" font-size="14" fill="white" text-anchor="middle" transform="rotate(-90 {rect_width + 45},{self.SVG_HEIGHT / 2})">Altitud (m)</text>
        </svg>"""

        try:
            with open(self.salida_svg, "w", encoding="utf-8") as f:
                f.write(svg_content)
            print(f"SVG generado con éxito: {self.salida_svg}")
        except Exception as e:
            print("Error al guardar SVG:", e)



# Uso
if __name__ == "__main__":
    svg = SVG("xml/circuitoEsquema.xml")
    svg.generar_svg()
