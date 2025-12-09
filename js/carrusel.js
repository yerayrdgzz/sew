"use strict";

class Carrusel{
    #busqueda;
    #actual;
    #maximo;
    #fotografias

    constructor(){
        this.#busqueda = "MotoGP Aragon";
        this.#actual = 0;
        this.#maximo = 4;

        this.#getFotografias();
    }

    #getFotografias(){
        $.ajax({
            dataType: "json",
            url: `https://api.flickr.com/services/feeds/photos_public.gne?tags=${this.#busqueda}&format=json&jsoncallback=?&tagmode=all`,
            method: "GET",
            success: (datos) => {
                let fotosProcesadas = datos.items.map(item => {
                    let urlOriginal = item.media.m; 
                    
                    let url640 = urlOriginal.replace('_m.jpg', '_z.jpg');
                    
                    return {
                        titulo: item.title,
                        url: url640
                    };
                });
                this.#procesarJSONFotografias(fotosProcesadas);
            },
            error: (xhr, status, error) => {
                console.error("Error al obtener fotografías:", status, error);
            }
        });
    }

    #procesarJSONFotografias(fotosProcesadas) {
        this.#fotografias = fotosProcesadas.slice(0, this.#maximo);

        this.#mostrarFotografias();
    }

    #mostrarFotografias() {
        const fotografiaInicial = this.#fotografias[this.#actual];
        const encabezado = $('<h2>').text(`Imágenes del circuito de ${this.#busqueda}`);
        const imagen = $('<img>', {
            src: fotografiaInicial.url,
            alt: fotografiaInicial.titulo 
        });
        const articulo = $('<article>:first').append(encabezado).append(imagen);
        
        $('main').append(articulo);

        const tiempoEnMilisegundos = 3000; // 3 segundos 
        const metodoLigado = this.#cambiarFotografia.bind(this);

        setInterval(metodoLigado, tiempoEnMilisegundos);
    }

    #cambiarFotografia() {
        this.#actual++;

        if (this.#actual >= this.#maximo) {
            this.#actual = 0; 
        }
        const fotografiaActual = this.#fotografias[this.#actual];

        // Actualizar la imagen <img>
        $('main article:first img').attr({
            'src': fotografiaActual.url,
            'alt': fotografiaActual.titulo
        });
    }
}