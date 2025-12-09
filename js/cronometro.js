"use strict";

class Cronometro {
    #tiempo;
    #inicio;
    #corriendo;

    constructor(){
        this.#tiempo = 0;
        this.#inicio = 0;
        this.#corriendo = 0;

        this.#configurarInterfaz();
    }

    arrancar(){
        if (this.#tiempo == 0){
                try{
                this.#inicio = Temporal.Now.instant();
            } catch(error){
                this.#inicio = new Date();
            } 
            const actualizadorLigado = this.#actualizar.bind(this);
            this.#corriendo = setInterval(actualizadorLigado, 100); 
        }     
    }

    #actualizar(){
        let tiempoTranscurrido;
        let ahora;
        
        if (typeof Temporal !== 'undefined' && this.#inicio instanceof Temporal.Instant) {
            
            ahora = Temporal.Now.instant();
            const duracion = this.#inicio.until(ahora);
            tiempoTranscurrido = duracion.total({ unit: 'millisecond' });
            
        } else if (this.#inicio instanceof Date) {
            
            ahora = new Date();
            tiempoTranscurrido = ahora.getTime() - this.#inicio.getTime();
        }

        this.#tiempo = tiempoTranscurrido;
        this.mostrar();
    }

    mostrar(){
        const totalMilisegundos = this.#tiempo;

        // Minutos: (ms / 1000 / 60) y se usa parseInt para obtener el número entero
        const minutos = parseInt((totalMilisegundos / 60000) % 60);

        // Segundos: (ms / 1000) y se usa % 60 para que el contador de segundos se reinicie en 0
        const segundos = parseInt((totalMilisegundos / 1000) % 60);
        
        // Décimas de segundo: (ms / 100) y se usa % 10 para obtener solo la primera cifra
        const decimas = parseInt((totalMilisegundos / 100) % 10);
        
        const minutosFormateados = String(minutos).padStart(2, '0');
        const segundosFormateados = String(segundos).padStart(2, '0');

        const tiempoFormato = 
            `${minutosFormateados}:${segundosFormateados}.${decimas}`;
            
        const parrafoCronometro = document.querySelector('main p');
        
        if (parrafoCronometro) {
            parrafoCronometro.textContent = tiempoFormato;
        }
    }

    parar(){
        clearInterval(this.#corriendo);
    }

    reiniciar(){
        this.parar();
        this.#tiempo = 0;
        this.mostrar();
    }

    #configurarInterfaz(){
        const botonesNode = document.querySelectorAll("main button")

        botonesNode.forEach((boton, i) => {
            switch(i){
                case 0:
                    boton.addEventListener('click', () => this.arrancar());
                    break;
                case 1:
                    boton.addEventListener('click', () => this.parar());
                    break;
                case 2: 
                    boton.addEventListener('click', () => this.reiniciar());
                    break;
            }
        });
    }
}