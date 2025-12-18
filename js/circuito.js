"use strict";

class CargadorKML {
    #entradaKML;

    constructor() {
        this.#entradaKML = document.querySelector("main input:first-of-type");
        if (this.#entradaKML) {
            this.#entradaKML.addEventListener('change', (event) => {
                this.#leerArchivoKML(event.target.files);
            });
        }
    }

    #leerArchivoKML(files) {
        const archivo = files && files[0];
        if (!archivo) return;
        const lector = new FileReader();
        lector.onload = (e) => this.#insertarCapaKML(e.target.result);
        lector.readAsText(archivo);
    }

    #insertarCapaKML(kmlContent) {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(kmlContent, 'application/xml');
        const coordinates = xmlDoc.getElementsByTagName('coordinates');

        const circuitPoints = [];
    
        for (let i = 0; i < coordinates.length; i++) {
            const coordsText = coordinates[i].textContent.trim();
            const coordsArray = coordsText.split(/\s+/).map(coord => {
                const [longitude, latitude] = coord.split(',').map(Number);
                return { lat: latitude, lng: longitude };
            });
                    
            circuitPoints.push(...coordsArray);
        }

        const divMap = document.createElement("div");
        const sectionToAppend = document.querySelector("section:first-of-type");
        sectionToAppend.appendChild(divMap);
    
        var mapaGeoposicionado = new google.maps.Map(divMap, {
            center: new google.maps.LatLng(41.077869,  -0.204329), 
            zoom: 14, 
            mapTypeId: 'terrain'
        });



        var marcador01 = new google.maps.Marker({
          position: new google.maps.LatLng(circuitPoints[0].lat, circuitPoints[0].lng),
          map: mapaGeoposicionado,
          title: 'Salida'
      });
    
        const circuitPath = new google.maps.Polyline({
            path: circuitPoints,
            geodesic: true,
            strokeColor: "#FF0000", 
            strokeOpacity: 1.0,
            strokeWeight: 3
        });

        circuitPath.setMap(mapaGeoposicionado); 
    }
    
}

class CargadorSVG {
    #entradaSVG;

    constructor() {
        this.#entradaSVG = document.querySelector("main section:nth-of-type(2) input");
        if (this.#entradaSVG) {
            this.#entradaSVG.addEventListener('change', (event) => {
                this.#leerArchivoSVG(event.target.files);
            });
        }
    }

    #leerArchivoSVG(files) {
        const archivo = files && files[0];
        if (!archivo) return;
        const lector = new FileReader();
        lector.onload = (e) => this.#insertarSVG(e.target.result);
        lector.readAsText(archivo);
    }

    #insertarSVG(contenidoTexto) {
        const parser = new DOMParser();
        const documentoSVG = parser.parseFromString(contenidoTexto, 'image/svg+xml');
        const elementoSVG = documentoSVG.documentElement;

        const svgAnterior = document.querySelector('main section:nth-of-type(2) svg');
        if (svgAnterior) svgAnterior.remove();

        const section = document.querySelector('main section:nth-of-type(2)');
        section.appendChild(elementoSVG);
    }
}

class Circuito {
    #h2;
    #nombre;
    #longitud;
    #anchura;
    #fecha;
    #hora;
    #vueltas;
    #localidad;
    #pais;
    #h3_referencias;
    #referencias = [];
    #h3_fotos;
    #fotos = [];
    #h3_video;
    #video;
    #h3_clasificados;
    #clasificados = [];
    #h3_campeon;
    #campeon = {};

    constructor() {
        const input = document.querySelector('main section:nth-of-type(3) input');
        if (input) input.addEventListener('change', (event) => { this.#leerArchivoHTML(event.target.files); });
        this.#comprobarApiFile();
    }

    #comprobarApiFile() {
        if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
            const section = document.querySelector("main section:nth-of-type(3)");
            const p = document.createElement("p");
            p.textContent = "¡¡¡Este navegador NO soporta el API File y este programa puede no funcionar correctamente !!!";
            section.appendChild(p);
            return;
        }
    }

    #leerArchivoHTML(files) {
        const archivo = files && files[0];
        if (!archivo) return;
        const lector = new FileReader();
        lector.onload = (evento) => {
            const contenido = evento.target.result;
            this.#procesarHTML(contenido);
            this.#mostrarHTML();
        };
        lector.readAsText(archivo);
    }

    #procesarHTML(htmlString) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlString, 'text/html');
        this.#h2 = doc.querySelector('h2');

        const tablaDatos = doc.querySelector('table');
        if (tablaDatos) {
            const filas = tablaDatos.querySelectorAll('tr');
            filas.forEach(fila => {
                const cabecera = fila.querySelector('th')?.textContent.trim();
                const valor = fila.querySelector('td')?.textContent.trim();
                switch (cabecera) {
                    case 'Nombre': this.#nombre = valor; break;
                    case 'Longitud': this.#longitud = valor; break;
                    case 'Anchura': this.#anchura = valor; break;
                    case 'Fecha': this.#fecha = valor; break;
                    case 'Hora': this.#hora = valor; break;
                    case 'Vueltas': this.#vueltas = parseInt(valor); break;
                    case 'Localidad': this.#localidad = valor; break;
                    case 'País': this.#pais = valor; break;
                }
            });
        }

        this.#h3_referencias = doc.querySelector('h3:has(+ ul)');
        this.#referencias = {};
        const enlacesRefs = doc.querySelectorAll('h3 + ul a');
        enlacesRefs.forEach(a => {
            const nombrePagina = a.textContent.trim();
            const urlPagina = a.href;
            
            this.#referencias[nombrePagina] = urlPagina;
        });

        this.#h3_fotos = doc.querySelector('h3:has(+ img)');
        this.#fotos = [];
        const imagenes = doc.querySelectorAll('img');
        imagenes.forEach(img => { if (img.tagName === 'IMG') this.#fotos.push(img.src); });

        this.#h3_video = doc.querySelector('h3:has(+ video)');
        const videoSource = doc.querySelector('video source');
        this.#video = videoSource ? videoSource.src : null;

        this.#h3_clasificados = doc.querySelector('h3:has(+ ol)');
        this.#clasificados = [];
        const clasificadosItems = doc.querySelectorAll('h3 +ol li');
        clasificadosItems.forEach(li => this.#clasificados.push(li.textContent.trim()));

        this.#h3_campeon = doc.querySelector('h3:has(+ p)');
        this.#campeon = {};
        const parrafosCampeon = doc.querySelectorAll('h3 ~ p');
        parrafosCampeon.forEach(p => {
            const texto = p.textContent.trim();
            const partes = texto.split(': ');
            if (partes.length === 2) {
                const clave = partes[0].trim().toLowerCase();
                const valor = partes[1].trim();
                if (clave === 'nombre') this.#campeon.nombre = valor;
                if (clave === 'duración') this.#campeon.duracion = valor;
            }
        });
    }

    #mostrarHTML() {
        const section = document.querySelector('main section:nth-of-type(3)');

        let htmlDatos = `
            <h3>${this.#h2 ? this.#h2.textContent : ''}</h3>
            <ul>
                <li>Longitud: ${this.#longitud || ''}</li>
                <li>Anchura: ${this.#anchura || ''}</li>
                <li>Fecha: ${this.#fecha || ''} (${this.#hora || ''})</li>
                <li>Vueltas: ${this.#vueltas || ''}</li>
                <li>Ubicación: ${this.#localidad || ''}, ${this.#pais || ''}</li>
            </ul>
        `;

        // Comprobamos si el objeto tiene al menos una clave (equivalente a length > 0)
        if (Object.keys(this.#referencias).length > 0) {
            htmlDatos += `
                <h3>${this.#h3_referencias ? this.#h3_referencias.textContent : 'Referencias'}</h3>
                <ul>
                    ${Object.entries(this.#referencias).map(([nombre, url]) => 
                        `<li><a href="${url}">${nombre}</a></li>`
                    ).join('')}
                </ul>
            `;
        }

        if (this.#fotos.length > 0) {
            htmlDatos += `
                <h3>${this.#h3_fotos ? this.#h3_fotos.textContent : 'Fotos'}</h3>
                ${this.#fotos.map(src => `<img src="${src}" alt="Foto del circuito">`).join('')}
            `;
        }

        if (this.#video) {
            htmlDatos += `
                <h3>${this.#h3_video ? this.#h3_video.textContent : 'Video'}</h3>
                <video controls>
                    <source src="${this.#video}" type="video/mp4">
                    Tu navegador no soporta el elemento de video.
                </video>
            `;
        }

        if (this.#clasificados.length > 0) {
            htmlDatos += `
                <h3>${this.#h3_clasificados ? this.#h3_clasificados.textContent : 'Clasificados'}</h3>
                <ol>${this.#clasificados.map(c => `<li>${c}</li>`).join('')}</ol>
            `;
        }

        if (this.#campeon.nombre) {
            htmlDatos += `
                <h3>${this.#h3_campeon ? this.#h3_campeon.textContent : 'Campeón'}</h3>
                <p>Nombre: ${this.#campeon.nombre}</p>
                <p>Duración: ${this.#campeon.duracion || ''}</p>
            `;
        }

        section.insertAdjacentHTML('beforeend', htmlDatos);
    }
}