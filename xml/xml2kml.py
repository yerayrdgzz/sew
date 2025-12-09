# -*- coding: utf-8 -*-

import xml.etree.ElementTree as ET

class CircuitoKML:

    def __init__(self, xml_path):
        self.xml_path = xml_path
        self.tree = ET.parse(xml_path)
        self.root = self.tree.getroot()

        self.kml = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
        self.doc = ET.SubElement(self.kml, 'Document')

        self.nombre = self.root.find('{*}name').text
        self.localidad = self.root.find('{*}locality').text
        self.pais = self.root.find('{*}country').text

    def _extraer_punto_base(self):
        """Obtiene las coordenadas principales del circuito."""
        coords = self.root.find('{*}coordinates')
        lon = float(coords.find('{*}longitude').text)
        lat = float(coords.find('{*}latitude').text)
        alt = float(coords.find('{*}altitude').text)
        return lon, lat, alt

    def _extraer_secciones(self):
        """Devuelve una lista con todas las coordenadas de las secciones."""
        coords_list = []
        for section in self.root.findall('.//{*}section'):
            c = section.find('{*}coordinates')
            lon = float(c.find('{*}longitude').text)
            lat = float(c.find('{*}latitude').text)
            alt = float(c.find('{*}altitude').text)
            coords_list.append((lon, lat, alt))
        return coords_list

    def crear_placemark_principal(self):
        """Añade el punto base del circuito al KML."""
        lon, lat, alt = self._extraer_punto_base()
        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = self.nombre
        ET.SubElement(pm, 'description').text = f"{self.localidad}, {self.pais}"
        punto = ET.SubElement(pm, 'Point')
        ET.SubElement(punto, 'coordinates').text = f"{lon},{lat},{alt}"
        ET.SubElement(punto, 'altitudeMode').text = "clampToGround"

    def crear_linea_pista(self):
        """Crea la línea que representa la pista."""
        coords_list = self._extraer_secciones()
        lista_texto = "\n".join([f"{lon},{lat},{alt}" for lon, lat, alt in coords_list])

        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = f"Planimetría de {self.nombre}"
        ls = ET.SubElement(pm, 'LineString')
        ET.SubElement(ls, 'extrude').text = "1"
        ET.SubElement(ls, 'tessellation').text = "1"
        ET.SubElement(ls, 'coordinates').text = lista_texto
        ET.SubElement(ls, 'altitudeMode').text = "clampToGround"

        estilo = ET.SubElement(pm, 'Style')
        linea = ET.SubElement(estilo, 'LineStyle')
        ET.SubElement(linea, 'color').text = "#ff0000ff"  # rojo opaco
        ET.SubElement(linea, 'width').text = "3"

    def guardar(self, nombre_salida):
        """Guarda el archivo KML en disco."""
        ET.indent(self.kml)
        arbol = ET.ElementTree(self.kml)
        arbol.write(nombre_salida, encoding='utf-8', xml_declaration=True)
        print(f"KML generado correctamente: {nombre_salida}")

    def generar_planimetria(self, salida_kml):
        """Crea el archivo KML completo de la planimetría."""
        self.crear_placemark_principal()
        self.crear_linea_pista()
        self.guardar(salida_kml)


# Ejemplo de uso directo
if __name__ == "__main__":
    circuito_xml = "xml/circuitoEsquema.xml"
    salida_kml = "xml/circuito.kml"
    planimetria = CircuitoKML(circuito_xml)
    planimetria.generar_planimetria(salida_kml)
