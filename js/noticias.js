"use strict";

class Noticias{
    #busqueda;
    #url;

    constructor(){
        this.#busqueda = "MotoGP";
        this.#url = "https://api.thenewsapi.com/v1/news/all";
    }

    async buscar(){
        let apiKey = "tskP9O3HAnYTTq6zvjOsLUXslbNzo9aRk1M32FBD"
        const urlAPI = `${this.#url}?api_token=${apiKey}&language=es&search=${this.#busqueda}&limit=3`
        
        try{
            const respuesta = await fetch(urlAPI);
            if (!respuesta.ok) throw new Error('Ciudad no encontrada');
            const datos = await respuesta.json();
            console.log(datos);
            let noticias = this.#procesarInformacion(datos);
            $("main").append(noticias);
        } catch(error){
            const p = document.createElement("p");
            p.textContent = "Error: No se ha podido cargar las noticias";
            $("main").append(p);
            console.error("Error en las noticias:", error);
        }
    }

    #procesarInformacion(datos){
        const fragment = document.createDocumentFragment();
        const section = document.createElement("section");

        const h2 = document.createElement('h2');
        h2.textContent = `Noticias sobre ${this.#busqueda}`;
        section.appendChild(h2);

        // Iterar sobre cada objeto de noticia en el array 'data'
        datos.data.forEach(noticia => {
            const article = document.createElement('article');
            // 1. Título (H3 con enlace)
            const h3 = document.createElement('h3');
            const linkTitle = document.createElement('a');
            linkTitle.href = noticia.url;
            linkTitle.target = '_blank'; // Abre el enlace en una nueva pestaña
            linkTitle.textContent = noticia.title;
            h3.appendChild(linkTitle);
            article.appendChild(h3);

            const pMeta = document.createElement('p');
            const publishedDate = new Date(noticia.published_at).toLocaleDateString();
            pMeta.innerHTML = `Fuente: ${noticia.source} | Publicado: ${publishedDate}`;
            article.appendChild(pMeta);

            // if (noticia.image_url) {
            //     const img = document.createElement('img');
            //     img.src = noticia.image_url;
            //     img.alt = noticia.title;
            //     article.appendChild(img);
            // }

            const pDescription = document.createElement('p');
            pDescription.textContent = noticia.description || noticia.snippet;
            article.appendChild(pDescription);

            section.appendChild(article);
        });
        fragment.append(section);
        return fragment;
    }
}